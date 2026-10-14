#!/usr/bin/env bash
# Uji otomatis langkah 3b. Prasyarat: php database/seeds/data_uji.php sudah dijalankan.
#   bash tests/smoke_langkah3b.sh
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
memuat() { # memuat LABEL TEKS : isi respons terakhir harus memuat TEKS
  if echo "$BODY" | grep -q "$2"; then echo "  OK    $1"
  else echo "  GAGAL $1 (tidak memuat: $2)"; echo "        $BODY"; GAGAL=1; fi
}
token()  { echo "$BODY" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4; }
idbaru() { echo "$BODY" | grep -o '"id_[a-z_]*":[0-9]*' | head -1 | grep -o '[0-9]*$'; }
nisn()   { printf '%010d' $((RANDOM * RANDOM)); }

call POST /api/auth/login "" '{"username":"admin_uji","password":"admin12345"}'; T=$(token)
[ -z "$T" ] && { echo "Login admin_uji gagal. Jalankan dulu: php database/seeds/data_uji.php"; exit 1; }
call GET /api/admin/tahun-ajaran "$T"
IDAKTIF=$(echo "$BODY" | grep -o '"id_tahun_ajaran":[0-9]*,"nama_tahun":"2026/2027"' | head -1 | grep -o '[0-9]*' | head -1)
[ -z "$IDAKTIF" ] && { echo "Tahun ajaran 2026/2027 (data uji) tidak ditemukan."; exit 1; }

echo "== Guru"
U1="guru_tes$RANDOM"; NIP=$(printf '%018d' $((RANDOM * RANDOM)))
call POST /api/admin/guru "$T" "{\"username\":\"$U1\",\"password\":\"rahasia123\",\"nama_guru\":\"Guru Tes Satu\",\"nip\":\"$NIP\",\"email\":\"tes@contoh.id\"}"; cek "buat guru + akun" 201; IDG=$(idbaru)
call POST /api/auth/login "" "{\"username\":\"$U1\",\"password\":\"rahasia123\"}"; cek "akun guru baru bisa login" 200
call POST /api/admin/guru "$T" "{\"username\":\"$U1\",\"password\":\"rahasia123\",\"nama_guru\":\"Lain\"}"; cek "username kembar" 409
call POST /api/admin/guru "$T" "{\"username\":\"${U1}b\",\"password\":\"rahasia123\",\"nama_guru\":\"Lain\",\"nip\":\"$NIP\"}"; cek "NIP kembar" 409
call POST /api/admin/guru "$T" "{\"username\":\"${U1}c\",\"password\":\"rahasia123\",\"nama_guru\":\"Lain\",\"email\":\"bukan-email\"}"; cek "email tidak valid" 422
call POST /api/admin/guru "$T" "{\"username\":\"${U1}d\",\"password\":\"rahasia123\"}"; cek "nama guru wajib" 422
call POST /api/admin/guru "$T" "{\"username\":\"${U1}e\",\"password\":\"pendek\",\"nama_guru\":\"Lain\"}"; cek "password terlalu pendek" 422
call POST /api/admin/guru "$T" "{\"username\":\"${U1}f\",\"password\":\"rahasia123\",\"nama_guru\":\"Lain\",\"nip\":\"abc12345\"}"; cek "NIP bukan angka" 422
call GET /api/admin/guru/$IDG "$T"; cek "detail guru" 200; memuat "belum jadi wali kelas" '"kelas_aktif":null'
call PUT /api/admin/guru/$IDG "$T" '{"nama_guru":"Guru Tes Diubah","jabatan":"Guru Kelas"}'; cek "ubah parsial" 200; memuat "nama berubah" 'Guru Tes Diubah'; memuat "NIP tidak ikut terhapus" "$NIP"
call GET "/api/admin/guru?q=Guru%20Tes" "$T"; cek "cari guru" 200
call GET "/api/admin/guru?q=Guru%20Uji" "$T"; IDGU=$(idbaru)
call DELETE /api/admin/guru/$IDGU "$T"; cek "hapus guru yang masih jadi wali (guru_uji)" 409

U2="guru_tes$RANDOM"
call POST /api/admin/guru "$T" "{\"username\":\"$U2\",\"password\":\"rahasia123\",\"nama_guru\":\"Guru Tes Dua\"}"; cek "buat guru kedua" 201; IDG2=$(idbaru)

