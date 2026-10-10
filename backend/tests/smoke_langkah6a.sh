#!/usr/bin/env bash
# Uji otomatis langkah 6a (endpoint Wali nilai/absensi, endpoint publik, konten website, unggah gambar).
# Prasyarat: php database/seeds/data_uji.php  dan  php database/seeds/data_awal.php sudah dijalankan.
#   bash tests/smoke_langkah6a.sh
# Data uji absensi/nilai diberi penanda (tanggal >= 2035, mapel NUJI%) dan dibersihkan di akhir oleh
# database/seeds/bersihkan_data_uji.php. Konten yang dibuat dihapus lewat API.
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
tidak_memuat() {
  if echo "$BODY" | grep -q "$2"; then echo "  GAGAL $1 (seharusnya TIDAK memuat: $2)"; echo "        $BODY"; GAGAL=1
  else echo "  OK    $1"; fi
}
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9][0-9]*' | head -1 | grep -o '[0-9]*$'; }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token; }

echo "== Persiapan"
T=$(login admin_uji admin12345)
TG=$(login guru_uji guru12345)
[ -z "$T" ] || [ -z "$TG" ] && { echo "Login admin_uji/guru_uji gagal. Jalankan dulu: php database/seeds/data_uji.php"; exit 1; }
call POST /api/wali/login "" '{"nisn":"0000000001"}'; TW=$(token)
[ -z "$TW" ] && { echo "Login Wali NISN 0000000001 gagal (data uji belum ada / siswa tidak aktif)."; exit 1; }
call GET /api/guru/siswa "$TG"
IDSUJI=$(echo "$BODY" | grep -o '"id_siswa":[0-9]*,"nama_siswa":"Siswa Uji Aktif"' | head -1 | grep -o '[0-9]*' | head -1)
[ -z "$IDSUJI" ] && { echo "Siswa Uji Aktif tidak ditemukan pada kelas guru_uji."; exit 1; }
call POST /api/admin/mapel "$T" "{\"kode_mapel\":\"NUJI$RANDOM\",\"nama_mapel\":\"Mapel Wali Uji\"}"; IDM=$(idbaru)

echo "== Wali: akses"
call GET /api/wali/nilai "";            cek "nilai tanpa token" 401
call GET /api/wali/absensi "";          cek "absensi tanpa token" 401
call GET /api/wali/nilai "$T";          cek "token Admin tidak dapat dipakai di endpoint Wali" 401
call GET /api/wali/nilai "$TG";         cek "token Guru tidak dapat dipakai di endpoint Wali" 401

echo "== Wali: nilai"
call GET /api/wali/nilai "$TW";         cek "nilai kosong tetap 200" 200
call POST /api/guru/nilai "$TG" "{\"id_siswa\":$IDSUJI,\"id_mapel\":$IDM,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":1,\"nilai\":80}"
call POST /api/guru/nilai "$TG" "{\"id_siswa\":$IDSUJI,\"id_mapel\":$IDM,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":2,\"nilai\":90}"
call POST /api/guru/nilai "$TG" "{\"id_siswa\":$IDSUJI,\"id_mapel\":$IDM,\"semester\":\"ganjil\",\"jenis\":\"ASTS\",\"nilai\":85}"
call POST /api/guru/nilai "$TG" "{\"id_siswa\":$IDSUJI,\"id_mapel\":$IDM,\"semester\":\"genap\",\"jenis\":\"ASAS\",\"nilai\":70}"
call GET /api/wali/nilai "$TW";         cek "nilai Wali" 200
memuat "mapel tampil" '"nama_mapel":"Mapel Wali Uji"'
memuat "UH berurutan" '"uh":\[{"nomor_uh":1,"nilai":80},{"nomor_uh":2,"nilai":90}\]'
memuat "ASTS ganjil" '"asts":85'
call GET "/api/wali/nilai?semester=genap" "$TW"; memuat "filter genap: ASAS" '"asas":70'; tidak_memuat "filter genap: tanpa ASTS 85" '"asts":85'
call GET "/api/wali/nilai?semester=ketiga" "$TW"; cek "semester salah" 400
call GET "/api/wali/nilai?id_siswa=99999" "$TW"; cek "id_siswa dari klien diabaikan" 200; memuat "tetap data token" '"nama_mapel":"Mapel Wali Uji"'
tidak_memuat "tanpa kontak wali" 'no_hp_wali'

