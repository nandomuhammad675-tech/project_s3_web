#!/usr/bin/env bash
# Uji otomatis langkah 3a. Prasyarat: php database/seeds/data_uji.php sudah dijalankan.
#   bash tests/smoke_langkah3a.sh
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
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9]*' | head -1 | grep -o '[0-9]*$'; }

call POST /api/auth/login "" '{"username":"admin_uji","password":"admin12345"}'; T=$(token)
call POST /api/auth/login "" '{"username":"guru_uji","password":"guru12345"}';   TG=$(token)
[ -z "$T" ] && { echo "Login admin_uji gagal. Jalankan dulu: php database/seeds/data_uji.php"; exit 1; }

echo "== Akses"
call GET /api/admin/akun "";    cek "tanpa token" 401
call GET /api/admin/akun "$TG"; cek "token Guru ditolak (403)" 403
call GET /api/admin/akun "$T";  cek "daftar akun oleh Admin" 200

echo "== Akun"
U="admin_tes$RANDOM"
call POST /api/admin/akun "$T" "{\"username\":\"$U\",\"password\":\"rahasia123\"}"; cek "buat akun Admin" 201; ID=$(idbaru)
call POST /api/admin/akun "$T" "{\"username\":\"$U\",\"password\":\"rahasia123\"}"; cek "username kembar" 409
call POST /api/admin/akun "$T" '{"username":"x","password":"rahasia123"}';           cek "username terlalu pendek" 422
call POST /api/admin/akun "$T" "{\"username\":\"${U}b\",\"password\":\"pendek\"}";   cek "password terlalu pendek" 422
call POST /api/admin/akun "$T" "{\"username\":\"${U}c\",\"password\":\"rahasia123\",\"role\":\"guru\"}"; cek "role guru ditolak" 422
call PUT /api/admin/akun/$ID/status "$T" '{"status_akun":"nonaktif"}';   cek "nonaktifkan akun lain" 200
call POST /api/auth/login "" "{\"username\":\"$U\",\"password\":\"rahasia123\"}"; cek "akun nonaktif tidak bisa login" 401
call PUT /api/admin/akun/$ID/status "$T" '{"status_akun":"aktif"}';      cek "aktifkan kembali" 200
call POST /api/auth/login "" "{\"username\":\"$U\",\"password\":\"rahasia123\"}"; cek "akun aktif bisa login" 200; TNEW=$(token)
call PUT /api/admin/akun/$ID/status "$T" '{"status_akun":"lain"}';       cek "status tidak dikenal" 422
call PUT /api/admin/akun/99999/status "$T" '{"status_akun":"aktif"}';    cek "akun tidak ada" 404
ME=$(echo "$BODY" >/dev/null; curl -s -H "Authorization: Bearer $T" "$BASE/api/auth/me" | grep -o '"id_user":[0-9]*' | grep -o '[0-9]*')
call PUT /api/admin/akun/$ME/status "$T" '{"status_akun":"nonaktif"}';   cek "nonaktifkan akun sendiri ditolak" 422
call PUT /api/admin/akun/$ME/password "$T" '{"password_baru":"apasaja123"}'; cek "reset password akun sendiri ditolak" 422
sleep 2
call PUT /api/admin/akun/$ID/password "$T" '{"password_baru":"baruBaru123"}'; cek "reset password akun lain" 200
call GET /api/auth/me "$TNEW"; cek "token lama akun itu ditolak setelah reset" 401
call POST /api/auth/login "" "{\"username\":\"$U\",\"password\":\"baruBaru123\"}"; cek "login dengan password hasil reset" 200
call PUT /api/admin/akun/$ID/status "$T" '{"status_akun":"nonaktif"}';   cek "bersihkan: nonaktifkan akun uji" 200

