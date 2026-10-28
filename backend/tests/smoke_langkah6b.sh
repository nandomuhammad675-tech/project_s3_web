#!/usr/bin/env bash
# Uji otomatis langkah 6b (baca akademik Admin + laporan semester JSON/Excel).
# Prasyarat: data_uji.php sudah dijalankan; phpoffice/phpspreadsheet terpasang (composer require phpoffice/phpspreadsheet).
#   bash tests/smoke_langkah6b.sh
# Data uji (absensi 2035-03-xx, mapel NUJI%) dibersihkan di akhir oleh database/seeds/bersihkan_data_uji.php.
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
  else echo "  GAGAL $1 (harapan $2, dapat $CODE)"; echo "        ${BODY:0:300}"; GAGAL=1; fi
}
memuat() {
  if echo "$BODY" | grep -q "$2"; then echo "  OK    $1"
  else echo "  GAGAL $1 (tidak memuat: $2)"; echo "        ${BODY:0:300}"; GAGAL=1; fi
}
tidak_memuat() {
  if echo "$BODY" | grep -q "$2"; then echo "  GAGAL $1 (seharusnya TIDAK memuat: $2)"; echo "        ${BODY:0:300}"; GAGAL=1
  else echo "  OK    $1"; fi
}
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9][0-9]*' | head -1 | grep -o '[0-9]*$'; }
login()  { call POST /api/auth/login "" "{\"username\":\"$1\",\"password\":\"$2\"}"; token; }

# unduh XLSX: unduh TOKEN PATH BERKAS  -> mengisi CODE, TIPE, NAMA
unduh() {
  local h; h="$(mktemp)"
  CODE=$(curl -s -o "$3" -D "$h" -w '%{http_code}' -H "Authorization: Bearer $1" "$BASE$2")
  TIPE=$(grep -i '^content-type:' "$h" | tr -d '\r' | head -1)
  NAMA=$(grep -i '^content-disposition:' "$h" | tr -d '\r' | head -1)
  rm -f "$h"
}
# sheet BERKAS -> daftar nama sheet; isi BERKAS -> seluruh teks sel (untuk dicari)
php_baca() {
  php -r 'require $argv[1]."/vendor/autoload.php"; $wb=\PhpOffice\PhpSpreadsheet\IOFactory::load($argv[2]);
    if($argv[3]==="sheet"){echo implode("|",$wb->getSheetNames());}
    else{foreach($wb->getAllSheets() as $s){foreach($s->toArray(null,false,false,false) as $r){echo implode("\t",array_map("strval",$r)),"\n";}}}' "$DIR/.." "$1" "$2"
}

echo "== Persiapan"
T=$(login admin_uji admin12345); TG=$(login guru_uji guru12345)
[ -z "$T" ] || [ -z "$TG" ] && { echo "Login admin_uji/guru_uji gagal. Jalankan dulu: php database/seeds/data_uji.php"; exit 1; }
php -r 'require $argv[1]."/vendor/autoload.php"; exit(class_exists("PhpOffice\\PhpSpreadsheet\\Spreadsheet")?0:1);' "$DIR/.." || { echo "phpoffice/phpspreadsheet belum terpasang: composer require phpoffice/phpspreadsheet"; exit 1; }
call GET /api/guru/me "$TG"
IDT=$(echo "$BODY" | grep -o '"id_tahun_ajaran":[0-9]*' | head -1 | grep -o '[0-9]*')
SEM=$(echo "$BODY" | grep -o '"semester":"[a-z]*"' | head -1 | cut -d'"' -f4)
IDK=$(echo "$BODY" | grep -o '"kelas":{"id_kelas":[0-9]*' | grep -o '[0-9]*$')
[ -z "$IDT" ] || [ -z "$SEM" ] || [ -z "$IDK" ] && { echo "guru_uji belum menjadi wali kelas pada tahun ajaran aktif."; exit 1; }
echo "  tahun=$IDT semester=$SEM kelas=$IDK"
call GET /api/guru/siswa "$TG"
IDS=$(echo "$BODY" | grep -o '"id_siswa":[0-9]*,"nama_siswa":"Siswa Uji Aktif"' | head -1 | grep -o '[0-9]*' | head -1)
[ -z "$IDS" ] && { echo "Siswa Uji Aktif tidak ditemukan."; exit 1; }
call POST /api/admin/mapel "$T" "{\"kode_mapel\":\"NUJI$RANDOM\",\"nama_mapel\":\"Mapel Laporan Uji\"}"; IDM=$(idbaru)
for n in 1 2; do call POST /api/guru/nilai "$TG" "{\"id_siswa\":$IDS,\"id_mapel\":$IDM,\"semester\":\"$SEM\",\"jenis\":\"UH\",\"nomor_uh\":$n,\"nilai\":$((70+n*10))}"; done
call POST /api/guru/nilai "$TG" "{\"id_siswa\":$IDS,\"id_mapel\":$IDM,\"semester\":\"$SEM\",\"jenis\":\"ASTS\",\"nilai\":88}"
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"2035-03-06\",\"items\":[{\"id_siswa\":$IDS,\"status\":\"hadir\"}]}"
call POST /api/guru/absensi "$TG" "{\"tanggal\":\"2035-03-07\",\"items\":[{\"id_siswa\":$IDS,\"status\":\"izin\"}]}"

