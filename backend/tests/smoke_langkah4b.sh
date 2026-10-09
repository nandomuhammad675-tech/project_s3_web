#!/usr/bin/env bash
# Uji otomatis langkah 4b (absensi, dasbor, nilai, kedisiplinan).
# Prasyarat: php database/seeds/data_uji.php sudah dijalankan.
#   bash tests/smoke_langkah4b.sh
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
tidakmemuat() {
  if echo "$BODY" | grep -q "$2"; then echo "  GAGAL $1 (seharusnya TIDAK memuat: $2)"; echo "        $BODY"; GAGAL=1
  else echo "  OK    $1"; fi
}
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9][0-9]*' | head -1 | grep -o '[0-9]*$'; }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token; }
bersihkan() { php "$DIR/../../database/seeds/bersihkan_data_uji.php" 2>&1 | sed 's/^/        /' || echo "  (peringatan: pembersihan data uji gagal)"; }

echo "== Persiapan"
bersihkan
T=$(login admin_uji admin12345)
TG=$(login guru_uji guru12345)
[ -z "$T" ] || [ -z "$TG" ] && { echo "Login admin_uji/guru_uji gagal. Jalankan dulu: php database/seeds/data_uji.php"; exit 1; }

call GET /api/admin/tahun-ajaran "$T"
IDAKTIF=$(echo "$BODY" | grep -o '"id_tahun_ajaran":[0-9]*,"nama_tahun":"2026/2027"' | head -1 | grep -o '[0-9]*' | head -1)
call GET /api/guru/me "$TG"
IDK_UJI=$(echo "$BODY" | grep -o '"kelas":{"id_kelas":[0-9]*' | grep -o '[0-9]*$')
call GET /api/guru/siswa "$TG"
IDSUJI=$(echo "$BODY" | grep -o '"id_siswa":[0-9]*,"nama_siswa":"Siswa Uji Aktif"' | head -1 | grep -o '[0-9]*' | head -1)
[ -z "$IDAKTIF" ] || [ -z "$IDK_UJI" ] || [ -z "$IDSUJI" ] && { echo "Data uji tidak lengkap (tahun/kelas/siswa). Jalankan: php database/seeds/data_uji.php"; exit 1; }

# tanggal acak jauh di masa depan (>= 2035, supaya mudah dibersihkan) pada hari yang diketahui
MON=$(date -d "2040-01-02 + $((RANDOM % 400)) weeks" +%F)
[ "$(date -d "$MON" +%u)" != "1" ] && { echo "Perhitungan tanggal salah (date tidak mendukung -d?)"; exit 1; }
SAT=$(date -d "$MON - 2 days" +%F); SUN=$(date -d "$MON - 1 day" +%F)
TUE=$(date -d "$MON + 1 day" +%F); WED=$(date -d "$MON + 2 days" +%F)
THU=$(date -d "$MON + 3 days" +%F); FRI=$(date -d "$MON + 4 days" +%F)
echo "  (tanggal uji: Sabtu $SAT, Minggu $SUN, Senin $MON)"

# guru + kelas kedua untuk uji isolasi
U2="guru_tes$RANDOM"
call POST /api/admin/guru "$T" "{\"username\":\"$U2\",\"password\":\"rahasia123\",\"nama_guru\":\"Guru Kelas Lain\"}"; IDG2=$(idbaru)
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":5,\"nama_kelas\":\"5Q\",\"id_guru_wali\":$IDG2}"; IDK2=$(idbaru)
T2=$(login "$U2" rahasia123)
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Siswa Luar Kelas\",\"nisn_resmi\":\"$(printf '%010d' $((RANDOM * RANDOM)))\"}"; IDSL=$(idbaru)

echo "== Absensi: akses"
call POST /api/guru/absensi "$T" "{\"tanggal\":\"$MON\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"; cek "Admin tidak bisa mengisi absensi" 403
call POST /api/guru/absensi "" "{}"; cek "tanpa token" 401

