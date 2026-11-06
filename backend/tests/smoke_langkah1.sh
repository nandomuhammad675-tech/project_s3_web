#!/usr/bin/env bash
# Uji cepat fondasi (XAMPP). Pakai:  bash tests/smoke_langkah1.sh http://localhost/si-kesa/backend/public
BASE="${1:-http://localhost/si-kesa/backend/public}"
t() { echo; echo "### $1"; shift; curl -s -i "$@" | sed -n '1p;/^Allow:/p;/^Access-Control/p;/^{/p'; }
t "1. pengaturan publik (harus 200 + success:true; ikut membuktikan koneksi database bila .env benar)" "$BASE/api/public/pengaturan"
t "2. rute tak ada (harus 404 JSON)"              "$BASE/api/tidak-ada"
t "3. metode salah (harus 405 + header Allow)"    -X DELETE "$BASE/api/public/pengaturan"
t "4. preflight CORS (harus 204 + header Access-Control)" -X OPTIONS -H "Origin: http://localhost:5500" "$BASE/api/public/pengaturan"