echo "== Admin: baca akademik (read-only)"
call GET /api/admin/akademik/absensi "";       cek "tanpa token" 401
call GET /api/admin/akademik/absensi "$TG";    cek "Guru tidak boleh" 403
call GET "/api/admin/akademik/absensi?id_kelas=$IDK&dari=2035-03-01&sampai=2035-03-31" "$T"; cek "absensi" 200
memuat "memuat siswa uji" 'Siswa Uji Aktif'; memuat "memuat nama guru" 'nama_guru'; memuat "total 2" '"total":2'
call GET "/api/admin/akademik/absensi?status=izin&dari=2035-03-01" "$T"; memuat "filter status" '"status":"izin"'; tidak_memuat "tanpa hadir" '"status":"hadir"'
call GET "/api/admin/akademik/absensi?status=bolos" "$T";   cek "status salah" 400
call GET "/api/admin/akademik/absensi?dari=31-03-2035" "$T"; cek "format tanggal salah" 400
call GET "/api/admin/akademik/absensi?dari=2035-04-01&sampai=2035-03-01" "$T"; cek "rentang terbalik" 400
call GET "/api/admin/akademik/absensi?id_kelas=abc" "$T";   cek "id_kelas bukan angka" 400
call GET "/api/admin/akademik/absensi?id_tahun_ajaran=99999999" "$T"; cek "tahun tidak ada" 404
call GET "/api/admin/akademik/nilai?id_mapel=$IDM" "$T";    cek "nilai" 200; memuat "UH-nya ada" '"jenis":"UH"'; memuat "ASTS ada" '"nilai":88'
call GET "/api/admin/akademik/nilai?id_mapel=$IDM&jenis=ASTS" "$T"; tidak_memuat "filter jenis" '"jenis":"UH"'
call GET "/api/admin/akademik/nilai?jenis=UTS" "$T";        cek "jenis salah" 400
call GET "/api/admin/akademik/disiplin" "$T";               cek "disiplin" 200
call POST /api/admin/akademik/absensi "$T" '{}';            cek "tidak ada rute tulis (POST)" 405
call PUT /api/admin/akademik/nilai "$T" '{}';               cek "tidak ada rute tulis (PUT)" 405
call DELETE /api/admin/akademik/disiplin "$T";              cek "tidak ada rute tulis (DELETE)" 405

echo "== Laporan: parameter dan akses"
call GET "/api/guru/laporan" "$TG";                         cek "Guru tanpa parameter" 400
call GET "/api/guru/laporan?semester=$SEM" "$TG";           cek "tanpa tahun_ajaran" 400
call GET "/api/guru/laporan?semester=ketiga&tahun_ajaran=$IDT" "$TG"; cek "semester salah" 400
call GET "/api/guru/laporan?semester=$SEM&tahun_ajaran=$IDT" ""; cek "tanpa token" 401
call GET "/api/guru/laporan?semester=$SEM&tahun_ajaran=$IDT" "$T"; cek "token Admin di rute Guru" 403
call GET "/api/guru/laporan?semester=$SEM&tahun_ajaran=$((IDT+1000))" "$TG"; cek "Guru: tahun ajaran bukan aktif" 403
call GET "/api/guru/laporan/excel?semester=$SEM&tahun_ajaran=$((IDT+1000))" "$TG"; cek "Guru Excel: tahun ajaran bukan aktif" 403
call GET "/api/admin/laporan?semester=$SEM&tahun_ajaran=$IDT" "$TG"; cek "Guru tidak boleh rute Admin" 403
call GET "/api/admin/laporan?semester=$SEM&tahun_ajaran=99999999" "$T"; cek "Admin: tahun tidak ada" 404
call GET "/api/admin/laporan?semester=$SEM&tahun_ajaran=$IDT&kelas=99999999" "$T"; cek "Admin: kelas tidak ada" 422

echo "== Laporan: preview JSON"
call GET "/api/guru/laporan?semester=$SEM&tahun_ajaran=$IDT" "$TG"; cek "Guru preview" 200
memuat "metadata tahun ajaran" '"tahun_ajaran"'; memuat "metadata sekolah" '"sekolah"'; memuat "siswa ada" 'Siswa Uji Aktif'
memuat "rekap hadir 1" '"hadir":1'; memuat "rekap izin 1" '"izin":1'; memuat "total 2" '"total":2'
memuat "mapel ada" 'Mapel Laporan Uji'; memuat "UH peta" '"uh":{"1":80,"2":90}'; memuat "ASTS" '"asts":88'
call GET "/api/guru/laporan?semester=$SEM&tahun_ajaran=$IDT&kelas=99999999" "$TG"; cek "Guru: parameter kelas diabaikan" 200
call GET "/api/admin/laporan?semester=$SEM&tahun_ajaran=$IDT&kelas=$IDK" "$T"; cek "Admin preview 1 kelas" 200; memuat "siswa ada" 'Siswa Uji Aktif'
call GET "/api/admin/laporan?semester=$SEM&tahun_ajaran=$IDT" "$T"; cek "Admin preview semua kelas" 200; memuat "Semua kelas" 'Semua kelas'
OTHER=$([ "$SEM" = ganjil ] && echo genap || echo ganjil)
call GET "/api/guru/laporan?semester=$OTHER&tahun_ajaran=$IDT" "$TG"; cek "semester lain" 200; tidak_memuat "tanpa data semester lain" 'Mapel Laporan Uji'