echo "== Tahun ajaran"
call GET /api/admin/tahun-ajaran "$T"; cek "daftar tahun ajaran" 200
IDAKTIF=$(echo "$BODY" | grep -o '"id_tahun_ajaran":[0-9]*,"nama_tahun":"2026/2027"' | head -1 | grep -o '[0-9]*' | head -1)
Y=$((3000 + RANDOM % 5000)); NAMA="$Y/$((Y+1))"
call POST /api/admin/tahun-ajaran "$T" "{\"nama_tahun\":\"$NAMA\"}"; cek "buat tahun ajaran" 201; IDT=$(idbaru)
call POST /api/admin/tahun-ajaran "$T" "{\"nama_tahun\":\"$NAMA\"}"; cek "nama kembar" 409
call POST /api/admin/tahun-ajaran "$T" '{"nama_tahun":"2027-2028"}'; cek "format nama salah" 422
call POST /api/admin/tahun-ajaran "$T" '{"nama_tahun":"2027/2030"}'; cek "tahun tidak berurutan" 422
call GET /api/admin/tahun-ajaran/$IDT "$T"; cek "detail" 200
call PUT /api/admin/tahun-ajaran/$IDT "$T" '{"semester_aktif":"genap","konfirmasi_semester":true}'; cek "ganti semester di tahun TIDAK aktif ditolak" 422
call PUT /api/admin/tahun-ajaran/$IDT/aktifkan "$T"; cek "aktivasi tanpa kenaikan kelas ditolak" 422
if [ -n "$IDAKTIF" ]; then
  call PUT /api/admin/tahun-ajaran/$IDAKTIF "$T" '{"semester_aktif":"genap"}'; cek "ganti semester tanpa konfirmasi ditolak" 422
  call PUT /api/admin/tahun-ajaran/$IDAKTIF/aktifkan "$T"; cek "aktifkan tahun yang sudah aktif ditolak" 422
  call DELETE /api/admin/tahun-ajaran/$IDAKTIF "$T"; cek "hapus tahun ajaran aktif ditolak" 409
else echo "  (lewati tes tahun ajaran aktif: 2026/2027 tidak ditemukan)"; fi
call DELETE /api/admin/tahun-ajaran/$IDT "$T"; cek "hapus tahun ajaran tanpa data" 200
call GET /api/admin/tahun-ajaran/$IDT "$T"; cek "sudah terhapus" 404

echo "== Mata pelajaran"
K="T$RANDOM"
call POST /api/admin/mapel "$T" "{\"kode_mapel\":\"$K\",\"nama_mapel\":\"Mapel Uji\"}"; cek "buat mapel" 201; IDM=$(idbaru)
call POST /api/admin/mapel "$T" "{\"kode_mapel\":\"$K\",\"nama_mapel\":\"Lain\"}"; cek "kode kembar" 409
call POST /api/admin/mapel "$T" '{"kode_mapel":"X"}'; cek "nama wajib" 422
call PUT /api/admin/mapel/$IDM "$T" "{\"kode_mapel\":\"$K\",\"nama_mapel\":\"Mapel Uji 2\"}"; cek "ubah mapel" 200
call GET /api/admin/mapel "$T"; cek "daftar mapel" 200
call DELETE /api/admin/mapel/$IDM "$T"; cek "hapus mapel" 200
call GET /api/admin/mapel/$IDM "$T"; cek "sudah terhapus" 404

echo "== Hari libur"
call POST /api/admin/hari-libur "$T" '{"tanggal_libur":"2026-12-25","keterangan":"Libur uji"}'; cek "buat hari libur (semua kelas)" 201; IDL=$(idbaru)
call POST /api/admin/hari-libur "$T" '{"tanggal_libur":"2026-13-40"}'; cek "tanggal tidak valid" 422
call POST /api/admin/hari-libur "$T" '{"tanggal_libur":"2026-12-26","id_kelas":[99999]}'; cek "kelas target tidak ada" 422
call GET /api/admin/hari-libur/$IDL "$T"; cek "detail" 200
call GET "/api/admin/hari-libur?dari=2026-12-01&sampai=2026-12-31" "$T"; cek "filter rentang tanggal" 200
call GET "/api/admin/hari-libur?dari=salah" "$T"; cek "filter tanggal salah" 400
call PUT /api/admin/hari-libur/$IDL "$T" '{"tanggal_libur":"2026-12-25","keterangan":"Libur uji diubah"}'; cek "ubah hari libur" 200
call DELETE /api/admin/hari-libur/$IDL "$T"; cek "hapus hari libur" 200
call GET /api/admin/hari-libur/$IDL "$T"; cek "sudah terhapus" 404

echo
[ $GAGAL -eq 0 ] && echo "SEMUA UJI LOLOS" || echo "ADA UJI YANG GAGAL (lihat tanda GAGAL di atas)"
