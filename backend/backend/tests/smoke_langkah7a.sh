#!/usr/bin/env bash
# Uji kontrak yang diandalkan lapisan JS PWA Guru: GET /api/guru/mapel dan alur absensi POST -> 409 -> PUT.
#   bash backend/tests/smoke_langkah7a.sh      (prasyarat: data_uji.php)
BASE="${BASE:-http://localhost/si-kesa/backend/public}"
DIR="$(cd "$(dirname "$0")" && pwd)"
GAGAL=0
call() { local a=(-s -w $'\n%{http_code}' -X "$1" "$BASE$2" -H 'Content-Type: application/json'); [ -n "$3" ] && a+=(-H "Authorization: Bearer $3"); [ -n "$4" ] && a+=(-d "$4"); local o; o=$(curl "${a[@]}"); CODE=${o##*$'\n'}; BODY=${o%$'\n'*}; }
cek() { if [ "$CODE" = "$2" ]; then echo "  OK    $1 (HTTP $CODE)"; else echo "  GAGAL $1 (harapan $2, dapat $CODE)"; echo "        ${BODY:0:300}"; GAGAL=1; fi; }
memuat() { echo "$BODY" | grep -q "$2" && echo "  OK    $1" || { echo "  GAGAL $1 (tidak memuat: $2)"; echo "        ${BODY:0:300}"; GAGAL=1; }; }
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9][0-9]*' | head -1 | grep -o '[0-9]*$'; }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token; }

echo "== Persiapan"
T=$(login admin_uji admin12345); TG=$(login guru_uji guru12345)
[ -z "$T" ] || [ -z "$TG" ] && { echo "Login admin_uji/guru_uji gagal. Jalankan: php database/seeds/data_uji.php"; exit 1; }
call GET /api/guru/siswa "$TG"
IDS=$(echo "$BODY" | grep -o '"id_siswa":[0-9]*,"nama_siswa":"Siswa Uji Aktif"' | head -1 | grep -o '[0-9]*' | head -1)
[ -z "$IDS" ] && { echo "Siswa Uji Aktif tidak ditemukan."; exit 1; }
call POST /api/admin/mapel "$T" "{\"kode_mapel\":\"NUJI$RANDOM\",\"nama_mapel\":\"Mapel Kontrak Uji\"}"; cek "buat mapel uji" 201

echo "== GET /api/guru/mapel"
call GET /api/guru/mapel "";      cek "tanpa token" 401
call GET /api/guru/mapel "$T";    cek "token Admin ditolak di rute Guru" 403
call GET /api/guru/mapel "$TG";   cek "Guru dapat membaca daftar mapel" 200
memuat "memuat mapel uji" 'Mapel Kontrak Uji'; memuat "bentuk items" '"items"'; memuat "kolom id_mapel" '"id_mapel"'
call POST /api/guru/mapel "$TG" '{}'; cek "tidak ada rute tulis" 405

echo "== Alur absensi yang dipakai absensi.js"
D=2035-04-04
call GET "/api/guru/absensi?date=$D" "$TG"; cek "lihat tanggal kosong" 200
memuat "status_kelas belum" '"status_kelas":"belum"'; memuat "id_absensi null" '"id_absensi":null'
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$D\",\"items\":[{\"id_siswa\":$IDS,\"status\":\"hadir\"}]}"; cek "POST pertama" 201
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$D\",\"items\":[{\"id_siswa\":$IDS,\"status\":\"hadir\"}]}"; cek "POST kedua pada tanggal sama = 409 (bukan upsert)" 409
call GET "/api/guru/absensi?date=$D" "$TG"; memuat "status_kelas sudah" '"status_kelas":"sudah"'
IDA=$(echo "$BODY" | grep -o "\"id_siswa\":$IDS,\"nama_siswa\":\"Siswa Uji Aktif\",\"nomor_absen\":[0-9a-z]*,\"id_absensi\":[0-9]*" | head -1 | grep -o '[0-9]*$')
[ -n "$IDA" ] && echo "  OK    id_absensi tersedia di GET ($IDA)" || { echo "  GAGAL id_absensi tidak ditemukan di GET"; echo "        ${BODY:0:300}"; GAGAL=1; }
call PUT /api/guru/absensi/$IDA "$TG" '{"status":"sakit"}'; cek "PUT koreksi" 200
call GET "/api/guru/absensi?date=$D" "$TG"; memuat "status berubah jadi sakit" '"status":"sakit"'
call PUT /api/guru/absensi/$IDA "$TG" '{"status":"bolos"}'; cek "status tidak valid" 422
call DELETE /api/guru/absensi/$IDA "$TG"; cek "tidak ada DELETE absensi" 405
call GET "/api/guru/absensi?date=2035-04-08" "$TG"; memuat "hari Minggu = libur" '"status_kelas":"libur"'
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"2035-04-08\",\"items\":[{\"id_siswa\":$IDS,\"status\":\"hadir\"}]}"; cek "POST hari Minggu" 422
call GET /api/auth/me "$TG"; cek "auth/me" 200

echo "== Bersihkan"
php "$DIR/../../database/seeds/bersihkan_data_uji.php"
echo; [ "$GAGAL" = "0" ] && echo "SEMUA UJI LANGKAH 7a LOLOS." || { echo "ADA UJI YANG GAGAL (lihat di atas)."; exit 1; }
