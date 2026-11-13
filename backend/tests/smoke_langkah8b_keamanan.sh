#!/usr/bin/env bash
# Uji keamanan dasar (Test Plan 2.2; TDD bab 12; NFR-SEC-02/04/05/06/09): pemalsuan token, injeksi SQL,
# manipulasi parameter, dan kebocoran informasi pada error.
#   bash tests/smoke_langkah8b_keamanan.sh    (prasyarat: php database/seeds/data_uji.php; perintah openssl tersedia)
# Pemalsuan token memakai JWT_SECRET dari backend/.env (hanya untuk APP_ENV=local). Uji ini hanya membaca data.
BASE="${BASE:-http://localhost/si-kesa/backend/public}"
DIR="$(cd "$(dirname "$0")" && pwd)"
ENVF="$DIR/../.env"
GAGAL=0
call() { local a=(-s -w $'\n%{http_code}' -X "$1" "$BASE$2" -H 'Content-Type: application/json'); [ -n "$3" ] && a+=(-H "Authorization: Bearer $3"); [ -n "$4" ] && a+=(-d "$4"); local o; o=$(curl "${a[@]}"); CODE=${o##*$'\n'}; BODY=${o%$'\n'*}; }
cek() { if [ "$CODE" = "$2" ]; then echo "  OK    $1 (HTTP $CODE)"; else echo "  GAGAL $1 (harapan $2, dapat $CODE)"; echo "        ${BODY:0:200}"; GAGAL=1; fi; }
# aman: bukan 500 dan tidak membocorkan detail teknis
aman() {
  if [ "$CODE" = "500" ] || echo "$BODY" | grep -qiE 'SQLSTATE|PDOException|Stack trace|syntax error|on line [0-9]|vendor/|\.php'; then
    echo "  GAGAL $1 (HTTP $CODE, respons bocor/500)"; echo "        ${BODY:0:200}"; GAGAL=1
  else echo "  OK    $1 (HTTP $CODE, tidak bocor)"; fi
}
token()  { echo "$1" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token "$BODY"; }
b64url() { openssl base64 -A | tr '+/' '-_' | tr -d '='; }
jwt() { # jwt HEADER_JSON PAYLOAD_JSON [SECRET]  (tanpa SECRET = tanda tangan kosong)
  local h p s; h=$(printf '%s' "$1" | b64url); p=$(printf '%s' "$2" | b64url)
  if [ -n "$3" ]; then s=$(printf '%s' "$h.$p" | openssl dgst -sha256 -hmac "$3" -binary | b64url); else s=""; fi
  echo "$h.$p.$s"
}

echo "== Persiapan"
SECRET=$(grep -E '^JWT_SECRET=' "$ENVF" | head -1 | cut -d= -f2- | tr -d '\r' | sed -E "s/[[:space:]]+#.*$//; s/^[\"']//; s/[\"']$//")
TA=$(login admin_uji admin12345); TG=$(login guru_uji guru12345)
call POST /api/wali/login "" '{"nisn":"0000000001"}'; TW=$(token "$BODY")
{ [ -z "$TA" ] || [ -z "$TG" ] || [ -z "$TW" ]; } && { echo "Login seed gagal. Jalankan: php database/seeds/data_uji.php"; exit 1; }
call GET /api/auth/me "$TA"; IDA=$(echo "$BODY" | grep -o '"id_user":[0-9]*' | head -1 | grep -o '[0-9]*$')
call GET /api/auth/me "$TG"; IDG=$(echo "$BODY" | grep -o '"id_user":[0-9]*' | head -1 | grep -o '[0-9]*$')
call GET /api/guru/me "$TG"; IDK=$(echo "$BODY" | grep -o '"id_kelas":[0-9]*' | head -1 | grep -o '[0-9]*$')
NOW=$(date +%s); H='{"typ":"JWT","alg":"HS256"}'

echo "== Token palsu (tanpa rahasia)"
PAY_A="{\"typ\":\"internal\",\"sub\":\"$IDA\",\"role\":\"admin\",\"iat\":$NOW,\"exp\":$((NOW+3600))}"
call GET /api/auth/me "$(jwt '{"typ":"JWT","alg":"none"}' "$PAY_A")"; cek "alg=none tanpa tanda tangan" 401
call GET /api/auth/me "$(jwt "$H" "$PAY_A")";                          cek "tanda tangan kosong" 401
call GET /api/auth/me "$(jwt "$H" "$PAY_A" 'rahasia-yang-salah-0123456789abcdef')"; cek "tanda tangan dengan rahasia salah" 401
# Ubah payload token Guru asli menjadi role admin tanpa menandatangani ulang
HDR=${TG%%.*}; REST=${TG#*.}; SIG=${REST#*.}
PAYLOAD_ADMIN=$(printf '%s' "$PAY_A" | b64url)
call GET /api/admin/akun "$HDR.$PAYLOAD_ADMIN.$SIG"; cek "payload diubah menjadi admin, tanda tangan lama" 401
call GET /api/admin/akun "${TG%.*}.";                 cek "tanda tangan dipotong" 401
call GET /api/auth/me "xxx";                          cek "token bukan JWT" 401
call GET /api/auth/me "";                             cek "tanpa header Authorization" 401
CODE=$(curl -s -o /dev/null -w '%{http_code}' "$BASE/api/auth/me" -H 'Authorization: Bearer '); BODY=""; cek "Bearer kosong" 401
CODE=$(curl -s -o /dev/null -w '%{http_code}' "$BASE/api/auth/me" -H 'Authorization: Basic YWRtaW46YWRtaW4='); BODY=""; cek "skema Basic ditolak" 401

echo "== Token dengan tanda tangan SAH (butuh JWT_SECRET lokal dan openssl) untuk menguji aturan isi token"
SAH_SIAP=1
if ! command -v openssl >/dev/null 2>&1; then
  echo "  GAGAL perintah openssl tidak ditemukan; bagian ini tidak dapat divalidasi"; GAGAL=1; SAH_SIAP=0
else
  VEC=$(printf '%s' 'what do ya want for nothing?' | openssl dgst -sha256 -hmac 'Jefe' | awk '{print $NF}')
  if [ "$VEC" != "5bdcc146bf60754e6a042426089575c75a003f089d2739839dec58b964ec3843" ]; then
    echo "  GAGAL HMAC-SHA256 lokal tidak sesuai vektor uji RFC 4231 ($(openssl version)); bagian ini tidak dapat divalidasi"; GAGAL=1; SAH_SIAP=0
  fi
fi
if [ "$SAH_SIAP" = "1" ] && { [ -z "$SECRET" ] || [ ${#SECRET} -lt 32 ]; }; then
  echo "  GAGAL JWT_SECRET tidak terbaca dari backend/.env atau lebih pendek dari 32 karakter (panjang terbaca: ${#SECRET})"; GAGAL=1; SAH_SIAP=0
fi
if [ "$SAH_SIAP" = "1" ]; then
  call GET /api/auth/me "$(jwt "$H" "$PAY_A" "$SECRET")"
  if [ "$CODE" = "200" ]; then
    echo "  OK    kontrol: token buatan sendiri yang sah diterima (membuktikan kasus di bawah valid)"
  else
    echo "  GAGAL kontrol: token buatan sendiri yang sah ditolak (HTTP $CODE); kasus token sah di bawah DILEWATI karena tidak valid"
    echo "        server: ${BODY:0:120}"
    echo "        diagnosis: panjang JWT_SECRET=${#SECRET}; id_user admin=$IDA; komentar inline di .env=$(grep -cE '^JWT_SECRET=.*[[:space:]]#' "$ENVF"); bertanda kutip=$(grep -cE "^JWT_SECRET=[\"']" "$ENVF"); openssl=$(openssl version)"
    GAGAL=1; SAH_SIAP=0
  fi
fi
if [ "$SAH_SIAP" = "1" ]; then
  call GET /api/auth/me "$(jwt "$H" "{\"typ\":\"internal\",\"sub\":\"$IDA\",\"role\":\"admin\",\"iat\":$((NOW-7200)),\"exp\":$((NOW-10))}" "$SECRET")"; cek "token kedaluwarsa" 401
  call GET /api/auth/me "$(jwt "$H" "{\"typ\":\"internal\",\"sub\":\"99999999\",\"role\":\"admin\",\"iat\":$NOW,\"exp\":$((NOW+3600))}" "$SECRET")"; cek "akun yang tidak ada" 401
  call GET /api/admin/akun "$(jwt "$H" "{\"typ\":\"wali\",\"sub\":\"$IDA\",\"role\":\"admin\",\"iat\":$NOW,\"exp\":$((NOW+3600))}" "$SECRET")"; cek "typ=wali dipakai di rute Admin" 401
  call GET /api/wali/siswa "$(jwt "$H" "{\"typ\":\"internal\",\"sub\":\"$IDA\",\"role\":\"admin\",\"iat\":$NOW,\"exp\":$((NOW+3600))}" "$SECRET")"; cek "token internal dipakai di rute Wali" 401
  call GET /api/wali/siswa "$(jwt "$H" "{\"typ\":\"wali\",\"id_siswa\":99999999,\"iat\":$NOW,\"exp\":$((NOW+3600))}" "$SECRET")"; cek "Wali untuk siswa yang tidak ada" 401
  call GET /api/wali/siswa "$(jwt "$H" "{\"typ\":\"wali\",\"id_siswa\":1,\"iat\":$((NOW-7200)),\"exp\":$((NOW-10))}" "$SECRET")"; cek "token Wali kedaluwarsa" 401
  call GET /api/auth/me "$(jwt "$H" "{\"sub\":\"$IDA\",\"role\":\"admin\",\"iat\":$NOW,\"exp\":$((NOW+3600))}" "$SECRET")"; cek "tanpa klaim typ" 401
else
  echo "  LEWAT 7 kasus token sah tidak dijalankan; hasilnya TIDAK dianggap lolos"
fi

echo "== Injeksi SQL (dianggap data biasa; tidak boleh 500 atau membocorkan detail)"
call POST /api/auth/login "" "{\"username\":\"admin_uji' OR '1'='1\",\"password\":\"x\"}"; cek "login: username injeksi" 401
call POST /api/auth/login "" "{\"username\":\"admin_uji\",\"password\":\"' OR '1'='1\"}"; cek "login: password injeksi" 401
call POST /api/auth/login "" '{"username":"admin_uji\u0027 --","password":"x"}'; cek "login: komentar SQL" 401
call POST /api/auth/login "" '{"username":{"$ne":null},"password":{"$ne":null}}'; aman "login: objek sebagai username/password (gaya NoSQL)"
call POST /api/wali/login "" "{\"nisn\":\"0000000001' OR '1'='1\"}"; aman "Wali login: NISN injeksi"
call POST /api/wali/login "" '{"nisn":"0000000001 OR 1=1"}'; aman "Wali login: NISN dengan OR"
call GET "/api/admin/siswa?q=%27%20OR%201%3D1%20--%20" "$TA"; aman "Admin siswa: q injeksi"
call GET "/api/admin/siswa?q=%27%3B%20DROP%20TABLE%20data_siswa%3B--" "$TA"; aman "Admin siswa: DROP TABLE"
call GET "/api/admin/siswa?status=aktif%27%20OR%20%271%27%3D%271" "$TA"; aman "Admin siswa: status injeksi"
call GET "/api/admin/guru?q=%25%27%20UNION%20SELECT%20kata_sandi_hash%20FROM%20akun_pengguna--" "$TA"; aman "Admin guru: UNION SELECT"
call GET "/api/admin/akademik/nilai?id_siswa=1%20OR%201%3D1" "$TA"; aman "Admin akademik: id_siswa injeksi"
call GET "/api/guru/nilai?id_siswa=1%20OR%201%3D1" "$TG"; aman "Guru nilai: id_siswa injeksi"
call GET "/api/guru/absensi?date=2026-10-05%27%20OR%20%271%27%3D%271" "$TG"; aman "Guru absensi: date injeksi"
call GET "/api/wali/absensi?bulan=2026-10%27%20OR%201%3D1" "$TW"; aman "Wali absensi: bulan injeksi"
call GET "/api/wali/disiplin?bulan=%27%20OR%201%3D1--" "$TW"; aman "Wali disiplin: bulan injeksi"
call GET "/api/admin/siswa/1%20OR%201%3D1" "$TA"; aman "path id injeksi"
call GET "/api/public/event?kategori=kegiatan%27%20OR%20%271%27%3D%271" ""; aman "publik event: kategori injeksi"

echo "== Manipulasi parameter dan input aneh"
call GET "/api/guru/dasbor/absensi?id_kelas=999999&date=2026-10-05" "$TG"; cek "Guru: id_kelas dari klien diabaikan" 200
echo "$BODY" | grep -q "\"id_kelas\":$IDK" && echo "  OK    dasbor tetap memakai kelas Guru ($IDK)" || { echo "  GAGAL dasbor tidak memakai kelas Guru ($IDK): ${BODY:0:160}"; GAGAL=1; }
call GET "/api/wali/nilai" "$TW"; A="$BODY"; call GET "/api/wali/nilai?id_siswa=999999" "$TW"; B="$BODY"
[ "$A" = "$B" ] && echo "  OK    Wali: id_siswa dari klien diabaikan (respons sama)" || { echo "  GAGAL Wali: respons berubah karena id_siswa dari klien"; GAGAL=1; }
call POST /api/auth/login "" '{"username":'; aman "JSON rusak"
call POST /api/auth/login "" ''; aman "badan kosong"
LONG=$(head -c 20000 /dev/zero | tr '\0' 'a'); call POST /api/auth/login "" "{\"username\":\"$LONG\",\"password\":\"x\"}"; aman "username 20.000 karakter"
call GET "/api/admin/siswa?page=-1&per_page=99999999" "$TA"; aman "paginasi ekstrem"
call GET "/api/admin/siswa?page=abc" "$TA"; aman "page bukan angka"
call GET "/api/admin/akun/abc" "$TA"; aman "id bukan angka"
call GET "/api/admin/siswa/99999999999999999999" "$TA"; aman "id sangat besar"

echo "== Kebocoran informasi pada error"
call GET /api/tidak-ada "" ; cek "rute tak dikenal = 404" 404; aman "404 tanpa detail teknis"
echo "$BODY" | grep -q '"success":false' && echo "  OK    404 berformat JSON kontrak" || { echo "  GAGAL 404 bukan JSON kontrak: ${BODY:0:120}"; GAGAL=1; }
call POST /api/auth/login "" '{"username":"admin_uji","password":"salah"}'; cek "password salah" 401
call POST /api/auth/login "" '{"username":"tidak_ada_xyz","password":"salah"}'; A="$BODY"
call POST /api/auth/login "" '{"username":"admin_uji","password":"salah"}'; B="$BODY"
[ "$A" = "$B" ] && echo "  OK    pesan login sama untuk username salah dan password salah (tidak membocorkan akun)" || { echo "  GAGAL pesan login berbeda antara username salah dan password salah"; GAGAL=1; }
curl -s -D - -o /dev/null "$BASE/api/public/pengaturan" | grep -qiE '^(x-powered-by|server:.*(php|apache)/)' && echo "  CATATAN header versi server terlihat (X-Powered-By/Server); pertimbangkan disembunyikan saat produksi"

echo; [ "$GAGAL" = "0" ] && echo "SEMUA UJI KEAMANAN LOLOS." || { echo "ADA UJI KEAMANAN YANG GAGAL (lihat baris GAGAL di atas)."; exit 1; }
