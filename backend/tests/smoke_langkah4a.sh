#!/usr/bin/env bash
# Uji otomatis langkah 4a (me, siswa, jadwal, piket). Prasyarat: php database/seeds/data_uji.php sudah dijalankan.
#   bash tests/smoke_langkah4a.sh
BASE="${BASE:-http://localhost/si-kesa/backend/public}"
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
tidakmemuat() {
  if echo "$BODY" | grep -q "$2"; then echo "  GAGAL $1 (seharusnya TIDAK memuat: $2)"; echo "        $BODY"; GAGAL=1
  else echo "  OK    $1"; fi
}
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9]*' | head -1 | grep -o '[0-9]*$'; }
nisn()   { printf '%010d' $((RANDOM * RANDOM)); }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token; }

T=$(login admin_uji admin12345)
TG=$(login guru_uji guru12345)
[ -z "$T" ] || [ -z "$TG" ] && { echo "Login admin_uji/guru_uji gagal. Jalankan dulu: php database/seeds/data_uji.php"; exit 1; }
call GET /api/admin/tahun-ajaran "$T"
IDAKTIF=$(echo "$BODY" | grep -o '"id_tahun_ajaran":[0-9]*,"nama_tahun":"2026/2027"' | head -1 | grep -o '[0-9]*' | head -1)
[ -z "$IDAKTIF" ] && { echo "Tahun ajaran 2026/2027 (data uji) tidak ditemukan."; exit 1; }

echo "== Akses dan profil"
call GET /api/guru/me "";       cek "tanpa token" 401
call GET /api/guru/me "$T";     cek "token Admin ditolak di rute Guru" 403
call GET /api/guru/jadwal "$T"; cek "Admin tidak bisa akses jadwal (AC-08)" 403
call GET /api/guru/piket "$T";  cek "Admin tidak bisa akses piket (AC-08)" 403
call GET /api/guru/me "$TG";    cek "profil guru_uji" 200; memuat "kelas 1A dari database" '"nama_kelas":"1A"'
IDK_UJI=$(echo "$BODY" | grep -o '"kelas":{"id_kelas":[0-9]*' | grep -o '[0-9]*$')

U0="guru_tes$RANDOM"
call POST /api/admin/guru "$T" "{\"username\":\"$U0\",\"password\":\"rahasia123\",\"nama_guru\":\"Guru Tanpa Kelas\"}"; IDG0=$(idbaru)
T0=$(login "$U0" rahasia123)
call GET /api/guru/me "$T0";     cek "guru tanpa kelas: /me tetap 200" 200; memuat "kelas bernilai null" '"kelas":null'
call GET /api/guru/siswa "$T0";  cek "guru tanpa kelas: /siswa ditolak" 403
call GET /api/guru/jadwal "$T0"; cek "guru tanpa kelas: /jadwal ditolak" 403

echo "== Daftar siswa kelas"
call GET /api/guru/siswa "$TG"; cek "daftar siswa kelas sendiri" 200; memuat "siswa aktif tampil" 'Siswa Uji Aktif'
tidakmemuat "siswa berstatus baru tidak tampil" 'Siswa Uji Baru'
tidakmemuat "siswa berstatus lulus tidak tampil" 'Siswa Uji Lulus'
IDSUJI=$(echo "$BODY" | grep -o '"id_siswa":[0-9]*,"nama_siswa":"Siswa Uji Aktif"' | head -1 | grep -o '[0-9]*' | head -1)
NP=$(nisn)
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Siswa Calon Pindah\",\"nisn_resmi\":\"$NP\"}"; IDSP=$(idbaru)
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDSP,\"id_kelas\":$IDK_UJI,\"nomor_absen\":9}"; IDRP=$(idbaru)
call GET /api/guru/siswa "$TG"; memuat "siswa baru ditempatkan tampil" 'Siswa Calon Pindah'
call PUT /api/admin/siswa/$IDSP "$T" '{"status_siswa":"pindah"}'
call GET /api/guru/siswa "$TG"; tidakmemuat "siswa pindah tidak tampil lagi" 'Siswa Calon Pindah'

