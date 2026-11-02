<?php
declare(strict_types=1);

/**
 * SI-KESA | PWA Admin | Front controller sederhana.
 * Hak akses sebenarnya dijaga server (API). Di sini hanya merender kerangka halaman;
 * pengecekan token dilakukan di sisi browser (assets/js/auth.js).
 */

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$uriPath   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$route     = trim((string)substr($uriPath, strlen($scriptDir)), '/');
$route     = preg_replace('#^index\.php/?#', '', $route);
$route     = $route === '' ? 'dashboard' : $route;

/** URL helper (tanpa asumsi aplikasi berada di root domain). */
function url(string $path = ''): string
{
    global $scriptDir;
    return $scriptDir . '/' . ltrim($path, '/');
}

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$config = [
    'schoolName'   => 'SDN Kedungsari 1',
    'appName'      => 'SI-KESA',
    // Ganti lewat environment bila API berada di domain/folder lain.
    'apiBase'      => getenv('SIKESA_API_BASE') ?: '/api',
    'guruUrl'      => getenv('SIKESA_GURU_URL') ?: '/pwa-guru/',
    'base'         => $scriptDir,
    'endpoints'    => [
        'tahunAjaran' => '/api/admin/tahun-ajaran',
        'mapel'       => '/api/admin/mata-pelajaran',
        'guru'        => '/api/admin/guru',
        'siswa'       => '/api/admin/siswa',
        'kelas'       => '/api/admin/kelas',
        'absensi'     => '/api/admin/dasbor/absensi',
        'login'       => '/api/auth/login',
        'hariLibur'   => '/api/admin/hari-libur',
        'ekskul'      => '/api/admin/ekstrakurikuler',
        'riwayatKelas' => '/api/admin/riwayat-kelas',
    ],
];

/**
 * Daftar rute. Tidak ada rute Jadwal dan Piket (WAJIB, dokumen 01 bagian 3.1).
 */
$routes = [
    'dashboard'          => ['view' => 'dashboard/index',         'title' => 'Dasbor',                      'scripts' => ['dashboard.js']],
    'login'              => ['view' => 'auth/login',               'title' => 'Masuk',                       'layout' => false, 'public' => true],
    'ubah-password'      => ['view' => 'auth/ubah-password',       'title' => 'Ganti Password Saya'],
    'kenaikan-kelas'     => ['view' => 'kenaikan-kelas/index',     'title' => 'Tahun Ajaran & Kenaikan Kelas', 'scripts' => ['kenaikan-kelas.js'], 'styles' => ['wizard.css']],
    'mata-pelajaran' => ['view' => 'mata-pelajaran/index', 'title' => 'Mata Pelajaran', 'scripts' => ['mata-pelajaran.js'], 'styles' => ['master.css']],
    'tahun-ajaran' => ['view' => 'tahun-ajaran/index', 'title' => 'Tahun Ajaran', 'scripts' => ['tahun-ajaran.js'], 'styles' => ['master.css']],
    'guru'               => ['view' => 'guru/index',               'title' => 'Guru', 'scripts' => ['guru.js'], 'styles' => ['master.css']],
    'siswa'              => ['view' => 'siswa/index',              'title' => 'Siswa', 'scripts' => ['siswa.js'], 'styles' => ['master.css']],
    'kelas'              => ['view' => 'kelas/index',              'title' => 'Kelas', 'scripts' => ['kelas.js'], 'styles' => ['master.css']],
    'hari-libur'  => ['view' => 'hari-libur/index', 'title' => 'Hari Libur', 'scripts' => ['hari-libur.js'], 'styles' => ['master.css']],
    'ekstrakurikuler'    => ['view' => 'ekstrakurikuler/index',    'title' => 'Ekstrakurikuler', 'scripts' => ['ekstrakurikuler.js'], 'styles' => ['master.css']],
    'riwayat-kelas'      => ['view' => 'riwayat-kelas/index',      'title' => 'Penempatan Siswa', 'scripts' => ['riwayat-kelas.js'], 'styles' => ['master.css']],
    'event'              => ['view' => 'event/index',              'title' => 'Event & Galeri'],
    'galeri-mading'      => ['view' => 'galeri-mading/index',      'title' => 'Mading'],
    'program-pembiasaan' => ['view' => 'program-pembiasaan/index', 'title' => 'Program Pembiasaan'],
    'pengaturan'         => ['view' => 'pengaturan/index',         'title' => 'Pengaturan'],
    'absensi'            => ['view' => 'absensi/index',            'title' => 'Data Akademik: Absensi'],
    'nilai'              => ['view' => 'nilai/index',              'title' => 'Data Akademik: Nilai'],
    'disiplin'           => ['view' => 'disiplin/index',           'title' => 'Data Akademik: Kedisiplinan'],
    'laporan'            => ['view' => 'laporan/index',            'title' => 'Laporan Excel'],
    'akun'               => ['view' => 'akun/index',               'title' => 'Kelola Akun'],
];

foreach (['mata-pelajaran', 'tahun-ajaran', 'guru', 'siswa', 'kelas', 'hari-libur', 'ekstrakurikuler'] as $k) {
    $routes[$k]['crumb'] = ['Data Master'];
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: SAMEORIGIN');

$page = $routes[$route] ?? null;
if ($page === null) {
    http_response_code(404);
    $page = ['view' => 'errors/404', 'title' => 'Halaman tidak ditemukan'];
    $route = '404';
}

$viewFile = __DIR__ . '/views/' . $page['view'] . '.php';
if (!is_file($viewFile) || filesize($viewFile) === 0) {
    $viewFile = __DIR__ . '/views/errors/belum-siap.php';
}

$pageTitle   = $page['title'];
$pageScripts = $page['scripts'] ?? [];
$pageStyles  = $page['styles'] ?? [];
$breadcrumb = $page['crumb'] ?? null;
$requireAuth = empty($page['public']);
$activeRoute = $route;

if (($page['layout'] ?? true) === false) {
    require __DIR__ . '/views/layouts/header.php';
    require $viewFile;
    require __DIR__ . '/views/layouts/footer.php';
    exit;
}

require __DIR__ . '/views/layouts/header.php';
echo '<div class="app-shell">';
require __DIR__ . '/views/layouts/sidebar.php';
echo '<div class="app-main">';
require __DIR__ . '/views/layouts/topbar.php';
echo '<main class="page" id="main" tabindex="-1">';
require $viewFile;
echo '</main></div></div>';
require __DIR__ . '/views/layouts/footer.php';