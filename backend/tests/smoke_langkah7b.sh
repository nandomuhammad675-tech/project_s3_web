#!/usr/bin/env bash
# Uji FR-AUTH-14 / AC-32 / BR-35: token Wali yang SUDAH terbit ditolak pada permintaan berikutnya
# bila siswa tidak lagi aktif atau penempatannya dihapus, tanpa menunggu token habis.
# Juga memeriksa bentuk respons profil Wali (tanpa kontak wali) dan pesan 401 yang generik.
#   bash backend/tests/smoke_langkah7b.sh      (prasyarat: php database/seeds/data_uji.php)
# Siswa uji dibuat sendiri (NISN acak) dan dihapus lagi lewat API di akhir; data seed tidak diubah.
BASE="${BASE:-http://localhost/si-kesa/backend/public}"
GAGAL=0
call() { local a=(-s -w $'\n%{http_code}' -X "$1" "$BASE$2" -H 'Content-Type: application/json'); [ -n "$3" ] && a+=(-H "Authorization: Bearer $3"); [ -n "$4" ] && a+=(-d "$4"); local o; o=$(curl "${a[@]}"); CODE=${o##*$'\n'}; BODY=${o%$'\n'*}; }
cek() { if [ "$CODE" = "$2" ]; then echo "  OK    $1 (HTTP $CODE)"; else echo "  GAGAL $1 (harapan $2, dapat $CODE)"; echo "        ${BODY:0:300}"; GAGAL=1; fi; }
memuat() { echo "$BODY" | grep -q "$2" && echo "  OK    $1" || { echo "  GAGAL $1 (tidak memuat: $2)"; echo "        ${BODY:0:300}"; GAGAL=1; }; }
tidak_memuat() { if echo "$BODY" | grep -q "$2"; then echo "  GAGAL $1 (seharusnya TIDAK memuat: $2)"; echo "        ${BODY:0:300}"; GAGAL=1; else echo "  OK    $1"; fi; }
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9][0-9]*' | head -1 | grep -o '[0-9]*$'; }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token; }
pesan()  { echo "$BODY" | grep -o '"message":"[^"]*"' | head -1; }

echo "== Persiapan"
T=$(login admin_uji admin12345); TG=$(login guru_uji guru12345)
[ -z "$T" ] || [ -z "$TG" ] && { echo "Login admin_uji/guru_uji gagal. Jalankan: php database/seeds/data_uji.php"; exit 1; }
call GET /api/guru/me "$TG"
IDK=$(echo "$BODY" | grep -o '"id_kelas":[0-9]*' | head -1 | grep -o '[0-9]*$')
[ -z "$IDK" ] && { echo "Kelas guru_uji tidak ditemukan (GET /api/guru/me)."; exit 1; }

# pesan gagal generik sebagai pembanding (NISN yang tidak ada)
call POST /api/wali/login "" '{"nisn":"0000000099"}'; cek "NISN tidak ada = 401" 401; PESAN_GAGAL=$(pesan)

NISN="9$(printf '%09d' $(( (RANDOM * 32768 + RANDOM) % 1000000000 )))"
NOABS=$((200 + RANDOM % 50))
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Siswa Uji Token Wali\",\"nisn_resmi\":\"$NISN\"}"; cek "buat siswa uji ($NISN)" 201; IDS=$(idbaru)
[ -z "$IDS" ] && { echo "Siswa uji tidak terbuat; berhenti."; exit 1; }

echo "== Skenario A: penempatan dihapus -> token lama ditolak"
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS,\"id_kelas\":$IDK,\"nomor_absen\":$NOABS}"; cek "tempatkan siswa uji" 201; IDR=$(idbaru)
call POST /api/wali/login "" "{\"nisn\":\"$NISN\"}"; cek "Wali login" 200; TW=$(token)
call GET /api/wali/siswa "$TW"; cek "profil Wali" 200
memuat "ada nama_siswa" '"nama_siswa":"Siswa Uji Token Wali"'; memuat "ada nisn" "\"nisn\":\"$NISN\""
memuat "ada nama_kelas" '"nama_kelas"'; memuat "ada tahun_ajaran" '"tahun_ajaran"'; memuat "ada nomor_absen" "\"nomor_absen\":$NOABS"
memuat "ada nama_wali_kelas (Guru Uji)" '"nama_wali_kelas":"Guru Uji"'
tidak_memuat "tanpa email Guru" 'email'; tidak_memuat "tanpa telepon Guru" 'no_telepon'
tidak_memuat "tanpa kontak wali siswa" '"nama_wali"'; tidak_memuat "tanpa no_hp_wali" 'no_hp_wali'
call GET /api/wali/nilai "$TW"; cek "nilai (kosong tetap 200)" 200
call GET /api/wali/absensi "$TW"; cek "absensi (kosong tetap 200)" 200
call DELETE /api/admin/riwayat-kelas/$IDR "$T"; cek "Admin hapus penempatan (koreksi)" 200
call GET /api/wali/siswa "$TW";   cek "token lama: profil ditolak" 401; memuat "pesan generik sama dengan login gagal" "$PESAN_GAGAL"
call GET /api/wali/nilai "$TW";   cek "token lama: nilai ditolak" 401
call GET /api/wali/absensi "$TW"; cek "token lama: absensi ditolak" 401
call POST /api/wali/login "" "{\"nisn\":\"$NISN\"}"; cek "login baru juga ditolak" 401

echo "== Skenario B: siswa menjadi pindah -> token lama ditolak"
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS,\"id_kelas\":$IDK,\"nomor_absen\":$NOABS}"; cek "tempatkan ulang" 201; IDR=$(idbaru)
call POST /api/wali/login "" "{\"nisn\":\"$NISN\"}"; cek "Wali login lagi" 200; TW2=$(token)
call GET /api/wali/siswa "$TW2"; cek "profil Wali" 200
call PUT /api/admin/siswa/$IDS "$T" '{"status_siswa":"pindah"}'; cek "Admin tandai pindah" 200
call GET /api/wali/siswa "$TW2";   cek "token lama: profil ditolak" 401; memuat "pesan generik" "$PESAN_GAGAL"
call GET /api/wali/nilai "$TW2";   cek "token lama: nilai ditolak" 401
call GET /api/wali/absensi "$TW2"; cek "token lama: absensi ditolak" 401
call POST /api/wali/login "" "{\"nisn\":\"$NISN\"}"; cek "login baru ditolak" 401
call GET /api/wali/siswa "$TW"; cek "token skenario A tetap ditolak" 401

echo "== Bersihkan (lewat API)"
call DELETE /api/admin/riwayat-kelas/$IDR "$T"; cek "hapus penempatan uji (status pindah tidak berubah)" 200
call DELETE /api/admin/siswa/$IDS "$T"; cek "hapus siswa uji" 200

echo; [ "$GAGAL" = "0" ] && echo "SEMUA UJI LANGKAH 7b LOLOS." || { echo "ADA UJI YANG GAGAL (lihat di atas)."; exit 1; }
