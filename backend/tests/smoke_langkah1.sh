#!/usr/bin/env bash
# Uji cepat fondasi (XAMPP). Pakai:  bash tests/smoke_langkah1.sh http://localhost/si-kesa/backend/public
BASE="${1:-http://localhost/si-kesa/backend/public}"
t() { echo; echo "### $1"; shift; curl -s -i "$@" | sed -n '1p;/^Allow:/p;/^Access-Control/p;/^{/p'; }
t "1. ping (harus 200 + success:true)"            "$BASE/api/public/ping"
t "2. database (harus 200 bila .env benar)"       "$BASE/api/public/ping-db"
t "3. rute tak ada (harus 404 JSON)"              "$BASE/api/tidak-ada"
t "4. metode salah (harus 405 + header Allow)"    -X DELETE "$BASE/api/public/ping"
t "5. preflight CORS (harus 204 + header Access-Control)" -X OPTIONS -H "Origin: http://localhost:5500" "$BASE/api/public/ping"
