<?php
/**
 * public/index.php - router tunggal website publik.
 *   .../public/                 -> views/home/index.php
 *   .../public/?page=guru       -> views/guru/index.php   (nama setelah page= = nama folder di views/; daftar resmi: page_map() di lib/bootstrap.php)
 * Alamat alias (?page=profil, berita, program, ekstra) dialihkan 301 ke alamat resmi.
 */
require __DIR__ . '/../lib/bootstrap.php';

// Tampilkan error hanya saat pengembangan (set SIKESA_DEBUG=1). Produksi: jangan bocorkan detail teknis.
$debug = getenv('SIKESA_DEBUG') === '1';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

$page = strtolower((string) preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($_GET['page'] ?? '')));
if ($page === '') { $page = 'home'; }

$alias = page_alias();
if (isset($alias[$page])) {
    $q = $_GET; unset($q['page']);
    header('Location: ' . page_url($page, $q), true, 301);
    exit;
}

$halaman = array_merge(['home'], array_values(array_filter(page_map())));
$file    = __DIR__ . '/../views/' . $page . '/index.php';

if (!in_array($page, $halaman, true) || !is_file($file)) {
    http_response_code(404);
    require __DIR__ . '/../views/errors/404.php';
    exit;
}

try {
    require $file;
} catch (Throwable $e) {
    error_log('[SI-KESA website] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code(500);
    require __DIR__ . '/../views/errors/500.php';
}