echo "== Absensi: validasi tanggal"
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$SUN\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"; cek "hari Minggu ditolak" 422; memuat "pesan hari Minggu" 'Minggu'
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"2040-02-30\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"; cek "tanggal tidak valid" 422
call POST /api/guru/absensi "$TG" "{\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"; cek "tanggal wajib" 422
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$MON\",\"items\":[]}"; cek "items kosong" 422
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$MON\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"bolos\"}]}"; cek "status tidak dikenal" 422
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$MON\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"},{\"id_siswa\":$IDSUJI,\"status\":\"sakit\"}]}"; cek "siswa dobel dalam satu batch" 422
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$MON\",\"items\":[{\"id_siswa\":$IDSL,\"status\":\"hadir\"}]}"; cek "siswa luar kelas / berstatus baru" 422
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$MON\",\"items\":[{\"id_siswa\":99999,\"status\":\"hadir\"}]}"; cek "siswa tidak ada" 422

echo "== Absensi: hari libur"
call POST /api/admin/hari-libur "$T" "{\"tanggal_libur\":\"$TUE\",\"keterangan\":\"Libur semua kelas uji\"}"; LU=$(idbaru)
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$TUE\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"; cek "libur untuk semua kelas ditolak" 422; memuat "pesan memuat keterangan libur" 'Libur semua kelas uji'
call GET "/api/guru/dasbor/absensi?date=$TUE" "$TG"; memuat "dasbor Guru: libur" '"status":"libur"'
call POST /api/admin/hari-libur "$T" "{\"tanggal_libur\":\"$WED\",\"keterangan\":\"Libur kelas 1A\",\"id_kelas\":[$IDK_UJI]}"; LK=$(idbaru)
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$WED\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"; cek "libur khusus kelas ini ditolak" 422
call POST /api/admin/hari-libur "$T" "{\"tanggal_libur\":\"$THU\",\"keterangan\":\"Libur kelas lain\",\"id_kelas\":[$IDK2]}"; LL=$(idbaru)
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$THU\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"izin\"}]}"; cek "libur khusus kelas LAIN tidak menghalangi" 201

echo "== Absensi: simpan, duplikat, koreksi"
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$SAT\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"; cek "hari Sabtu diterima (hari efektif)" 201
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$MON\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"hadir\"}]}"; cek "simpan absensi hari Senin" 201; memuat "status kelas menjadi sudah" '"status_kelas":"sudah"'
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"$MON\",\"items\":[{\"id_siswa\":$IDSUJI,\"status\":\"sakit\"}]}"; cek "siswa + tanggal yang sama (duplikat)" 409
call GET "/api/guru/absensi?date=$MON" "$TG"; cek "lihat absensi tanggal itu" 200; memuat "status tercatat" '"status":"hadir"'
IDA=$(echo "$BODY" | grep -o '"id_absensi":[0-9][0-9]*' | head -1 | grep -o '[0-9]*$')
call GET "/api/guru/absensi?date=bukan-tanggal" "$TG"; cek "format date salah" 400
call GET "/api/guru/absensi?date=$FRI" "$TG"; cek "tanggal belum diisi" 200; memuat "belum diabsen" '"status_kelas":"belum"'
call GET "/api/guru/absensi?date=$SUN" "$TG"; memuat "hari Minggu tampil libur" '"status_kelas":"libur"'
call PUT /api/guru/absensi/$IDA "$TG" '{"status":"sakit"}'; cek "perbaiki absensi sendiri" 200; memuat "status berubah" '"status":"sakit"'
call PUT /api/guru/absensi/$IDA "$TG" '{"status":"bolos"}'; cek "perbaikan dengan status salah" 422
call PUT /api/guru/absensi/$IDA "$T2" '{"status":"alpa"}'; cek "guru lain memperbaiki absensi kelas ini" 404
call DELETE /api/guru/absensi/$IDA "$TG"; cek "absensi tidak punya DELETE" 405