echo "== Wali: absensi"
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"2035-01-03\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"2035-01-04\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"sakit\"}]}"
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"2035-02-01\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"alpa\"}]}"
call GET "/api/wali/absensi?bulan=2035-01" "$TW"; cek "absensi per bulan" 200
memuat "rekap hadir 1" '"hadir":1'; memuat "rekap sakit 1" '"sakit":1'; memuat "total 2 (Jan saja)" '"total":2'
tidak_memuat "Februari tidak ikut" '2035-02-01'
call GET "/api/wali/absensi?bulan=2035-02" "$TW"; memuat "Februari: alpa" '"alpa":1'
call GET "/api/wali/absensi?bulan=2035-13" "$TW"; cek "bulan tidak valid" 400
call GET "/api/wali/absensi?bulan=Januari" "$TW"; cek "bulan bukan YYYY-MM" 400
call GET "/api/wali/absensi?semester=x" "$TW"; cek "semester salah" 400
call GET "/api/wali/absensi" "$TW"; cek "tanpa filter" 200

echo "== Publik"
call GET /api/public/guru "";               cek "guru" 200; tidak_memuat "tanpa email" '"email"'; tidak_memuat "tanpa telepon" 'no_telepon'; tidak_memuat "tanpa id akun" 'id_user'
call GET /api/public/program-pembiasaan ""; cek "program pembiasaan" 200
call GET /api/public/ekstrakurikuler "";    cek "ekstrakurikuler" 200
call GET /api/public/event "";              cek "event" 200
call GET "/api/public/event?kategori=lomba" ""; cek "kategori event salah" 400
call GET /api/public/galeri "";             cek "galeri mandiri" 200
call GET /api/public/mading "";             cek "mading" 200
call GET /api/public/pengaturan "";         cek "pengaturan" 200; memuat "dua kunci" '"ppdb_form_url"'; memuat "video" '"video_profil_url"'
call POST /api/public/guru "" '{}';         cek "publik hanya GET" 405

echo "== Konten: akses"
call GET /api/admin/event "";               cek "event admin tanpa token" 401
call GET /api/admin/event "$TG";            cek "Guru tidak bisa kelola konten" 403
call POST /api/admin/ekstrakurikuler "$TG" '{"nama_ekskul":"X"}'; cek "Guru tidak bisa menambah ekskul" 403

echo "== Ekstrakurikuler + relasi Guru"
call POST /api/admin/ekstrakurikuler "$T" '{"nama_ekskul":""}';   cek "nama kosong" 422
call POST /api/admin/ekstrakurikuler "$T" '{"nama_ekskul":"Ekskul Uji 6a","deskripsi":"Latihan uji","ikon":"javascript:alert(1)"}'; cek "ikon berbahaya ditolak" 422
call POST /api/admin/ekstrakurikuler "$T" '{"nama_ekskul":"Ekskul Uji 6a","deskripsi":"Latihan uji","ikon":"https://contoh.id/ikon.png"}'; cek "buat ekskul" 201; IDE=$(idbaru)
call PUT /api/admin/ekstrakurikuler/$IDE "$T" '{"deskripsi":"Deskripsi baru"}'; cek "ubah parsial" 200; memuat "nama tetap" 'Ekskul Uji 6a'; memuat "deskripsi berubah" 'Deskripsi baru'
call GET /api/public/ekstrakurikuler "";   memuat "tampil di publik" 'Ekskul Uji 6a'
call GET "/api/admin/guru?q=Guru%20Uji" "$T"; IDG=$(echo "$BODY" | grep -o '"id_guru":[0-9]*,"id_user":[0-9]*,"username":"guru_uji"' | head -1 | grep -o '[0-9]*' | head -1)
call POST /api/admin/guru-ekskul "$T" "{\"id_guru\":$IDG,\"id_ekskul\":$IDE}"; cek "kaitkan Guru-ekskul" 201; IDGE=$(idbaru)
call POST /api/admin/guru-ekskul "$T" "{\"id_guru\":$IDG,\"id_ekskul\":$IDE}"; cek "kaitan ganda" 409
call POST /api/admin/guru-ekskul "$T" "{\"id_guru\":99999999,\"id_ekskul\":$IDE}"; cek "guru tidak ada" 422
call GET "/api/admin/guru-ekskul?id_ekskul=$IDE" "$T"; memuat "daftar kaitan" 'Ekskul Uji 6a'
call DELETE /api/admin/guru-ekskul/$IDGE "$T"; cek "lepas kaitan" 200

