# SI-KESA Backend (PHP tanpa framework)

## Menjalankan di XAMPP (folder si-kesa ada di htdocs)
1. XAMPP Control Panel: Start **Apache** dan **MySQL**.
2. `composer install` di folder `backend`.
3. Salin `.env.example` menjadi `.env`, isi `JWT_SECRET` (acak, minimal 32 karakter). `DB_*` bawaan XAMPP sudah cocok.
4. Buat database `si_kesa` di phpMyAdmin lalu impor `../database/si_kesa_v9.sql`.
5. Uji: `http://localhost/si-kesa/backend/public/api/public/ping`

## Lapisan (TDD §2)
`controllers → services → repositories → database`; `models` = bentuk data entitas utama;
`middleware` = auth/role/rate limit; `core` = Router, Request, Database, ErrorHandler.

## Keamanan folder
`backend/.htaccess` menolak akses web ke seluruh backend kecuali `public/`.