echo "== Dasbor status absensi"
call GET "/api/guru/dasbor/absensi?date=$MON" "$TG"; cek "dasbor Guru" 200; memuat "Senin: sudah" '"status":"sudah"'
call GET "/api/guru/dasbor/absensi?date=$FRI" "$TG"; memuat "Jumat: belum" '"status":"belum"'
call GET "/api/guru/dasbor/absensi?date=$SUN" "$TG"; memuat "Minggu: libur" '"status":"libur"'
call GET "/api/guru/dasbor/absensi?date=$WED" "$TG"; memuat "libur khusus kelas: libur" '"status":"libur"'
call GET "/api/admin/dasbor/absensi?date=$MON" "$T"; cek "dasbor Admin (seluruh kelas)" 200
memuat "kelas 1A sudah" '"nama_kelas":"1A","tingkat_kelas":1,"nama_guru_wali":"Guru Uji","status":"sudah"'
memuat "kelas 5Q belum" '"nama_kelas":"5Q","tingkat_kelas":5,"nama_guru_wali":"Guru Kelas Lain","status":"belum"'
call GET "/api/admin/dasbor/absensi?date=$THU" "$T"; memuat "libur khusus 5Q: 5Q libur" '"nama_kelas":"5Q","tingkat_kelas":5,"nama_guru_wali":"Guru Kelas Lain","status":"libur"'
call GET "/api/admin/dasbor/absensi?date=$MON" "$TG"; cek "Guru tidak bisa membuka dasbor Admin" 403
call GET "/api/admin/dasbor/absensi?date=salah" "$T"; cek "dasbor Admin: date salah" 400

echo "== Nilai"
KM="NUJI$RANDOM"
call POST /api/admin/mapel "$T" "{\"kode_mapel\":\"$KM\",\"nama_mapel\":\"Mapel Nilai Uji\"}"; IDM=$(idbaru)
N="\"id_siswa\":$IDSUJI,\"id_mapel\":$IDM"
call POST /api/guru/nilai "$T" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":1,\"nilai\":80}"; cek "Admin tidak bisa menginput nilai" 403
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":1,\"nilai\":80}"; cek "UH-1" 201; NUH1=$(idbaru)
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":2,\"nilai\":90}"; cek "UH-2 (UH boleh berulang)" 201; NUH2=$(idbaru)
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":1,\"nilai\":70}"; cek "UH-1 kembar" 409
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"ASTS\",\"nilai\":85}"; cek "ASTS" 201; NASTS=$(idbaru)
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"ASTS\",\"nilai\":60}"; cek "ASTS ganda ditolak" 409
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"ASAS\",\"nilai\":88}"; cek "ASAS" 201; NASAS=$(idbaru)
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"genap\",\"jenis\":\"ASTS\",\"nilai\":75}"; cek "ASTS semester lain boleh" 201
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":3,\"nilai\":101}"; cek "nilai di atas 100" 422
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":3,\"nilai\":-1}"; cek "nilai negatif" 422
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nomor_uh\":3,\"nilai\":\"abc\"}"; cek "nilai bukan angka" 422
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UH\",\"nilai\":80}"; cek "UH tanpa nomor" 422
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"ASAS\",\"nomor_uh\":1,\"nilai\":80}"; cek "ASAS dengan nomor UH" 422
call POST /api/guru/nilai "$TG" "{$N,\"semester\":\"ganjil\",\"jenis\":\"UTS\",\"nilai\":80}"; cek "jenis UTS tidak dipakai" 422
call POST /api/guru/nilai "$TG" "{\"id_siswa\":$IDSL,\"id_mapel\":$IDM,\"semester\":\"ganjil\",\"jenis\":\"ASTS\",\"nilai\":80}"; cek "siswa luar kelas" 422
call POST /api/guru/nilai "$TG" "{\"id_siswa\":$IDSUJI,\"id_mapel\":250,\"semester\":\"ganjil\",\"jenis\":\"ASTS\",\"nilai\":80}"; cek "mata pelajaran tidak ada" 422
call PUT /api/guru/nilai/$NUH1 "$TG" '{"nilai":95}'; cek "perbaiki nilai" 200; memuat "nilai berubah" '"nilai":95'
call PUT /api/guru/nilai/$NUH1 "$TG" '{"nilai":150}'; cek "perbaikan di luar 0-100" 422
call PUT /api/guru/nilai/$NUH1 "$T2" '{"nilai":10}'; cek "guru lain memperbaiki nilai kelas ini" 404
call DELETE /api/guru/nilai/$NUH1 "$T2"; cek "guru lain menghapus nilai kelas ini" 404
call GET /api/guru/nilai/$NUH1 "$T2"; cek "guru lain membaca nilai kelas ini" 404
call DELETE /api/guru/nilai/$NASTS "$TG"; cek "ASTS tidak dapat dihapus" 422
call DELETE /api/guru/nilai/$NASAS "$TG"; cek "ASAS tidak dapat dihapus" 422
call DELETE /api/guru/nilai/$NUH2 "$TG"; cek "UH yang salah input dapat dihapus" 200
call GET "/api/guru/nilai?id_mapel=$IDM&semester=ganjil" "$TG"; cek "daftar nilai + filter" 200; memuat "ASTS tampil" '"jenis":"ASTS"'; tidakmemuat "UH-2 sudah terhapus" '"nomor_uh":2'
call GET "/api/guru/nilai?semester=ketiga" "$TG"; cek "filter semester salah" 400
call GET "/api/guru/nilai?id_siswa=abc" "$TG"; cek "filter siswa bukan angka" 400