echo "== Event + galeri"
call POST /api/admin/event "$T" '{"nama_event":"Event Uji","kategori":"lomba"}'; cek "kategori salah" 422
call POST /api/admin/event "$T" '{"nama_event":"Event Uji","kategori":"prestasi","tanggal":"2035-13-45"}'; cek "tanggal salah" 422
call POST /api/admin/event "$T" '{"nama_event":"Event Uji Tampil","kategori":"prestasi","tanggal":"2035-01-10","status_tampil":true}'; cek "event tampil" 201; IDV1=$(idbaru)
call POST /api/admin/event "$T" '{"nama_event":"Event Uji Tersembunyi","kategori":"kegiatan","status_tampil":false}'; cek "event tersembunyi" 201; IDV2=$(idbaru)
call POST /api/admin/galeri "$T" "{\"id_event\":$IDV1,\"file_gambar\":\"https://contoh.id/foto1.jpg\",\"keterangan\":\"Foto uji\"}"; cek "galeri event" 201; IDGL1=$(idbaru)
call POST /api/admin/galeri "$T" '{"file_gambar":"https://contoh.id/fasilitas.jpg","keterangan":"Galeri mandiri uji"}'; cek "galeri mandiri" 201; IDGL2=$(idbaru)
call POST /api/admin/galeri "$T" '{"id_event":99999999,"file_gambar":"https://contoh.id/x.jpg"}'; cek "event tidak ada" 422
call POST /api/admin/galeri "$T" '{"file_gambar":"http://contoh.id/x.jpg"}'; cek "http (bukan https) ditolak" 422
call POST /api/admin/galeri "$T" '{"file_gambar":"../../etc/passwd"}'; cek "path berbahaya ditolak" 422
call GET /api/public/event ""; memuat "event tampil di publik" 'Event Uji Tampil'; tidak_memuat "event tersembunyi tidak tampil" 'Event Uji Tersembunyi'; memuat "membawa galeri" 'foto1.jpg'; tidak_memuat "tanpa status_tampil" 'status_tampil'
call GET "/api/public/event?kategori=kegiatan" ""; tidak_memuat "filter kategori" 'Event Uji Tampil'
call GET /api/public/galeri ""; memuat "galeri mandiri tampil" 'Galeri mandiri uji'; tidak_memuat "foto event tidak di galeri mandiri" 'foto1.jpg'
call GET /api/admin/event "$T"; memuat "admin melihat yang tersembunyi" 'Event Uji Tersembunyi'
call PUT /api/admin/event/$IDV2 "$T" '{"status_tampil":true}'; cek "tampilkan event" 200
call GET /api/public/event ""; memuat "kini tampil" 'Event Uji Tersembunyi'
call PUT /api/admin/galeri/$IDGL2 "$T" "{\"id_event\":$IDV2}"; cek "pindahkan galeri ke event" 200
call GET /api/public/galeri ""; tidak_memuat "tidak lagi mandiri" 'Galeri mandiri uji'
call DELETE /api/admin/event/$IDV1 "$T"; cek "hapus event" 200
call GET /api/admin/galeri/$IDGL1 "$T"; cek "galeri event ikut terhapus (CASCADE)" 404
call DELETE /api/admin/event/$IDV2 "$T"; cek "hapus event 2" 200

echo "== Mading"
call POST /api/admin/mading "$T" '{"judul_karya":"Karya Uji"}'; cek "gambar wajib" 422
call POST /api/admin/mading "$T" '{"judul_karya":"Karya Uji Tampil","nama_penulis":"Siswa","file_gambar":"https://contoh.id/karya.jpg"}'; cek "mading tampil" 201; IDMD1=$(idbaru)
call POST /api/admin/mading "$T" '{"judul_karya":"Karya Uji Sembunyi","file_gambar":"https://contoh.id/k2.jpg","status_tampil":false}'; cek "mading disembunyikan" 201; IDMD2=$(idbaru)
call GET /api/public/mading ""; memuat "tampil" 'Karya Uji Tampil'; tidak_memuat "tersembunyi tidak tampil" 'Karya Uji Sembunyi'
call PUT /api/admin/mading/$IDMD1 "$T" '{"status_tampil":"mungkin"}'; cek "status_tampil bukan boolean" 422
call DELETE /api/admin/mading/$IDMD1 "$T"; cek "hapus mading 1" 200
call DELETE /api/admin/mading/$IDMD2 "$T"; cek "hapus mading 2" 200
call GET /api/admin/mading/$IDMD1 "$T"; cek "sudah terhapus" 404

