# Website Publik SI-KESA

## Menjalankan
    SIKESA_API_URL=http://localhost/si-kesa/backend/public php -S 127.0.0.1:8081 -t public
(SIKESA_API_URL = alamat folder `backend/public`; tanpa env, nilai bawaan di `lib/api.php`. `SIKESA_DEBUG=1` menampilkan error PHP.)
Backend + MySQL (`database/si_kesa_v9.sql`) harus berjalan; website hanya memanggil `GET /api/public/*`.

## Struktur layout (satu pintu)
- `public/index.php` — router (`?page=<folder di views/>`), memuat `lib/bootstrap.php`.
- `lib/bootstrap.php` — `base_url()`, `asset()`, `esc()`, `page_url()`, `tanggal_id()`, `layout_start()/layout_end()`.
- `views/layouts/main.php` — SATU-SATUNYA tempat `<head>`, navbar, footer, dan skrip dipasang.
- `views/partials/site-nav.php`, `site-footer.php`, `site-info.php` (kontak statis, dipakai footer + halaman Hubungi Kami).
- `public/assets/css/style.css` + `js/main.js` (bersama); `css/pages/<halaman>.css` (khusus halaman).

Halaman baru cukup:
    <?php require_once __DIR__.'/../../lib/bootstrap.php';
    layout_start(['title'=>'...','current'=>'home','css'=>'nama']); ?>
    <main>...</main>
    <?php layout_end(); ?>

## Sumber data (database via API)
Beranda, Profil Guru, Berita (+detail), Program Pembiasaan, Ekstrakurikuler, Galeri, Mading, video profil, dan formulir PPDB.
PPDB: alamat Google Form dari `pengaturan_sistem.ppdb_form_url`; jawaban tetap di Google (Dokumen 00 §15.1).
Hubungi Kami: kontak statis di `site-info.php` (sesuai dokumen).
`views/ppdb/_arsip_formulir_lokal.php.txt` = formulir lokal lama (menyimpan CSV), diarsipkan dan tidak dirutekan.
