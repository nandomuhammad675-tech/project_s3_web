#!/usr/bin/env bash
# Uji otomatis autentikasi. Jalankan SETELAH: php database/seeds/data_uji.php
#   bash tests/smoke_langkah2.sh                 # uji biasa
#   bash tests/smoke_langkah2.sh --ratelimit     # termasuk uji pembatasan percobaan (mengunci NISN uji 5 menit)
BASE="${BASE:-http://localhost/si-kesa/backend/public}"
GAGAL=0

call() { # call METODE PATH [TOKEN] [BODY]  -> mengisi CODE dan BODY
  local args=(-s -w $'\n%{http_code}' -X "$1" "$BASE$2" -H 'Content-Type: application/json')
  [ -n "$3" ] && args+=(-H "Authorization: Bearer $3")
  [ -n "$4" ] && args+=(-d "$4")
  local out; out=$(curl "${args[@]}")
  CODE=${out##*$'\n'}; BODY=${out%$'\n'*}
}
cek() { # cek LABEL HARAPAN
  if [ "$CODE" = "$2" ]; then echo "  OK    $1 (HTTP $CODE)"
  else echo "  GAGAL $1 (harapan $2, dapat $CODE)"; echo "        $BODY"; GAGAL=1; fi
}
token() { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }

echo "== Admin / Guru"
call POST /api/auth/login "" '{"username":"admin_uji","password":"admin12345"}'; cek "login admin benar" 200; T_ADMIN=$(token)
call POST /api/auth/login "" '{"username":"admin_uji","password":"salah"}';      cek "login password salah" 401
call POST /api/auth/login "" '{"username":"admin_uji"}';                         cek "login tanpa password" 400
call POST /api/auth/login "" '{"username":"guru_uji","password":"guru12345"}';   cek "login guru benar" 200; T_GURU=$(token)
call GET  /api/auth/me "";                                                       cek "me tanpa token" 401
call GET  /api/auth/me "xxx.yyy.zzz";                                            cek "me token rusak" 401
call GET  /api/auth/me "$T_ADMIN";                                               cek "me dengan token admin" 200
call GET  /api/auth/me "$T_GURU";                                                cek "me dengan token guru" 200

echo "== Wali Murid"
call POST /api/wali/login "" '{"nisn":"0000000001"}'; cek "wali aktif login" 200; T_WALI=$(token)
call POST /api/wali/login "" '{"nisn":"0000000002"}'; cek "wali siswa BARU ditolak" 401; M1="$BODY"
call POST /api/wali/login "" '{"nisn":"0000000003"}'; cek "wali siswa LULUS ditolak" 401; M2="$BODY"
call POST /api/wali/login "" '{"nisn":"0000000099"}'; cek "wali NISN tidak ada ditolak" 401; M3="$BODY"
call POST /api/wali/login "" '{"nisn":"abc"}';        cek "wali NISN format salah ditolak" 401; M4="$BODY"
if [ "$M1" = "$M2" ] && [ "$M2" = "$M3" ] && [ "$M3" = "$M4" ]; then echo "  OK    pesan gagal Wali sama untuk semua penyebab"
else echo "  GAGAL pesan gagal Wali berbeda-beda (membocorkan NISN)"; GAGAL=1; fi
call POST /api/wali/login "" '{}';                    cek "wali tanpa nisn" 400
call GET  /api/wali/siswa "$T_WALI";                  cek "profil siswa dengan token wali" 200
call GET  /api/wali/siswa "";                         cek "profil siswa tanpa token" 401
call GET  /api/wali/siswa "$T_ADMIN";                 cek "token internal ditolak di endpoint Wali" 401
call GET  /api/auth/me "$T_WALI";                     cek "token Wali ditolak di endpoint internal" 401

echo "== Ganti password (jeda 2 detik agar token lama pasti terbit sebelum perubahan)"
sleep 2
call PUT /api/auth/password "$T_ADMIN" '{"password_lama":"salah","password_baru":"adminBaru123"}';    cek "password lama salah" 422
call PUT /api/auth/password "$T_ADMIN" '{"password_lama":"admin12345","password_baru":"abc"}';        cek "password baru terlalu pendek" 422
call PUT /api/auth/password "$T_ADMIN" '{"password_lama":"admin12345","password_baru":"adminBaru123"}'; cek "ganti password benar" 200; T_NEW=$(token)
call GET /api/auth/me "$T_ADMIN"; cek "token LAMA ditolak setelah ganti password" 401
call GET /api/auth/me "$T_NEW";   cek "token BARU diterima" 200
call POST /api/auth/login "" '{"username":"admin_uji","password":"admin12345"}'; cek "password lama tidak bisa login lagi" 401
call PUT /api/auth/password "$T_NEW" '{"password_lama":"adminBaru123","password_baru":"admin12345"}'; cek "kembalikan password awal" 200

if [ "$1" = "--ratelimit" ]; then
  echo "== Rate limit (NISN 0000000077 akan terkunci 5 menit)"
  for i in 1 2 3 4 5; do call POST /api/wali/login "" '{"nisn":"0000000077"}'; cek "percobaan gagal ke-$i" 401; done
  call POST /api/wali/login "" '{"nisn":"0000000077"}'; cek "percobaan ke-6 diblokir" 429
fi

echo
[ $GAGAL -eq 0 ] && echo "SEMUA UJI LOLOS" || echo "ADA UJI YANG GAGAL (lihat tanda GAGAL di atas)"
