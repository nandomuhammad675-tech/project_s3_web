<?php
/**
 * Pintu masuk PWA Guru SI-KESA
 * Route lewat query string:  index.php?page=beranda | absensi | nilai | jadwal | piket | disiplin | laporan | akun | login
 * Halaman yang filenya masih kosong otomatis menampilkan "sedang disiapkan".
 */

// ===== PENGATURAN =====
$apiBase = '../backend/public';      // alamat dasar backend (TANPA /api; path endpoint sudah berawalan /api/...), relatif terhadap halaman PWA
$root    = '';                      // alamat folder pwa-guru/ relatif terhadap index.php

// ===== Fungsi bantu (nama sengaja berbeda dari login.php: esc / aset) =====
function e($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }

function icon(string $nama, string $kelas = ''): string {
    return '<svg class="i ' . e($kelas) . '" aria-hidden="true" focusable="false"><use href="#i-' . e($nama) . '"/></svg>';
}

function asset(string $path): string {
    global $root;
    $file = __DIR__ . '/assets/' . $path;
    $ver  = is_file($file) ? filemtime($file) : 1;
    return $root . 'assets/' . implode('/', array_map('rawurlencode', explode('/', $path))) . '?v=' . $ver;
}

// ===== Daftar halaman =====
$routes = [
    'beranda'  => ['file' => 'dashboard/index.php',      'title' => 'Beranda',     'nav' => 'beranda',  'js' => ['dashboard.js']],
    'absensi'  => ['file' => 'absensi/list.php',         'title' => 'Absensi',     'nav' => 'absensi',  'js' => ['absensi.js']],
    'nilai'    => ['file' => 'nilai/pilih-mapel.php',    'title' => 'Nilai',       'nav' => 'nilai',    'js' => ['nilai.js']],
    'jadwal'   => ['file' => 'jadwal/index.php',         'title' => 'Jadwal',      'nav' => 'jadwal',   'js' => ['jadwal.js']],
    'piket'    => ['file' => 'piket/index.php',          'title' => 'Piket',       'nav' => 'piket',    'js' => ['piket.js']],
    'disiplin' => ['file' => 'disiplin/index.php',       'title' => 'Kedisiplinan','nav' => 'disiplin', 'js' => ['disiplin.js']],
    'laporan'  => ['file' => 'laporan/index.php',        'title' => 'Laporan',     'nav' => 'laporan',  'js' => ['laporan.js']],
    'akun'     => ['file' => 'profil/ubah-password.php', 'title' => 'Akun',        'nav' => 'akun',     'js' => ['profil.js']],
];

// ===== Menu navigasi (dipakai sidebar & bottomnav) =====
$nav = [
    ['key' => 'beranda',  'label' => 'Beranda',     'icon' => 'home',            'grup' => 'Utama', 'bawah' => true],
    ['key' => 'absensi',  'label' => 'Absensi',     'icon' => 'clipboard-check', 'grup' => 'Utama', 'bawah' => true],
    ['key' => 'nilai',    'label' => 'Nilai',       'icon' => 'book-open',       'grup' => 'Utama', 'bawah' => true],
    ['key' => 'jadwal',   'label' => 'Jadwal',      'icon' => 'calendar',        'grup' => 'Utama', 'bawah' => true],
    ['key' => 'piket',    'label' => 'Piket',       'icon' => 'key-round',       'grup' => 'Kelas', 'bawah' => false],
    ['key' => 'disiplin', 'label' => 'Kedisiplinan','icon' => 'shield-check',    'grup' => 'Kelas', 'bawah' => false],
    ['key' => 'laporan',  'label' => 'Laporan',     'icon' => 'file-text',       'grup' => 'Kelas', 'bawah' => false],
    ['key' => 'akun',     'label' => 'Akun',        'icon' => 'user-round',      'grup' => 'Akun',  'bawah' => true],
];

// ===== Pilih halaman =====
$page = strtolower(preg_replace('/[^a-zA-Z-]/', '', (string) ($_GET['page'] ?? 'beranda')));
if ($page === '') { $page = 'beranda'; }

// Login punya dokumen HTML sendiri (tanpa sidebar/topbar)
if ($page === 'login') {
    require __DIR__ . '/views/auth/login.php';
    exit;
}

$route = $routes[$page] ?? null;
if ($route === null) {
    http_response_code(404);
    $route = ['file' => 'errors/404.php', 'title' => 'Halaman tidak ditemukan', 'nav' => '', 'js' => []];
}

$pageTitle    = $route['title'];
$activePage   = $route['nav'];
$extraScripts = $route['js'];
$viewFile     = __DIR__ . '/views/' . $route['file'];

require __DIR__ . '/views/layouts/header.php';
require __DIR__ . '/views/layouts/topbar.php';

echo '<main class="content" id="konten">', "\n";
if (is_file($viewFile) && filesize($viewFile) > 0) {
    require $viewFile;
} else {
    $emptyJudul = $pageTitle;
    $emptyPesan = 'Halaman ini sedang disiapkan.';
    require __DIR__ . '/views/partials/empty-state.php';
}
echo "\n</main>\n";

require __DIR__ . '/views/layouts/bottomnav.php';
require __DIR__ . '/views/layouts/footer.php';