<?php
/**
 * public/index.php  -  Router sederhana
 *
 * Cara kerja:
 *   .../public/                 -> views/home/index.php
 *   .../public/?page=guru       -> views/guru/index.php
 *   .../public/?page=galeri     -> views/galeri/index.php
 *   (nama setelah page= = nama folder di website/views/)
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page = $_GET['page'] ?? 'home';
$page = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $page));   // buang karakter berbahaya
if ($page === '') {
    $page = 'home';
}

// Folder yang bukan halaman
$tertutup = ['layouts', 'partials', 'errors'];

$file = __DIR__ . '/../views/' . $page . '/index.php';

if (in_array($page, $tertutup, true) || !is_file($file)) {
    http_response_code(404);
    echo '<h1>404 - Halaman tidak ditemukan</h1>';
    echo '<p>Folder <code>views/' . htmlspecialchars($page) . '/index.php</code> belum ada.</p>';
    echo '<p><a href="./">Kembali ke beranda</a></p>';
    exit;
}

require $file;