echo "== Jadwal pelajaran"
call POST /api/admin/mapel "$T" "{\"kode_mapel\":\"J$RANDOM\",\"nama_mapel\":\"Mapel Jadwal Uji\"}"; IDM=$(idbaru)
call POST /api/guru/jadwal "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"senin\",\"jam_mulai\":\"07:00\",\"jam_selesai\":\"08:00\",\"keterangan\":\"uji\"}"; cek "buat jadwal" 201; J1=$(idbaru)
memuat "jam dinormalkan" '"jam_mulai":"07:00:00"'
call POST /api/guru/jadwal "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"senin\",\"jam_mulai\":\"07:30\",\"jam_selesai\":\"08:30\"}"; cek "jadwal beririsan ditolak" 409
call POST /api/guru/jadwal "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"senin\",\"jam_mulai\":\"08:00\",\"jam_selesai\":\"09:00\"}"; cek "jadwal bersambung (08:00) diterima" 201; J2=$(idbaru)
call POST /api/guru/jadwal "$TG" "{\"semester\":\"genap\",\"id_mapel\":$IDM,\"hari\":\"senin\",\"jam_mulai\":\"07:30\",\"jam_selesai\":\"08:30\"}"; cek "semester lain tidak bentrok" 201; J3=$(idbaru)
call POST /api/guru/jadwal "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"selasa\",\"jam_mulai\":\"09:00\",\"jam_selesai\":\"09:00\"}"; cek "jam selesai = jam mulai" 422
call POST /api/guru/jadwal "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"selasa\",\"jam_mulai\":\"7pm\",\"jam_selesai\":\"09:00\"}"; cek "format jam salah" 422
call POST /api/guru/jadwal "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"minggu\",\"jam_mulai\":\"07:00\",\"jam_selesai\":\"08:00\"}"; cek "hari minggu bukan hari jadwal" 422
call POST /api/guru/jadwal "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":99,\"hari\":\"selasa\",\"jam_mulai\":\"07:00\",\"jam_selesai\":\"08:00\"}"; cek "mata pelajaran tidak ada" 422
call POST /api/guru/jadwal "$TG" "{\"semester\":\"ketiga\",\"id_mapel\":$IDM,\"hari\":\"selasa\",\"jam_mulai\":\"07:00\",\"jam_selesai\":\"08:00\"}"; cek "semester tidak dikenal" 422
call POST /api/guru/jadwal "$TG" "{\"id_kelas\":99999,\"id_tahun_ajaran\":99999,\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"rabu\",\"jam_mulai\":\"07:00\",\"jam_selesai\":\"08:00\"}"; cek "id_kelas dari klien diabaikan" 201; J4=$(idbaru)
memuat "jadwal tetap milik kelas sendiri" "\"id_kelas\":$IDK_UJI"
call PUT /api/guru/jadwal/$J1 "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"senin\",\"jam_mulai\":\"07:00\",\"jam_selesai\":\"07:45\"}"; cek "ubah jadwal sendiri" 200
call PUT /api/guru/jadwal/$J1 "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"senin\",\"jam_mulai\":\"07:30\",\"jam_selesai\":\"08:30\"}"; cek "ubah menjadi beririsan dengan jadwal lain" 409
call GET "/api/guru/jadwal?hari=senin" "$TG"; cek "filter hari" 200
call GET "/api/guru/jadwal?hari=minggu" "$TG"; cek "filter hari tidak valid" 400
call GET "/api/guru/jadwal?semester=genap" "$TG"; cek "filter semester" 200

echo "== Isolasi antar kelas (AC-15)"
U2="guru_tes$RANDOM"
call POST /api/admin/guru "$T" "{\"username\":\"$U2\",\"password\":\"rahasia123\",\"nama_guru\":\"Guru Lain Kelas\"}"; IDG2=$(idbaru)
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":5,\"nama_kelas\":\"5Q\",\"id_guru_wali\":$IDG2}"; IDK2=$(idbaru)
T2=$(login "$U2" rahasia123)
call POST /api/guru/jadwal "$T2" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"senin\",\"jam_mulai\":\"07:00\",\"jam_selesai\":\"08:00\"}"; cek "guru lain: jam sama di kelas lain tidak bentrok" 201; JX=$(idbaru)
call GET /api/guru/jadwal/$JX "$TG";    cek "baca jadwal kelas lain" 404
call PUT /api/guru/jadwal/$JX "$TG" "{\"semester\":\"ganjil\",\"id_mapel\":$IDM,\"hari\":\"senin\",\"jam_mulai\":\"10:00\",\"jam_selesai\":\"11:00\"}"; cek "ubah jadwal kelas lain" 404
call DELETE /api/guru/jadwal/$JX "$TG"; cek "hapus jadwal kelas lain" 404
call GET "/api/guru/jadwal" "$TG"; tidakmemuat "daftar tidak memuat jadwal kelas lain" "\"id_jadwal\":$JX,"

