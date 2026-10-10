#!/usr/bin/env bash
# Uji otomatis langkah 5 (kenaikan kelas). Prasyarat: php database/seeds/data_uji.php sudah dijalankan.
#   bash tests/smoke_langkah5.sh
#
# PERHATIAN: uji ini benar-benar menjalankan kenaikan kelas dan mengaktifkan tahun ajaran baru pada database
# pengembangan, lalu membalikkannya dengan database/seeds/batalkan_kenaikan_kelas.php dan menghapus data ujinya.
# Jangan dijalankan pada database yang berisi data sekolah sungguhan.
BASE="${BASE:-http://localhost/si-kesa/backend/public}"
DIR="$(cd "$(dirname "$0")" && pwd)"
GAGAL=0

call() { # call METODE PATH [TOKEN] [BODY]
  local args=(-s -w $'\n%{http_code}' -X "$1" "$BASE$2" -H 'Content-Type: application/json')
  [ -n "$3" ] && args+=(-H "Authorization: Bearer $3")
  [ -n "$4" ] && args+=(-d "$4")
  local out; out=$(curl "${args[@]}")
  CODE=${out##*$'\n'}; BODY=${out%$'\n'*}
}
cek() {
  if [ "$CODE" = "$2" ]; then echo "  OK    $1 (HTTP $CODE)"
  else echo "  GAGAL $1 (harapan $2, dapat $CODE)"; echo "        $BODY"; GAGAL=1; fi
}
memuat() {
  if echo "$BODY" | grep -q "$2"; then echo "  OK    $1"
  else echo "  GAGAL $1 (tidak memuat: $2)"; echo "        $BODY"; GAGAL=1; fi
}
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9][0-9]*' | head -1 | grep -o '[0-9]*$'; }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token; }
nisn()   { printf '%010d' $((RANDOM * RANDOM)); }

mkguru() { # mkguru NAMA  -> GID GUID GUSER
  GUSER="guru_k$RANDOM$RANDOM"
  call POST /api/admin/guru "$T" "{\"username\":\"$GUSER\",\"password\":\"rahasia123\",\"nama_guru\":\"$1\"}"
  GID=$(echo "$BODY" | grep -o '"id_guru":[0-9]*' | head -1 | grep -o '[0-9]*$')
  GUID=$(echo "$BODY" | grep -o '"id_user":[0-9]*' | head -1 | grep -o '[0-9]*$')
}
mkkelas() { # mkkelas TAHUN TINGKAT NAMA [WALI] -> KID
  local w=""; [ -n "$4" ] && w=",\"id_guru_wali\":$4"
  call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$1,\"tingkat_kelas\":$2,\"nama_kelas\":\"$3\"$w}"
  KID=$(idbaru)
}
mksiswa() { # mksiswa NAMA -> SID NISNBARU
  NISNBARU=$(nisn)
  call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"$1\",\"nisn_resmi\":\"$NISNBARU\"}"
  SID=$(idbaru)
}
tempatkan() { # tempatkan SISWA KELAS -> RID
  call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$1,\"id_kelas\":$2}"
  RID=$(idbaru)
}
tidak_login() { call POST /api/wali/login "" "{\"nisn\":\"$1\"}"; }

echo "== Persiapan"
T=$(login admin_uji admin12345)
TG=$(login guru_uji guru12345)
[ -z "$T" ] || [ -z "$TG" ] && { echo "Login admin_uji/guru_uji gagal. Jalankan dulu: php database/seeds/data_uji.php"; exit 1; }
call GET /api/admin/tahun-ajaran "$T"
IDA=$(echo "$BODY" | grep -o '"id_tahun_ajaran":[0-9]*,"nama_tahun":"2026/2027","semester_aktif":"[a-z]*","status_aktif":true' | head -1 | grep -o '[0-9]*' | head -1)
[ -z "$IDA" ] && { echo "Tahun ajaran aktif 2026/2027 (data uji) tidak ditemukan/ tidak aktif. Mungkin sisa uji sebelumnya: jalankan php database/seeds/batalkan_kenaikan_kelas.php <id_tahun_tujuan>."; exit 1; }
call GET /api/guru/me "$TG"; K1A=$(echo "$BODY" | grep -o '"kelas":{"id_kelas":[0-9]*' | grep -o '[0-9]*$')
call GET /api/guru/siswa "$TG"; SUJI=$(echo "$BODY" | grep -o '"id_siswa":[0-9]*,"nama_siswa":"Siswa Uji Aktif"' | head -1 | grep -o '[0-9]*' | head -1)

