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

## Langkah 6a: Wali, publik, konten website
- Data awal: `php database/seeds/data_awal.php` (enam program pembiasaan; aman diulang).
- Unggah gambar: `POST /api/admin/upload` (multipart, field `file`; JPG/PNG/WEBP, maks 2 MB) -> `{"path":"uploads/<acak>.png"}`.
  Path itu dipakai pada kolom gambar (banner, ikon, file_gambar). URL gambar = `<alamat API>/<path>`.
  File tersimpan di `public/uploads/` (diabaikan git). Menghapus konten TIDAK menghapus berkasnya (lihat catatan pembersihan).
- Uji: `bash tests/smoke_langkah6a.sh`