echo "== Program pembiasaan"
call GET /api/admin/program-pembiasaan "$T"; cek "daftar program" 200
IDP=$(echo "$BODY" | grep -o '"id_program":[0-9]*,"hari":"senin"' | head -1 | grep -o '[0-9]*' | head -1)
if [ -z "$IDP" ]; then echo "  LEWAT program belum di-seed. Jalankan: php database/seeds/data_awal.php"; GAGAL=1
else
  call POST /api/admin/program-pembiasaan "$T" '{}'; cek "tidak ada rute tambah" 405
  call DELETE /api/admin/program-pembiasaan/$IDP "$T"; cek "tidak ada rute hapus" 405
  call PUT /api/admin/program-pembiasaan/$IDP "$T" '{}'; cek "PUT kosong" 422
  call PUT /api/admin/program-pembiasaan/$IDP "$T" '{"deskripsi":"Deskripsi uji","hari":"minggu"}'; cek "ubah deskripsi" 200; memuat "hari tetap senin" '"hari":"senin"'
  call PUT /api/admin/program-pembiasaan/$IDP "$T" '{"deskripsi":null}'; cek "kosongkan deskripsi (pulihkan)" 200
  call GET /api/public/program-pembiasaan ""; memuat "urutan mulai Senin" '"data":\[{"hari":"senin"'
fi

echo "== Pengaturan"
call GET /api/admin/pengaturan "$TG"; cek "Guru tidak bisa" 403
call PUT /api/admin/pengaturan "$T" '{}'; cek "kosong" 422
call PUT /api/admin/pengaturan "$T" '{"ppdb_form_url":"javascript:alert(1)"}'; cek "javascript: ditolak" 422
call PUT /api/admin/pengaturan "$T" '{"ppdb_form_url":"ftp://contoh.id/form"}'; cek "ftp ditolak" 422
call PUT /api/admin/pengaturan "$T" '{"ppdb_form_url":"https://forms.gle/ujiPPDB","video_profil_url":"https://www.youtube.com/embed/uji"}'; cek "URL valid" 200
memuat "pengubah tercatat" '"diubah_oleh":"admin_uji"'
call GET /api/public/pengaturan ""; memuat "publik: PPDB" 'forms.gle/ujiPPDB'; tidak_memuat "publik tanpa metadata" 'diubah_oleh'
call PUT /api/admin/pengaturan "$T" '{"ppdb_form_url":null,"video_profil_url":null}'; cek "kosongkan (pulihkan)" 200

echo "== Unggah gambar"
PNG="$(mktemp --suffix=.png)"; TXT="$(mktemp --suffix=.png)"
echo 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==' | base64 -d > "$PNG"
echo '<?php echo "bukan gambar"; ?>' > "$TXT"
up() { local out; out=$(curl -s -w $'\n%{http_code}' -X POST "$BASE/api/admin/upload" -H "Authorization: Bearer $1" -F "file=@$2"); CODE=${out##*$'\n'}; BODY=${out%$'\n'*}; }
up "" "$PNG";   cek "tanpa token" 401
up "$TG" "$PNG"; cek "Guru tidak bisa unggah" 403
up "$T" "$TXT"; cek "berkas bukan gambar (nama .png) ditolak" 422
up "$T" "$PNG"; cek "gambar valid" 201
UPATH=$(echo "$BODY" | grep -o '"path":"uploads/[a-f0-9]*\.png"' | cut -d'"' -f4)
[ -n "$UPATH" ] && memuat "path acak" 'uploads/[a-f0-9]\{32\}\.png' || { echo "  GAGAL path tidak ditemukan"; GAGAL=1; }
if [ -n "$UPATH" ]; then
  code=$(curl -s -o /dev/null -w '%{http_code}' "$BASE/$UPATH"); [ "$code" = "200" ] && echo "  OK    gambar dapat dibuka ($BASE/$UPATH)" || { echo "  GAGAL gambar tidak dapat dibuka (HTTP $code)"; GAGAL=1; }
  call POST /api/admin/mading "$T" "{\"judul_karya\":\"Karya Unggah Uji\",\"file_gambar\":\"$UPATH\"}"; cek "path unggahan diterima di konten" 201; IDMU=$(idbaru)
  call DELETE /api/admin/mading/$IDMU "$T"
  rm -f "$DIR/../public/$UPATH"
fi
rm -f "$PNG" "$TXT"

echo "== Bersihkan"
call DELETE /api/admin/ekstrakurikuler/$IDE "$T"; cek "hapus ekskul uji" 200
php "$DIR/../../database/seeds/bersihkan_data_uji.php"

echo
[ "$GAGAL" = "0" ] && echo "SEMUA UJI LANGKAH 6a LOLOS." || { echo "ADA UJI YANG GAGAL (lihat di atas)."; exit 1; }