# --- kelas dan siswa pada tahun ASAL (aktif)
mkkelas $IDA 3 3Z; K3A=$KID
mkkelas $IDA 5 5Z; K5A=$KID
mkkelas $IDA 6 6Z; K6A=$KID
[ -z "$K3A" ] || [ -z "$K5A" ] || [ -z "$K6A" ] && { echo "Gagal membuat kelas uji di tahun asal (mungkin sudah ada kelas tingkat 3/5/6 lain). Hentikan dan cek data."; exit 1; }
mksiswa "Siswa Kenaikan 3";  S3=$SID;  N3=$NISNBARU;  tempatkan $S3 $K3A;  R3=$RID
mksiswa "Siswa Kenaikan 5a"; S5A=$SID; N5A=$NISNBARU; tempatkan $S5A $K5A; R5A=$RID
mksiswa "Siswa Kenaikan 5b"; S5B=$SID; N5B=$NISNBARU; tempatkan $S5B $K5A; R5B=$RID
mksiswa "Siswa Kenaikan 6";  S6=$SID;  N6=$NISNBARU;  tempatkan $S6 $K6A;  R6=$RID
mksiswa "Siswa Baru Dipetakan"; B1=$SID; NB1=$NISNBARU
mksiswa "Siswa Baru Dibiarkan"; B2=$SID; NB2=$NISNBARU

# --- tahun ajaran TUJUAN (belum aktif)
Y=$((3000 + RANDOM % 5000))
call POST /api/admin/tahun-ajaran "$T" "{\"nama_tahun\":\"$Y/$((Y+1))\"}"; cek "buat tahun ajaran tujuan" 201; IDT=$(idbaru)
[ -z "$IDT" ] && { echo "Tahun ajaran tujuan gagal dibuat; hentikan."; exit 1; }
BODYK() { echo "{\"id_tahun_ajaran_tujuan\":$IDT$1}"; }

echo "== Penolakan dasar (tidak ada data yang boleh berubah)"
call POST /api/admin/kenaikan-kelas "$TG" "$(BODYK '')"; cek "Guru tidak boleh menjalankan" 403
call POST /api/admin/kenaikan-kelas "$T" '{}'; cek "id tahun tujuan wajib" 422
call POST /api/admin/kenaikan-kelas "$T" '{"id_tahun_ajaran_tujuan":99999}'; cek "tahun tujuan tidak ada" 422
call POST /api/admin/kenaikan-kelas "$T" "{\"id_tahun_ajaran_tujuan\":$IDA}"; cek "tahun tujuan = tahun aktif" 422
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK '')"; cek "tahun tujuan belum punya kelas" 422; memuat "daftar tingkat yang kurang" 'Tingkat 2 memiliki 0 kelas'

# --- struktur kelas tujuan: wali sengaja belum lengkap
mkguru "Wali 2"; G2=$GID; mkkelas $IDT 2 2Z $G2; K2T=$KID
mkguru "Wali 4"; G4=$GID; U4=$GUID; mkkelas $IDT 4 4Z $G4; K4T=$KID
mkguru "Wali 5"; G5=$GID; mkkelas $IDT 5 5Z $G5; K5T=$KID
mkguru "Wali 6"; G6=$GID; mkkelas $IDT 6 6Z $G6; K6T=$KID
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK '')"; cek "tingkat 3 belum ada" 422; memuat "pesan tingkat 3" 'Tingkat 3 memiliki 0 kelas'
mkkelas $IDT 3 3Z; K3T=$KID
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK '')"; cek "kelas tanpa wali" 422; memuat "menyebut kelas 3Z" 'Kelas 3Z'
mkguru "Wali 3"; G3=$GID
call PUT /api/admin/kelas/$K3T "$T" "{\"id_guru_wali\":$G3}"; cek "tetapkan wali kelas 3Z" 200
call PUT /api/admin/akun/$U4/status "$T" '{"status_akun":"nonaktif"}'; cek "nonaktifkan akun wali 4Z" 200
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK '')"; cek "wali berakun nonaktif" 422; memuat "menyebut akun nonaktif" 'nonaktif'
call PUT /api/admin/akun/$U4/status "$T" '{"status_akun":"aktif"}'; cek "aktifkan kembali akun wali 4Z" 200

