<?php
/**
 * lib/bootstrap.php - fungsi bantu BERSAMA untuk semua halaman website publik.
 * Dipanggil satu kali oleh public/index.php (router) sebelum halaman apa pun dimuat.
 * Memuat: base_url(), asset(), esc(), page_url(), tanggal_id(), layout_start()/layout_end(), site_info(), ui_hero()/ui_hero_slides()/ui_gagal()/ui_kosong()/ui_pager().
 */
if (defined('SIKESA_BOOTSTRAP_LOADED')) { return; }
define('SIKESA_BOOTSTRAP_LOADED', true);

date_default_timezone_set('Asia/Jakarta');

/** URL dasar folder public/ (tempat folder assets berada), dihitung dari document root. */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        $base = '';
        $root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ''), '/');
        $pub  = realpath(__DIR__ . '/../public');
        if ($pub) {
            $pub = str_replace('\\', '/', $pub);
            if ($root !== '' && stripos($pub, $root) === 0) {
                $base = substr($pub, strlen($root)) . '/';
            }
        }
        if ($base === '') {
            $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/';
        }
    }
    return $base . ltrim($path, '/');
}

/** URL file di public/assets (+ ?v=waktu-ubah supaya cache browser ikut diperbarui). */
function asset(string $path): string
{
    $file = __DIR__ . '/../public/assets/' . ltrim($path, '/');
    return base_url('assets/' . ltrim($path, '/')) . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function esc($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/**
 * SATU-SATUNYA peta halaman. kunci menu => nilai ?page= (= nama folder di views/).
 * Navbar, footer, dan semua tautan memakai page_url(kunci); jangan menulis alamat halaman di tempat lain.
 */
function page_map(): array
{
    return [
        'home'    => '',
        'profil'  => 'guru',
        'berita'  => 'kegiatan',
        'program' => 'program-pembiasaan',
        'ekstra'  => 'ekstrakurikuler',
        'galeri'  => 'galeri',
        'mading'  => 'mading',
        'kontak'  => 'kontak',
        'ppdb'    => 'ppdb',
    ];
}

/** Alamat yang mungkin diketik/ditautkan orang -> nama folder sebenarnya (dialihkan 301 oleh router). */
function page_alias(): array
{
    return ['profil' => 'guru', 'berita' => 'kegiatan', 'program' => 'program-pembiasaan', 'ekstra' => 'ekstrakurikuler'];
}

/** page_url('berita') -> .../?page=kegiatan ; page_url('berita', ['hal' => 2], 'berita') -> .../?page=kegiatan&hal=2#berita */
function page_url(string $key, array $params = [], string $anchor = ''): string
{
    $page = page_map()[$key] ?? '';
    $q = ($page !== '' ? ['page' => $page] : []) + $params;
    return base_url('') . ($q ? '?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986) : '') . ($anchor !== '' ? '#' . $anchor : '');
}

/** 2026-10-05 -> "5 Oktober 2026" (format tampilan Indonesia). */
function tanggal_id(?string $ymd): string
{
    static $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = $ymd ? strtotime($ymd) : false;
    return $t ? ((int) date('j', $t)) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '';
}

/**
 * ===== LAYOUT SATU PINTU =====
 * Setiap halaman cukup:
 *     layout_start(['title' => '...', 'current' => 'home', 'css' => 'home', 'js' => 'home']);
 *     ... isi halaman (<main>...</main>) ...
 *     layout_end();
 * Header (<head>), navbar, footer, dan skrip menu hanya ditulis SEKALI di views/layouts/main.php.
 *
 * Opsi: title, current (menu aktif), css / js (nama berkas, atau daftar nama, di assets/css/pages & assets/js), description.
 */
function layout_start(array $opt = []): void
{
    $GLOBALS['__layout'] = $opt;
    ob_start();
}

function layout_end(): void
{
    $content = (string) ob_get_clean();
    $opt     = $GLOBALS['__layout'] ?? [];
    require __DIR__ . '/../views/layouts/main.php';
}


/** Data kontak statis sekolah (satu sumber: footer + halaman Hubungi Kami). */
function site_info(): array
{
    static $info = null;
    return $info ??= require __DIR__ . '/../views/partials/site-info.php';
}

/** Kotak keadaan "gagal memuat" (ada tombol Coba Lagi). */
function ui_gagal(string $apa, string $retryUrl): void
{
    echo '<div class="status status--gagal ui-gagal" role="alert"><p>' . esc($apa) . ' gagal dimuat. Periksa koneksi internet lalu coba lagi.</p>'
       . '<a class="pill pill--solid" href="' . esc($retryUrl) . '">Coba Lagi</a></div>';
}

/** Kotak keadaan "belum ada data". */
function ui_kosong(string $pesan): void
{
    echo '<p class="status">' . esc($pesan) . '</p>';
}

/** Navigasi halaman (Sebelumnya / Halaman x dari y / Selanjutnya). $url(int $hal): string */
function ui_pager(int $hal, int $total, callable $url, string $label, string $anchor): void
{
    if ($total <= 1) { return; }
    $prev = max(1, $hal - 1);
    $next = min($total, $hal + 1);
    echo '<nav class="pager" aria-label="' . esc($label) . '"><div class="pager__nav">'
       . '<a class="pill' . ($hal <= 1 ? ' is-disabled' : '') . '" href="' . esc($url($prev)) . '#' . esc($anchor) . '"' . ($hal <= 1 ? ' aria-disabled="true" tabindex="-1"' : '') . ' rel="prev">Sebelumnya</a>'
       . '<span class="pager__info">Halaman ' . $hal . ' dari ' . $total . '</span>'
       . '<a class="pill' . ($hal >= $total ? ' is-disabled' : '') . '" href="' . esc($url($next)) . '#' . esc($anchor) . '"' . ($hal >= $total ? ' aria-disabled="true" tabindex="-1"' : '') . ' rel="next">Selanjutnya</a>'
       . '</div></nav>';
}

/** Foto latar hero bawaan (jalur relatif terhadap public/assets/). Ubah daftar ini untuk mengganti foto hero di SEMUA halaman. */
function hero_foto_bawaan(): array
{
    return ['gambar/0.jpg', 'gambar/17.jpg'];
}

/**
 * Hero halaman dalam (Profil, Berita, Program, Ekstra, Galeri, Mading, Hubungi Kami):
 * judul besar di tengah + subjudul, foto latar berganti otomatis, teks mengetik berulang.
 * $sub boleh berisi <br> untuk memisah baris. $bg (opsional) = alamat satu gambar latar tetap (tanpa slideshow).
 */
function ui_hero(string $judul, string $sub = '', ?string $bg = null): void
{
    if ($bg) {
        echo '<section class="hero page-hero" style="--bg:url(\'' . esc($bg) . '\')"><div class="page-hero__content"><h1>' . esc($judul) . '</h1>'
           . ($sub !== '' ? '<p>' . $sub . '</p>' : '') . '</div></section>';
        return;
    }
    $baris = $sub !== '' ? preg_split('/<br\s*\/?>/i', $sub) : [];
    ui_hero_slides('', [$judul], $baris, [], null, true);
}

/**
 * Hero berlatar foto yang berganti otomatis + teks mengetik berulang.
 * $kecil: teks kecil di atas judul. $judul: satu atau beberapa baris judul (h1).
 * $tag: kalimat di bawah judul (teks, atau daftar baris). $tombol: ['Teks tombol', 'alamat'] (boleh kosong).
 * $foto: daftar file di public/assets/ (null = hero_foto_bawaan(); yang tidak ada dilewati; kosong -> img/home.jpg).
 * $tengah: true = teks rata tengah dan lebih besar (halaman dalam); false = rata kiri (Beranda).
 * Gaya ada di style.css, animasinya di main.js.
 */
function ui_hero_slides(string $kecil, array $judul, $tag = '', array $tombol = [], ?array $foto = null, bool $tengah = false): void
{
    $imgs = array_values(array_filter($foto ?? hero_foto_bawaan(), function ($f) {
        return is_file(__DIR__ . '/../public/assets/' . $f);
    }));
    if (!$imgs) { $imgs = ['img/home.jpg']; }

    echo '<section class="hero hero--slides' . ($tengah ? ' hero--center' : '') . '" id="heroSlides"><div class="hero__bg" aria-hidden="true">';
    foreach ($imgs as $i => $img) {
        $u = esc(asset($img));
        echo $i === 0
            ? '<div class="hero__slide is-active" data-i="0" style="background-image:url(\'' . $u . '\')"></div>'
            : '<div class="hero__slide" data-i="' . $i . '" data-bg="' . $u . '"></div>';
    }
    echo '</div><div class="hero__content">';
    if ($kecil !== '') { echo '<p class="hero__small" data-type data-speed="35">' . esc($kecil) . '</p>'; }
    echo '<h1>';
    foreach ($judul as $n => $baris) {
        echo ($n ? '<br>' : '') . '<span data-type data-speed="70">' . esc($baris) . '</span>';
    }
    echo '</h1>';
    $tagBaris = is_array($tag) ? $tag : ($tag !== '' ? [$tag] : []);
    if ($tagBaris) {
        echo '<p class="hero__tag">';
        foreach ($tagBaris as $n => $baris) {
            echo ($n ? '<br>' : '') . '<span data-type data-speed="28">' . esc($baris) . '</span>';
        }
        echo '</p>';
    }
    if (count($tombol) === 2) { echo '<a href="' . esc($tombol[1]) . '" class="btn-ppdb btn-ppdb--hero">' . esc($tombol[0]) . '</a>'; }
    echo '</div></section>';
}