echo "== Laporan: Excel"
F="$(mktemp --suffix=.xlsx)"
unduh "$TG" "/api/guru/laporan/excel?semester=$SEM&tahun_ajaran=$IDT" "$F"
[ "$CODE" = 200 ] && echo "  OK    Guru Excel (HTTP 200)" || { echo "  GAGAL Guru Excel (HTTP $CODE)"; GAGAL=1; }
echo "$TIPE" | grep -qi 'spreadsheetml.sheet' && echo "  OK    Content-Type xlsx" || { echo "  GAGAL Content-Type: $TIPE"; GAGAL=1; }
echo "$NAMA" | grep -q "laporan-$SEM-" && echo "  OK    nama berkas: $NAMA" || { echo "  GAGAL Content-Disposition: $NAMA"; GAGAL=1; }
[ "$(head -c2 "$F")" = "PK" ] && echo "  OK    berkas ZIP/XLSX valid" || { echo "  GAGAL bukan XLSX"; GAGAL=1; }
SH=$(php_baca "$F" sheet)
[ "$SH" = "Informasi|Rekap Absensi|Rekap Nilai" ] && echo "  OK    sheet: $SH" || { echo "  GAGAL sheet: $SH"; GAGAL=1; }
ISI=$(php_baca "$F" isi)
for k in "Siswa Uji Aktif" "Mapel Laporan Uji" "SDN Kedungsari 1" "UH-2" "ASTS"; do
  echo "$ISI" | grep -q "$k" && echo "  OK    sel memuat: $k" || { echo "  GAGAL sel tidak memuat: $k"; GAGAL=1; }
done
echo "$ISI" | grep -q $'\t1\t0\t1\t0\t2' && echo "  OK    hitungan absensi (hadir 1, sakit 0, izin 1, alpa 0, total 2)" || { echo "  GAGAL hitungan absensi di sheet Rekap Absensi"; GAGAL=1; }
echo "$ISI" | grep -qE $'\t0[0-9]{9}\t' && echo "  OK    NISN tetap berawalan nol (teks)" || { echo "  GAGAL NISN kehilangan nol di depan"; GAGAL=1; }

unduh "$TG" "/api/guru/laporan/excel?semester=$SEM&tahun_ajaran=$IDT&jenis=absensi" "$F"
SH=$(php_baca "$F" sheet); [ "$SH" = "Informasi|Rekap Absensi" ] && echo "  OK    jenis=absensi: $SH" || { echo "  GAGAL jenis=absensi: $SH"; GAGAL=1; }
unduh "$TG" "/api/guru/laporan/excel?semester=$SEM&tahun_ajaran=$IDT&jenis=nilai" "$F"
SH=$(php_baca "$F" sheet); [ "$SH" = "Informasi|Rekap Nilai" ] && echo "  OK    jenis=nilai: $SH" || { echo "  GAGAL jenis=nilai: $SH"; GAGAL=1; }
call GET "/api/guru/laporan/excel?semester=$SEM&tahun_ajaran=$IDT&jenis=semua2" "$TG"; cek "jenis salah" 400
unduh "$T" "/api/admin/laporan/excel?semester=$SEM&tahun_ajaran=$IDT&kelas=$IDK" "$F"
[ "$CODE" = 200 ] && echo "  OK    Admin Excel 1 kelas (HTTP 200)" || { echo "  GAGAL Admin Excel (HTTP $CODE)"; GAGAL=1; }
unduh "$T" "/api/admin/laporan/excel?semester=$SEM&tahun_ajaran=$IDT" "$F"
[ "$CODE" = 200 ] && echo "  OK    Admin Excel semua kelas (HTTP 200)" || { echo "  GAGAL Admin Excel semua kelas (HTTP $CODE)"; GAGAL=1; }
unduh "" "/api/admin/laporan/excel?semester=$SEM&tahun_ajaran=$IDT" "$F"; [ "$CODE" = 401 ] && echo "  OK    Excel tanpa token (HTTP 401)" || { echo "  GAGAL Excel tanpa token (HTTP $CODE)"; GAGAL=1; }
rm -f "$F"

echo "== Formula injection (uji tanpa DB)"
php "$DIR/Laporan/export_test.php" && echo "  OK    sel berawalan = + - @ tetap teks" || { echo "  GAGAL formula injection"; GAGAL=1; }

echo "== Bersihkan"
php "$DIR/../../database/seeds/bersihkan_data_uji.php"

echo
[ "$GAGAL" = "0" ] && echo "SEMUA UJI LANGKAH 6b LOLOS." || { echo "ADA UJI YANG GAGAL (lihat di atas)."; exit 1; }
