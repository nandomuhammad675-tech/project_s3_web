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

## Catatan perbaikan navigasi
Penyebab navigasi kacau: 6 halaman (Beranda, Profil/guru, Berita, Ekstra, Galeri, Hubungi Kami) membawa `<html>`, navbar, footer, dan skrip sendiri DI DALAM `layout_start()` sehingga navbar/footer dobel, menu di tiap halaman beda (Program & Mading hilang), tiga halaman berisi sisa konflik merge (`<<<<<<< HEAD`), dan tautan memakai `base_url('profil#...')`/`guru` yang tidak ada rutenya.
Perbaikan: halaman hanya berisi `<main>`; navbar/footer satu sumber (`partials/site-nav.php`, `site-footer.php`); `page_map()` + `page_alias()` di `lib/bootstrap.php` (`?page=profil|berita|program|ekstra` dialihkan 301); `page_url(kunci, [param], 'anchor')`; halaman 404/500 berisi; menu mobile tertutup saat tautan diklik/ESC/klik luar. Urutan section dan CSS tidak diubah.

## Catatan penggabungan (website_fixed + desain website_baru)
- Dasar: website_fixed. Dari website_baru dipakai: navbar (`partials/site-nav.php` + gaya di `style.css`), halaman Kegiatan & Prestasi (daftar berfilter kategori + detail dengan lightbox), bagian "Kegiatan & Prestasi Terbaru" di Beranda, halaman Hubungi Kami.
- Ekstrakurikuler (daftar + detail) dan Mading memakai kartu yang sama dengan Kegiatan & Prestasi (`.kartu--post` di `style.css`, `css/pages/detail.css`); datanya tetap seperti website_fixed.
- Kegiatan & Prestasi memakai data event dari API tanpa data contoh: API mati => "gagal dimuat" + Coba Lagi. `views/kegiatan/data.php` (berita contoh) dihapus.
- Media sosial (Instagram, Facebook) selalu tampil di footer dan Hubungi Kami. Isi tautan asli di `views/partials/site-info.php` (sekarang masih `#`).
- `jam` di `site-info.php` (Senin - Sabtu, 07.00 - 13.00 WIB) berasal dari website_baru; sesuaikan bila tidak tepat.
- CSS lama yang tidak terpakai dihapus: kegiatan, kegiatan-detail, ekstrakurikuler, ekstrakurikuler-detail, mading.

## Pembaruan berikutnya
- Profil: NIP tidak ditampilkan; guru yang mengajar ekstra menampilkan "Guru Ekstra <nama>" (butuh field `ekskul` pada `/api/public/guru`, lihat patch backend).
- Mading: saringan kategori (Semua + kategori dari database) lewat `?kategori=` dan `kategori_tersedia` dari `/api/public/mading`.
- Galeri, Ekstrakurikuler, Mading memakai kartu yang sama dan diratakan ke tengah (`.grid--center`). Foto Galeri bisa diperbesar.
- Hubungi Kami: tanpa tombol telepon; Instagram dan Facebook memakai logo.
- Carousel Beranda: transisi berjalan di semua lebar layar.
- Hero halaman dalam (Ekstra, Galeri, Mading, Hubungi Kami, Berita) kini seukuran hero Program/Profil: layar penuh, foto home.jpg.