echo "== Piket"
call POST /api/guru/piket "$TG" "{\"hari\":\"senin\",\"keterangan\":\"Piket uji\",\"id_siswa\":[$IDSUJI]}"; cek "buat piket" 201; P1=$(idbaru)
memuat "siswa bertugas tampil" 'Siswa Uji Aktif'
call POST /api/guru/piket "$TG" "{\"hari\":\"senin\",\"id_siswa\":[]}"; cek "satu piket per hari" 409
call POST /api/guru/piket "$TG" "{\"hari\":\"selasa\",\"id_siswa\":[99999]}"; cek "siswa tidak ada di kelas" 422
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Siswa Luar Kelas\",\"nisn_resmi\":\"$(nisn)\"}"; IDSL=$(idbaru)
call POST /api/guru/piket "$TG" "{\"hari\":\"selasa\",\"id_siswa\":[$IDSL]}"; cek "siswa berstatus baru ditolak" 422
call POST /api/guru/piket "$TG" "{\"hari\":\"selasa\",\"id_siswa\":[$IDSP]}"; cek "siswa pindah ditolak" 422
call POST /api/guru/piket "$TG" "{\"hari\":\"minggu\"}"; cek "hari tidak valid" 422
call POST /api/guru/piket "$TG" "{\"hari\":\"selasa\",\"keterangan\":\"Tanpa siswa\"}"; cek "piket tanpa siswa diperbolehkan" 201; P2=$(idbaru)
call PUT /api/guru/piket/$P2 "$TG" "{\"hari\":\"senin\"}"; cek "pindah ke hari yang sudah terisi" 409
call PUT /api/guru/piket/$P2 "$TG" "{\"keterangan\":\"Diubah\",\"id_siswa\":[$IDSUJI]}"; cek "ubah keterangan dan siswa" 200; memuat "siswa terisi" 'Siswa Uji Aktif'
call PUT /api/guru/piket/$P2 "$TG" "{\"id_siswa\":[]}"; cek "kosongkan siswa" 200; tidakmemuat "siswa terhapus dari piket" 'Siswa Uji Aktif'
call GET /api/guru/piket "$TG"; cek "daftar piket" 200

call GET /api/guru/piket/$P1 "$T2";     cek "baca piket kelas lain" 404
call PUT /api/guru/piket/$P1 "$T2" '{"keterangan":"dibajak"}'; cek "ubah piket kelas lain" 404
call DELETE /api/guru/piket/$P1 "$T2";  cek "hapus piket kelas lain" 404
call POST /api/guru/piket "$T2" "{\"hari\":\"senin\",\"id_siswa\":[$IDSUJI]}"; cek "memasukkan siswa kelas lain ke piket" 422

echo "== Pembersihan"
call DELETE /api/guru/piket/$P1 "$TG"; cek "hapus piket 1" 200
call DELETE /api/guru/piket/$P2 "$TG"; cek "hapus piket 2" 200
call GET /api/guru/piket/$P1 "$TG";    cek "piket sudah terhapus" 404
for J in $J1 $J2 $J3 $J4; do call DELETE /api/guru/jadwal/$J "$TG"; cek "hapus jadwal $J" 200; done
call DELETE /api/guru/jadwal/$JX "$T2"; cek "hapus jadwal guru lain" 200
call DELETE /api/admin/mapel/$IDM "$T"; cek "hapus mapel uji" 200
call DELETE /api/admin/riwayat-kelas/$IDRP "$T"; cek "hapus penempatan siswa pindah" 200
call DELETE /api/admin/siswa/$IDSP "$T"; cek "hapus siswa pindah" 200
call DELETE /api/admin/siswa/$IDSL "$T"; cek "hapus siswa luar kelas" 200
call DELETE /api/admin/kelas/$IDK2 "$T"; cek "hapus kelas 5Q" 200
call DELETE /api/admin/guru/$IDG2 "$T"; cek "hapus guru kelas lain" 200
call DELETE /api/admin/guru/$IDG0 "$T"; cek "hapus guru tanpa kelas" 200

echo
[ $GAGAL -eq 0 ] && echo "SEMUA UJI LOLOS" || echo "ADA UJI YANG GAGAL (lihat tanda GAGAL di atas)"