echo "== Kelas"
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":5,\"nama_kelas\":\"5T\",\"id_guru_wali\":$IDG}"; cek "buat kelas tingkat 5 dengan wali" 201; IDK5=$(idbaru)
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":5,\"nama_kelas\":\"5U\"}"; cek "tingkat 5 hanya boleh satu kelas" 422
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":1,\"nama_kelas\":\"1T\",\"id_guru_wali\":$IDG}"; cek "guru sudah jadi wali kelas lain" 409
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":1,\"nama_kelas\":\"1T\",\"id_guru_wali\":$IDG2}"; cek "tingkat 1 boleh dua kelas (kelas kedua)" 201; IDK1=$(idbaru)
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":1,\"nama_kelas\":\"1U\"}"; cek "tingkat 1 maksimal dua kelas" 422
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":7,\"nama_kelas\":\"7A\"}"; cek "tingkat 7 ditolak" 422
call POST /api/admin/kelas "$T" '{"id_tahun_ajaran":99999,"tingkat_kelas":2,"nama_kelas":"2A"}'; cek "tahun ajaran tidak ada" 422
call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDAKTIF,\"tingkat_kelas\":2,\"nama_kelas\":\"2A\",\"id_guru_wali\":99999}"; cek "guru wali tidak ada" 422
call PUT /api/admin/kelas/$IDK5 "$T" '{"nama_kelas":"5V"}'; cek "ubah nama kelas (parsial)" 200; memuat "wali tidak ikut hilang" "\"id_guru_wali\":$IDG"
call PUT /api/admin/kelas/$IDK5 "$T" '{"id_tahun_ajaran":99999}'; cek "tahun ajaran kelas tidak bisa diubah" 422
call PUT /api/admin/kelas/$IDK5 "$T" "{\"id_guru_wali\":$IDG2}"; cek "ganti wali ke guru yang sudah jadi wali" 409
call GET "/api/admin/kelas?id_tahun_ajaran=$IDAKTIF" "$T"; cek "daftar kelas per tahun ajaran" 200
call GET "/api/admin/kelas?id_tahun_ajaran=abc" "$T"; cek "filter bukan angka" 400
call DELETE /api/admin/guru/$IDG "$T"; cek "hapus guru yang jadi wali kelas 5V" 409
call POST /api/admin/hari-libur "$T" "{\"tanggal_libur\":\"2026-12-27\",\"keterangan\":\"Libur kelas uji\",\"id_kelas\":[$IDK5]}"; cek "buat libur khusus kelas" 201; IDL=$(idbaru)
call DELETE /api/admin/kelas/$IDK5 "$T"; cek "hapus kelas yang jadi target libur" 409
call DELETE /api/admin/hari-libur/$IDL "$T"; cek "hapus hari libur" 200

echo "== Siswa"
N1=$(nisn); N2=$(nisn)
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Siswa Tes Satu\",\"nisn_resmi\":\"$N1\",\"nama_wali\":\"Wali Tes\",\"no_hp_wali\":\"081234567890\"}"; cek "buat siswa" 201; IDS=$(idbaru); memuat "status awal baru" '"status_siswa":"baru"'
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Lain\",\"nisn_resmi\":\"$N1\"}"; cek "NISN kembar" 409
call POST /api/admin/siswa "$T" '{"nama_siswa":"Lain","nisn_resmi":"123"}'; cek "NISN bukan 10 digit" 422
call POST /api/admin/siswa "$T" '{"nama_siswa":"Lain","nisn_resmi":"12345abcde"}'; cek "NISN mengandung huruf" 422
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Lain\",\"nisn_resmi\":\"$(nisn)\",\"status_siswa\":\"aktif\"}"; cek "status awal tidak boleh aktif" 422
call POST /api/admin/siswa "$T" "{\"nisn_resmi\":\"$(nisn)\"}"; cek "nama wajib" 422
call POST /api/admin/siswa "$T" "{\"nama_siswa\":\"Siswa Tes Dua\",\"nisn_resmi\":\"$N2\"}"; cek "buat siswa kedua" 201; IDS2=$(idbaru)
call PUT /api/admin/siswa/$IDS "$T" '{"nama_siswa":"Siswa Tes Satu Diubah"}'; cek "ubah parsial" 200; memuat "NISN tidak ikut berubah" "$N1"
call PUT /api/admin/siswa/$IDS "$T" '{"status_siswa":"aktif"}'; cek "status tidak boleh diubah manual ke aktif" 422
call PUT /api/admin/siswa/$IDS "$T" '{"status_siswa":"lulus"}'; cek "status tidak boleh diubah manual ke lulus" 422
call GET "/api/admin/siswa?q=Siswa%20Tes&status=baru" "$T"; cek "cari siswa + filter status" 200
call GET "/api/admin/siswa?status=aneh" "$T"; cek "filter status tidak dikenal" 400
call GET "/api/admin/siswa?q=0000000003" "$T"; IDSLULUS=$(idbaru)
call GET "/api/admin/siswa?q=0000000001" "$T"; IDSAKTIF=$(idbaru)

