#!/usr/bin/env bash
# Matriks RBAC lintas SEMUA rute (Test Plan 2.2: Security & Authorization Testing; URS BR-01/02/03/15, FR-AUTH-03).
# Rute dibaca langsung dari backend/routes/*.php sehingga rute baru yang lupa diberi middleware ikut terdeteksi.
#   bash tests/smoke_langkah8a_rbac.sh        (prasyarat: php database/seeds/data_uji.php)
# Yang dibuktikan (permintaan ditolak SEBELUM menyentuh data, jadi aman dijalankan berulang):
#   rute Admin : tanpa token 401 | token Wali 401 | token Guru 403
#   rute Guru  : tanpa token 401 | token Wali 401 | token Admin 403
#   rute Wali  : tanpa token 401 | token Admin 401 | token Guru 401
#   rute /api/auth/* (selain login): tanpa token 401 | token Wali 401
#   rute publik: hanya GET tanpa token (200); metode tulis 405
BASE="${BASE:-http://localhost/si-kesa/backend/public}"
DIR="$(cd "$(dirname "$0")" && pwd)"
ROUTES="$DIR/../routes"
GAGAL=0; JUMLAH=0

kode() { # kode METODE PATH [TOKEN]  -> HTTP status
  local a=(-s -o /dev/null -w '%{http_code}' -X "$1" "$BASE$2" -H 'Content-Type: application/json' -d '{}')
  [ -n "$3" ] && a+=(-H "Authorization: Bearer $3")
  curl "${a[@]}"
}
harap() { # harap NAMA METODE PATH TOKEN HARAPAN
  local k; k=$(kode "$2" "$3" "$4"); JUMLAH=$((JUMLAH+1))
  if [ "$k" != "$5" ]; then echo "  GAGAL $1: $2 $3 (harapan $5, dapat $k)"; GAGAL=$((GAGAL+1)); fi
}
rute() { # rute POLA_GRUP -> "METODE /path/1" (parameter {id} diganti 1)
  grep -hE '\$r->(get|post|put|delete)\(' "$ROUTES"/*.php | grep -E "$1" \
    | awk -F"'" '{ m=$1; sub(/.*\$r->/,"",m); sub(/\(.*/,"",m); print toupper(m), $2 }' \
    | sed -E 's/\{[a-z_]+\}/1/g'
}
token()  { echo "$1" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
login()  { token "$(curl -s -X POST "$BASE/api/auth/login" -H 'Content-Type: application/json' -d "{\"username\":\"$1\",\"password\":\"$2\"}")"; }

echo "== Persiapan"
TA=$(login admin_uji admin12345); TG=$(login guru_uji guru12345)
TW=$(token "$(curl -s -X POST "$BASE/api/wali/login" -H 'Content-Type: application/json' -d '{"nisn":"0000000001"}')")
{ [ -z "$TA" ] || [ -z "$TG" ] || [ -z "$TW" ]; } && { echo "Login admin_uji/guru_uji/Wali gagal. Jalankan: php database/seeds/data_uji.php"; exit 1; }

RADMIN=$(rute '\$admin\);'); RGURU=$(rute '\$guru(Opsional)?\);'); RWALI=$(rute 'WaliMiddleware::class\]\);'); RAUTH=$(rute '\[AuthMiddleware::class\]\);')
NA=$(echo "$RADMIN" | wc -l); NG=$(echo "$RGURU" | wc -l); NW=$(echo "$RWALI" | wc -l); NU=$(echo "$RAUTH" | wc -l)
echo "  rute terbaca: admin=$NA guru=$NG wali=$NW auth=$NU"
# Penjaga: bila pembacaan rute gagal (format file berubah), uji ini tidak boleh lolos diam-diam.
[ "$NA" -ge 70 ] && [ "$NG" -ge 25 ] && [ "$NW" -ge 4 ] && [ "$NU" -ge 3 ] || { echo "  GAGAL jumlah rute yang terbaca terlalu sedikit; periksa pola pembacaan di skrip ini"; exit 1; }

echo "== Rute Admin ($NA): tanpa token 401, Wali 401, Guru 403"
while read -r M P; do
  harap "admin tanpa token" "$M" "$P" "" 401
  harap "admin dengan token Wali" "$M" "$P" "$TW" 401
  harap "admin dengan token Guru" "$M" "$P" "$TG" 403
done <<< "$RADMIN"

echo "== Rute Guru ($NG): tanpa token 401, Wali 401, Admin 403"
while read -r M P; do
  harap "guru tanpa token" "$M" "$P" "" 401
  harap "guru dengan token Wali" "$M" "$P" "$TW" 401
  harap "guru dengan token Admin" "$M" "$P" "$TA" 403
done <<< "$RGURU"

echo "== Rute Wali ($NW): tanpa token 401, Admin 401, Guru 401"
while read -r M P; do
  harap "wali tanpa token" "$M" "$P" "" 401
  harap "wali dengan token Admin" "$M" "$P" "$TA" 401
  harap "wali dengan token Guru" "$M" "$P" "$TG" 401
done <<< "$RWALI"

echo "== Rute /api/auth/* ($NU): tanpa token 401, Wali 401"
while read -r M P; do
  harap "auth tanpa token" "$M" "$P" "" 401
  harap "auth dengan token Wali" "$M" "$P" "$TW" 401
done <<< "$RAUTH"

echo "== Rute publik: GET tanpa token 200, metode tulis 405"
PUB=$(grep -hE '\$r->get\(.*/api/public/' "$ROUTES"/api_public.php | awk -F"'" '{print $2}' | grep -vE '/ping')
for P in $PUB; do
  harap "publik GET" GET "$P" "" 200
  harap "publik POST" POST "$P" "" 405
  harap "publik PUT" PUT "$P" "" 405
  harap "publik DELETE" DELETE "$P" "" 405
done

echo "== Rute sementara sudah dihapus (kebersihan rilis)"
harap "ping sementara dihapus" GET /api/public/ping "" 404
harap "ping-db sementara dihapus (membocorkan jumlah tabel)" GET /api/public/ping-db "" 404

echo "== Metode yang memang tidak tersedia (405) dan rute tak dikenal (404)"
harap "DELETE kedisiplinan (Guru)" DELETE /api/guru/disiplin/1 "$TG" 405
harap "DELETE akun (Admin)" DELETE /api/admin/akun "$TA" 405
harap "DELETE /akun/{id} tidak ada rute" DELETE /api/admin/akun/1 "$TA" 404
harap "POST akademik nilai (Admin read-only)" POST /api/admin/akademik/nilai "$TA" 405
harap "PUT akademik absensi (Admin read-only)" PUT /api/admin/akademik/absensi "$TA" 405
harap "DELETE akademik disiplin (Admin read-only)" DELETE /api/admin/akademik/disiplin "$TA" 405
harap "POST wali/siswa (read-only)" POST /api/wali/siswa "$TW" 405
harap "DELETE wali/nilai (read-only)" DELETE /api/wali/nilai "$TW" 405
harap "rute tak dikenal" GET /api/tidak-ada "" 404

echo; echo "  Total pemeriksaan: $JUMLAH; gagal: $GAGAL"
[ "$GAGAL" = "0" ] && echo "SEMUA UJI RBAC LOLOS." || { echo "ADA UJI RBAC YANG GAGAL (lihat baris GAGAL di atas)."; exit 1; }