echo "== Kedisiplinan"
call POST /api/guru/disiplin "$T" "{\"id_siswa\":$IDSUJI,\"tanggal\":\"$MON\",\"jenis_pelanggaran\":\"UJI-Terlambat\",\"catatan\":\"x\"}"; cek "Admin tidak bisa menambah catatan" 403
call POST /api/guru/disiplin "$TG" "{\"id_siswa\":$IDSUJI,\"tanggal\":\"$MON\",\"jenis_pelanggaran\":\"UJI-Terlambat\",\"catatan\":\"Datang pukul 07.30\"}"; cek "tambah catatan" 201; CD=$(idbaru)
call POST /api/guru/disiplin "$TG" "{\"id_siswa\":$IDSUJI,\"tanggal\":\"$MON\",\"jenis_pelanggaran\":\"UJI-Terlambat\"}"; cek "catatan wajib" 422
call POST /api/guru/disiplin "$TG" "{\"id_siswa\":$IDSUJI,\"tanggal\":\"2040-13-01\",\"jenis_pelanggaran\":\"UJI-X\",\"catatan\":\"x\"}"; cek "tanggal tidak valid" 422
call POST /api/guru/disiplin "$TG" "{\"id_siswa\":$IDSL,\"tanggal\":\"$MON\",\"jenis_pelanggaran\":\"UJI-X\",\"catatan\":\"x\"}"; cek "siswa luar kelas" 422
call PUT /api/guru/disiplin/$CD "$TG" '{"catatan":"Datang pukul 07.45"}'; cek "perbaiki catatan" 200; memuat "catatan berubah" '07.45'; memuat "jenis tidak ikut hilang" 'UJI-Terlambat'
call PUT /api/guru/disiplin/$CD "$TG" "{\"id_siswa\":$IDSL}"; cek "siswa pada catatan tidak dapat diganti" 422
call DELETE /api/guru/disiplin/$CD "$TG"; cek "catatan kedisiplinan tidak dapat dihapus (Guru)" 405
call DELETE /api/guru/disiplin/$CD "$T"; cek "catatan kedisiplinan tidak dapat dihapus (Admin)" 405
call GET /api/guru/disiplin/$CD "$T2"; cek "guru lain membaca catatan kelas ini" 404
call PUT /api/guru/disiplin/$CD "$T2" '{"catatan":"dibajak"}'; cek "guru lain memperbaiki catatan kelas ini" 404
call GET "/api/guru/disiplin?id_siswa=$IDSUJI" "$TG"; cek "daftar catatan per siswa" 200; memuat "catatan tampil" 'UJI-Terlambat'

echo "== Pembersihan"
for L in $LU $LK $LL; do call DELETE /api/admin/hari-libur/$L "$T"; cek "hapus hari libur uji $L" 200; done
call DELETE /api/admin/siswa/$IDSL "$T"; cek "hapus siswa luar kelas" 200
call DELETE /api/admin/kelas/$IDK2 "$T"; cek "hapus kelas 5Q" 200
call DELETE /api/admin/guru/$IDG2 "$T"; cek "hapus guru kelas lain" 200
bersihkan

echo
[ $GAGAL -eq 0 ] && echo "SEMUA UJI LOLOS" || echo "ADA UJI YANG GAGAL (lihat tanda GAGAL di atas)"