echo "== Penempatan siswa"
call POST /api/wali/login "" "{\"nisn\":\"$N1\"}"; cek "siswa baru belum bisa login Wali" 401
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS,\"id_kelas\":$IDK1,\"nomor_absen\":7}"; cek "tempatkan siswa baru ke kelas aktif" 201; IDR=$(idbaru)
call GET /api/admin/siswa/$IDS "$T"; memuat "status otomatis menjadi aktif" '"status_siswa":"aktif"'; memuat "riwayat tercatat" '"nomor_absen":7'
call POST /api/wali/login "" "{\"nisn\":\"$N1\"}"; cek "setelah ditempatkan, Wali bisa login" 200
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS,\"id_kelas\":$IDK1}"; cek "siswa sudah ditempatkan tahun ini" 409
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS2,\"id_kelas\":$IDK1,\"nomor_absen\":7}"; cek "nomor absen kembar dalam kelas" 409
call GET /api/admin/siswa/$IDS2 "$T"; memuat "penempatan gagal tidak mengubah status siswa kedua" '"status_siswa":"baru"'
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS2,\"id_kelas\":$IDK1,\"nomor_absen\":0}"; cek "nomor absen 0" 422
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS2,\"id_kelas\":$IDK1,\"nomor_absen\":300}"; cek "nomor absen 300" 422
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDSLULUS,\"id_kelas\":$IDK1}"; cek "siswa lulus tidak bisa ditempatkan" 422
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS2,\"id_kelas\":99999}"; cek "kelas tidak ada" 422
call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":99999,\"id_kelas\":$IDK1}"; cek "siswa tidak ada" 422
call PUT /api/admin/riwayat-kelas/$IDR "$T" '{"nomor_absen":8}'; cek "ubah nomor absen" 200; memuat "nomor berubah" '"nomor_absen":8'
call PUT /api/admin/riwayat-kelas/$IDR "$T" "{\"id_kelas\":$IDK5}"; cek "pindah kelas lewat PUT ditolak" 422
call GET "/api/admin/riwayat-kelas?id_kelas=$IDK1" "$T"; cek "daftar penempatan per kelas" 200

# kelas pada tahun ajaran yang belum aktif
Y=$((3000 + RANDOM % 5000)); NAMATB="$Y/$((Y+1))"
call POST /api/admin/tahun-ajaran "$T" "{\"nama_tahun\":\"$NAMATB\"}"; cek "buat tahun ajaran uji ($NAMATB, belum aktif)" 201; IDTB=$(idbaru)
IDKB=""
if [ -n "$IDTB" ]; then
  call POST /api/admin/kelas "$T" "{\"id_tahun_ajaran\":$IDTB,\"tingkat_kelas\":2,\"nama_kelas\":\"2X\"}"; cek "buat kelas di tahun ajaran belum aktif" 201; IDKB=$(idbaru)
  if [ -n "$IDKB" ]; then
    call POST /api/admin/riwayat-kelas "$T" "{\"id_siswa\":$IDS2,\"id_kelas\":$IDKB}"; cek "penempatan langsung ke tahun belum aktif ditolak" 422
  fi
else
  echo "  (lewati 2 uji tahun belum aktif: tahun ajaran uji tidak berhasil dibuat; lihat GAGAL di atas)"
fi

call DELETE /api/admin/siswa/$IDS "$T"; cek "hapus siswa yang sudah punya penempatan" 409
call DELETE /api/admin/siswa/$IDSAKTIF "$T"; cek "hapus siswa data uji (punya penempatan)" 409
call DELETE /api/admin/riwayat-kelas/$IDR "$T"; cek "koreksi: hapus penempatan tanpa data terkait" 200
call GET /api/admin/siswa/$IDS "$T"; memuat "status kembali menjadi baru" '"status_siswa":"baru"'
call POST /api/wali/login "" "{\"nisn\":\"$N1\"}"; cek "setelah penempatan dihapus, Wali ditolak lagi" 401

echo "== Status pindah"
call PUT /api/admin/siswa/$IDS2 "$T" '{"status_siswa":"pindah"}'; cek "tandai siswa baru sebagai pindah" 200
call PUT /api/admin/siswa/$IDS2 "$T" '{"status_siswa":"baru"}'; cek "pindah tidak bisa dikembalikan manual" 422

echo "== Pembersihan"
call DELETE /api/admin/siswa/$IDS "$T";   cek "hapus siswa uji 1" 200
call DELETE /api/admin/siswa/$IDS2 "$T";  cek "hapus siswa uji 2" 200
[ -n "$IDKB" ] && { call DELETE /api/admin/kelas/$IDKB "$T";  cek "hapus kelas tahun belum aktif" 200; }
[ -n "$IDTB" ] && { call DELETE /api/admin/tahun-ajaran/$IDTB "$T"; cek "hapus tahun ajaran uji" 200; }
call DELETE /api/admin/kelas/$IDK1 "$T";  cek "hapus kelas 1T" 200
call DELETE /api/admin/kelas/$IDK5 "$T";  cek "hapus kelas 5V (target libur sudah dihapus)" 200
call DELETE /api/admin/guru/$IDG "$T";    cek "hapus guru 1 (sudah bukan wali)" 200
call POST /api/auth/login "" "{\"username\":\"$U1\",\"password\":\"rahasia123\"}"; cek "akun guru ikut terhapus" 401
call DELETE /api/admin/guru/$IDG2 "$T";   cek "hapus guru 2" 200

echo
[ $GAGAL -eq 0 ] && echo "SEMUA UJI LOLOS" || echo "ADA UJI YANG GAGAL (lihat tanda GAGAL di atas)"
