#!/usr/bin/env bash
# Uji GET /api/wali/disiplin (FR-DIS-06, AC-38): Wali membaca catatan kedisiplinan anaknya sendiri, read-only,
# tahun ajaran aktif, terbaru di atas, tanpa data Guru, dan tidak pernah memuat catatan siswa lain.
#   bash tests/smoke_langkah7c.sh      (prasyarat: php database/seeds/data_uji.php)
# Catatan uji berjenis "UJI-..." dan dibersihkan lewat database/seeds/bersihkan_data_uji.php (catatan tidak dapat
# dihapus lewat API). Siswa kedua dibuat sendiri (NISN acak) dan dihapus lewat API di akhir.
BASE="${BASE:-http://localhost/si-kesa/backend/public}"
DIR="$(cd "$(dirname "$0")" && pwd)"
GAGAL=0
call() { local a=(-s -w $'\n%{http_code}' -X "$1" "$BASE$2" -H 'Content-Type: application/json'); [ -n "$3" ] && a+=(-H "Authorization: Bearer $3"); [ -n "$4" ] && a+=(-d "$4"); local o; o=$(curl "${a[@]}"); CODE=${o##*$'\n'}; BODY=${o%$'\n'*}; }
cek() { if [ "$CODE" = "$2" ]; then echo "  OK    $1 (HTTP $CODE)"; else echo "  GAGAL $1 (harapan $2, dapat $CODE)"; echo "        ${BODY:0:300}"; GAGAL=1; fi; }
memuat() { echo "$BODY" | grep -q "$2" && echo "  OK    $1" || { echo "  GAGAL $1 (tidak memuat: $2)"; echo "        ${BODY:0:300}"; GAGAL=1; }; }
tidak_memuat() { if echo "$BODY" | grep -q "$2"; then echo "  GAGAL $1 (seharusnya TIDAK memuat: $2)"; echo "        ${BODY:0:300}"; GAGAL=1; else echo "  OK    $1"; fi; }
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9][0-9]*' | head -1 | grep -o '[0-9]*$'; }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token; }
posisi() { echo "$BODY" | grep -bo "$1" | head -1 | cut -d: -f1; }

echo "== Persiapan"
php "$DIR/../../database/seeds/bersihkan_data_uji.php" >/dev/null 2>&1
T=$(login admin_uji admin12345); TG=$(login guru_uji guru12345)
[ -z "$T" ] || [ -z "$TG" ] && { echo "Login admin_uji/guru_uji gagal. Jalankan: php database/seeds/data_uji.php"; exit 1; }
call POST /api/wali/login "" '{"nisn":"0000000001"}'; TW=$(token)
[ -z "$TW" ] && { echo "Login Wali NISN 0000000001 gagal (data uji belum ada)."; exit 1; }
call GET /api/guru/siswa "$TG"
IDS=$(echo "$BODY" | grep -o '"id_siswa":[0-9]*,"nama_siswa":"Siswa Uji Aktif"' | head -1 | grep -o '[0-9]*' | head -1)
[ -z "$IDS" ] && { echo "Siswa Uji Aktif tidak ditemukan."; exit 1; }
call GET /api/guru/me "$TG"; IDK=$(echo "$BODY" | grep -o '"id_kelas":[0-9]*' | head -1 | grep -o '[0-9]*$')

echo "== Akses"
call GET /api/wali/disiplin "";       cek "tanpa token" 401
call GET /api/wali/disiplin "$T";     cek "token Admin ditolak" 401
call GET /api/wali/disiplin "$TG";    cek "token Guru ditolak" 401
call POST /api/wali/disiplin "$TW" '{}'; cek "tidak ada rute tulis (POST)" 405
call PUT /api/wali/disiplin "$TW" '{}';  cek "tidak ada rute tulis (PUT)" 405
call DELETE /api/wali/disiplin "$TW";    cek "tidak ada rute tulis (DELETE)" 405

echo "== Isi dan urutan"
call GET /api/wali/disiplin "$TW"; cek "belum ada catatan tetap 200" 200
memuat "total 0" '"total":0'; memuat "items kosong" '"items":\[\]'
call POST /api/guru/disiplin "$TG" "{\"id_siswa\":$IDS,\"tanggal\":\"2035-03-10\",\"jenis_pelanggaran\":\"UJI-Terlambat\",\"catatan\":\"Datang pukul 07.30\"}"; cek "Guru catat Maret" 201
call POST /api/guru/disiplin "$TG" "{\"id_siswa\":$IDS,\"tanggal\":\"2035-04-02\",\"jenis_pelanggaran\":\"UJI-Seragam\",\"catatan\":\"Tidak memakai dasi\"}"; cek "Guru catat April" 201
call GET /api/wali/disiplin "$TW"; cek "Wali membaca" 200
memuat "total 2" '"total":2'; memuat "ada tahun_ajaran" '"tahun_ajaran"'
memuat "memuat jenis_pelanggaran" 'UJI-Terlambat'; memuat "memuat catatan lengkap" 'Datang pukul 07.30'; memuat "memuat tanggal" '2035-03-10'
PA=$(posisi 'UJI-Seragam'); PM=$(posisi 'UJI-Terlambat')
[ -n "$PA" ] && [ -n "$PM" ] && [ "$PA" -lt "$PM" ] && echo "  OK    terbaru di atas (April sebelum Maret)" || { echo "  GAGAL urutan terbaru di atas"; GAGAL=1; }
tidak_memuat "tanpa id_guru" 'id_guru'; tidak_memuat "tanpa nama_guru" 'nama_guru'; tidak_memuat "tanpa id_kelas" 'id_kelas'
tidak_memuat "tanpa id_siswa" 'id_siswa'; tidak_memuat "tanpa kontak wali" 'no_hp_wali'

echo "== Filter dan parameter"
call GET "/api/wali/disiplin?bulan=2035-03" "$TW"; cek "filter Maret" 200
memuat "Maret: Terlambat" 'UJI-Terlambat'; tidak_memuat "Maret: tanpa Seragam" 'UJI-Seragam'; memuat "Maret: total 1" '"total":1'
call GET "/api/wali/disiplin?bulan=2035-05" "$TW"; cek "bulan tanpa catatan tetap 200" 200; memuat "total 0" '"total":0'
call GET "/api/wali/disiplin?bulan=2035-13" "$TW"; cek "bulan 13" 400
call GET "/api/wali/disiplin?bulan=Maret" "$TW"; cek "bulan bukan YYYY-MM" 400
call GET "/api/wali/disiplin?id_siswa=99999" "$TW"; cek "id_siswa dari klien diabaikan" 200; memuat "tetap data token" 'UJI-Terlambat'

echo "== Isolasi antar siswa"
NISN="9$(printf '%09d' $(( (RANDOM * 32768 + RANDOM) % 1000000000 )))"; NOABS=$((200 + RANDOM % 50))
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Siswa Uji Disiplin B\",\"nisn_resmi\":\"$NISN\"}"; cek "buat siswa B" 201; IDB=$(idbaru)
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDB,\"id_kelas\":$IDK,\"nomor_absen\":$NOABS}"; cek "tempatkan siswa B" 201; IDRB=$(idbaru)
call POST /api/guru/disiplin "$TG" "{\"id_siswa\":$IDB,\"tanggal\":\"2035-03-12\",\"jenis_pelanggaran\":\"UJI-Milik-B\",\"catatan\":\"Hanya untuk B\"}"; cek "Guru catat untuk B" 201
call GET /api/wali/disiplin "$TW"; tidak_memuat "Wali A tidak melihat catatan B" 'UJI-Milik-B'; memuat "Wali A tetap total 2" '"total":2'
call POST /api/wali/login "" "{\"nisn\":\"$NISN\"}"; cek "Wali B login" 200; TWB=$(token)
call GET /api/wali/disiplin "$TWB"; cek "Wali B membaca" 200; memuat "Wali B melihat miliknya" 'UJI-Milik-B'; tidak_memuat "Wali B tidak melihat catatan A" 'UJI-Terlambat'; memuat "Wali B total 1" '"total":1'
call PUT /api/admin/siswa/$IDB "$T" '{"status_siswa":"pindah"}'; cek "siswa B ditandai pindah" 200
call GET /api/wali/disiplin "$TWB"; cek "token lama Wali B ditolak setelah pindah" 401

echo "== Bersihkan"
php "$DIR/../../database/seeds/bersihkan_data_uji.php"
call DELETE /api/admin/riwayat-kelas/$IDRB "$T"; cek "hapus penempatan B" 200
call DELETE /api/admin/siswa/$IDB "$T"; cek "hapus siswa B" 200

echo; [ "$GAGAL" = "0" ] && echo "SEMUA UJI LANGKAH 7c LOLOS." || { echo "ADA UJI YANG GAGAL (lihat di atas)."; exit 1; }
