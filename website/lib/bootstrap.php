<?php
/**
 * lib/bootstrap.php - fungsi bantu BERSAMA untuk semua halaman website publik.
 * Dipanggil satu kali oleh public/index.php (router) sebelum halaman apa pun dimuat.
 * Memuat: base_url(), asset(), esc(), page_url(), tanggal_id(), layout_start()/layout_end().
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

/** Peta kunci menu -> alamat. Router: public/index.php membaca ?page=<nama folder di views/>. */
function page_url(string $key): string
{
    static $map = [
        'home'    => '',
        'profil'  => '?page=guru',
        'berita'  => '?page=kegiatan',
        'program' => '?page=program-pembiasaan',
        'ekstra'  => '?page=ekstrakurikuler',
        'galeri'  => '?page=galeri',
        'mading'  => '?page=mading',
        'kontak'  => '?page=kontak',
        'ppdb'    => '?page=ppdb',
    ];
    return base_url($map[$key] ?? $key);
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
 * Opsi: title, current (menu aktif), css / js (nama berkas di assets/css/pages & assets/js), description.
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