echo "== Penempatan tingkat 6 dan siswa baru"
P6=$(printf '"penempatan_tingkat_6":[{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$S5A" "$K6T")
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6")"; cek "satu kelas tingkat 6 + penempatan dikirim" 422; memuat "pesan tidak boleh dikirim" 'tidak boleh dikirim'
call GET "/api/admin/kenaikan-kelas/log?tahun_ajaran_tujuan=$IDT" "$T"; cek "log kosong, tidak ada yang berubah" 200; memuat "total 0" '"total":0'

mkguru "Wali 6b"; G6B=$GID; mkkelas $IDT 6 6Y $G6B; K6Y=$KID
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK '')"; cek "dua kelas tingkat 6 tanpa penempatan" 422; memuat "siswa tingkat 5 belum dipetakan" 'belum dipetakan'
P6=$(printf '"penempatan_tingkat_6":[{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$S5A" "$K6T")
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6")"; cek "hanya sebagian siswa tingkat 5 dipetakan" 422
P6=$(printf '"penempatan_tingkat_6":[{"id_siswa":%s,"id_kelas_tujuan":%s},{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$S5A" "$K5T" "$S5B" "$K6Y")
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6")"; cek "kelas tujuan bukan tingkat 6" 422; memuat "pesan bukan kelas tingkat 6" 'bukan kelas tingkat 6'
P6=$(printf '"penempatan_tingkat_6":[{"id_siswa":%s,"id_kelas_tujuan":%s},{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$S5A" "$K6T" "$S3" "$K6Y")
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6")"; cek "siswa bukan tingkat 5 ikut dipetakan" 422
P6=$(printf '"penempatan_tingkat_6":[{"id_siswa":%s,"id_kelas_tujuan":%s},{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$S5A" "$K6T" "$S5A" "$K6Y")
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6")"; cek "siswa dipetakan dua kali" 422

P6=$(printf '"penempatan_tingkat_6":[{"id_siswa":%s,"id_kelas_tujuan":%s},{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$S5A" "$K6T" "$S5B" "$K6Y")
PB=$(printf '"penempatan_siswa_baru":[{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$S3" "$K3T")
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6,$PB")"; cek "siswa yang dipetakan sebagai baru ternyata aktif" 422
PB=$(printf '"penempatan_siswa_baru":[{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$B1" "$K3A")
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6,$PB")"; cek "kelas tujuan siswa baru bukan milik tahun tujuan" 422
call GET /api/admin/siswa/$S6 "$T"; memuat "setelah semua penolakan: S6 tetap aktif" '"status_siswa":"aktif"'

echo "== Kenaikan kelas berhasil"
PB=$(printf '"penempatan_siswa_baru":[{"id_siswa":%s,"id_kelas_tujuan":%s}]' "$B1" "$K3T")
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6,$PB")"; cek "jalankan kenaikan kelas" 200
memuat "satu siswa lulus (tingkat 6)" '"lulus":1'
memuat "satu siswa baru masuk" '"masuk":1'
memuat "ada rincian per kelas tujuan" '"per_kelas_tujuan"'
call POST /api/admin/kenaikan-kelas "$T" "$(BODYK ",$P6,$PB")"; cek "dijalankan dua kali ditolak" 422; memuat "pesan sudah pernah" 'sudah pernah'

call GET /api/admin/siswa/$S6 "$T";  memuat "tingkat 6 menjadi lulus" '"status_siswa":"lulus"'
call GET /api/admin/siswa/$S5A "$T"; memuat "5a naik ke kelas 6Z" "\"id_kelas\":$K6T"
call GET /api/admin/siswa/$S5B "$T"; memuat "5b naik ke kelas 6Y (pemecahan rombel)" "\"id_kelas\":$K6Y"
call GET /api/admin/siswa/$S3 "$T";  memuat "tingkat 3 naik ke 4Z" "\"id_kelas\":$K4T"
call GET /api/admin/siswa/$SUJI "$T"; memuat "siswa uji tingkat 1 naik ke 2Z" "\"id_kelas\":$K2T"
call GET /api/admin/siswa/$B1 "$T";  memuat "siswa baru dipetakan menjadi aktif" '"status_siswa":"aktif"'; memuat "dan ditempatkan di 3Z tujuan" "\"id_kelas\":$K3T"
call GET /api/admin/siswa/$B2 "$T";  memuat "siswa baru yang dibiarkan tetap baru" '"status_siswa":"baru"'

call GET "/api/admin/kenaikan-kelas/log?tahun_ajaran_tujuan=$IDT" "$T"; cek "log hasil proses" 200
memuat "log: lulus 1" '"lulus":1'; memuat "log: masuk 1" '"masuk":1'; memuat "log mencatat pemroses" 'admin_uji'

echo "== Jeda sebelum tahun ajaran tujuan diaktifkan"
tidak_login $N6;  cek "siswa lulus belum bisa login Wali" 401
tidak_login $NB1; cek "siswa baru hasil masuk belum bisa login Wali" 401
tidak_login $N5A; cek "siswa yang naik tidak terdampak (masih punya penempatan di tahun aktif)" 200
tidak_login $NB2; cek "siswa baru yang tidak dipetakan tetap ditolak" 401

echo "== Aktivasi tahun ajaran tujuan"
call PUT /api/admin/tahun-ajaran/$IDT/aktifkan "$T"; cek "aktifkan tahun tujuan setelah kenaikan kelas" 200
call GET /api/admin/tahun-ajaran/$IDA "$T"; memuat "tahun lama tidak aktif lagi" '"status_aktif":false'
tidak_login $NB1; cek "siswa baru hasil masuk kini bisa login" 200
tidak_login $N5A; cek "siswa naik kini terdaftar di kelas baru" 200
tidak_login $N6;  cek "siswa lulus tetap ditolak" 401
call GET /api/guru/me "$TG"; cek "guru lama: /me tetap 200" 200; memuat "bukan wali di tahun baru (kelas null)" '"kelas":null'
call GET /api/guru/siswa "$TG"; cek "guru lama: tidak punya kelas di tahun baru" 403

echo "== Pembalikan (alat pengembang, BR-37)"
php "$DIR/../../database/seeds/batalkan_kenaikan_kelas.php" $IDT 2>&1 | sed 's/^/        /'
call GET /api/admin/tahun-ajaran/$IDA "$T"; memuat "tahun asal aktif kembali" '"status_aktif":true'
call GET /api/admin/siswa/$S6 "$T";  memuat "lulus kembali aktif" '"status_siswa":"aktif"'
call GET /api/admin/siswa/$B1 "$T";  memuat "siswa baru dipetakan kembali baru" '"status_siswa":"baru"'
call GET "/api/admin/kenaikan-kelas/log?tahun_ajaran_tujuan=$IDT" "$T"; memuat "log terhapus" '"total":0'
call GET /api/guru/me "$TG"; memuat "guru_uji kembali menjadi wali 1A" '"nama_kelas":"1A"'

echo "== Pembersihan"
for R in $R3 $R5A $R5B $R6; do call DELETE /api/admin/riwayat-kelas/$R "$T"; done
for S in $S3 $S5A $S5B $S6 $B1 $B2; do call DELETE /api/admin/siswa/$S "$T"; cek "hapus siswa uji $S" 200; done
for K in $K2T $K3T $K4T $K5T $K6T $K6Y; do call DELETE /api/admin/kelas/$K "$T"; cek "hapus kelas tujuan $K" 200; done
for K in $K3A $K5A $K6A; do call DELETE /api/admin/kelas/$K "$T"; cek "hapus kelas asal $K" 200; done
for G in $G2 $G3 $G4 $G5 $G6 $G6B; do call DELETE /api/admin/guru/$G "$T"; cek "hapus guru uji $G" 200; done
call DELETE /api/admin/tahun-ajaran/$IDT "$T"; cek "hapus tahun ajaran tujuan" 200

echo
[ $GAGAL -eq 0 ] && echo "SEMUA UJI LOLOS" || echo "ADA UJI YANG GAGAL (lihat tanda GAGAL di atas)"
