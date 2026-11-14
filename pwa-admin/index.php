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

$legacy = [
    'absensi'       => ['akademik', 'absensi'],
    'nilai'         => ['akademik', 'nilai'],
    'disiplin'      => ['akademik', 'disiplin'],
    'ubah-password' => ['akun', 'password'],
];
if (isset($legacy[$route])) {
    header('Location: ' . url($legacy[$route][0]) . '#' . $legacy[$route][1], true, 302);
    exit;
}

/**
 * Alamat API dan PWA Guru diturunkan otomatis dari lokasi folder ini
 * (susunan XAMPP: htdocs/si-kesa/{backend,pwa-admin,pwa-guru,...}).
 * Ubah lewat environment (SIKESA_API_BASE, SIKESA_GURU_URL) atau config.local.php:
 *   <?php return ['apiBase' => 'https://api.sekolah.sch.id/api', 'guruUrl' => 'https://guru.sekolah.sch.id/index.php?hal=login'];
 * apiBase = alamat backend/public DITAMBAH "/api", tanpa garis miring akhir.
 */
$induk = rtrim(str_replace('\\', '/', dirname($scriptDir)), '/');
if ($induk === '.' || $induk === '/') {
    $induk = '';
}

$config = [
    'schoolName'   => 'SDN Kedungsari 1',
    'appName'      => 'SI-KESA',
    'apiBase'      => rtrim((string)(getenv('SIKESA_API_BASE') ?: $induk . '/backend/public/api'), '/'),
    'guruUrl'      => getenv('SIKESA_GURU_URL') ?: $induk . '/pwa-guru/index.php?hal=login',
    'guruHome'     => getenv('SIKESA_GURU_HOME') ?: $induk . '/pwa-guru/index.php?hal=dashboard',
    'base'         => $scriptDir,
    'endpoints'    => [
        'tahunAjaran' => '/api/admin/tahun-ajaran',
        'mapel'       => '/api/admin/mapel',
        'guru'        => '/api/admin/guru',
        'siswa'       => '/api/admin/siswa',
        'kelas'       => '/api/admin/kelas',
        'absensi'     => '/api/admin/dasbor/absensi',
        'login'       => '/api/auth/login',
        'hariLibur'   => '/api/admin/hari-libur',
        'ekskul'      => '/api/admin/ekstrakurikuler',
        'guruEkskul'  => '/api/admin/guru-ekskul',
        'riwayatKelas' => '/api/admin/riwayat-kelas',
        'event'        => '/api/admin/event',
        'mading'       => '/api/admin/mading',
        'galeri'       => '/api/admin/galeri',
        'programPembiasaan' => '/api/admin/program-pembiasaan',
        'pengaturan'   => '/api/admin/pengaturan',
        'akademik'     => '/api/admin/akademik',
        'laporan'      => '/api/admin/laporan',
        'akun'         => '/api/admin/akun',
        'ubahPassword' => '/api/auth/password',
        'upload'       => '/api/admin/upload',
    ],
];

if (is_file(__DIR__ . '/config.local.php')) {
    $lokal = require __DIR__ . '/config.local.php';
    if (is_array($lokal)) {
        $config = array_replace_recursive($config, $lokal);
        $config['apiBase'] = rtrim((string)$config['apiBase'], '/');
    }
}
// Alamat dasar berkas unggahan (path "uploads/xxx.png" dibuka di {uploadBase}/{path})
$config['uploadBase'] = preg_replace('#/api$#', '', $config['apiBase']);

/**
 * Daftar rute. Tidak ada rute Jadwal dan Piket (WAJIB, dokumen 01 bagian 3.1).
 */
$routes = [
    'dashboard'          => ['view' => 'dashboard/index',         'title' => 'Dasbor',                      'scripts' => ['dashboard.js']],
    'login'              => ['view' => 'auth/login',               'title' => 'Masuk',                       'layout' => false, 'public' => true],
    'profil'             => ['view' => 'akun/profil', 'title' => 'Profil Akun', 'scripts' => ['profil.js']],
    'ubah-password'      => ['view' => 'auth/ubah-password',       'title' => 'Ganti Password Saya'],
    'kenaikan-kelas'     => ['view' => 'kenaikan-kelas/index',     'title' => 'Tahun Ajaran & Kenaikan Kelas', 'scripts' => ['kenaikan-kelas.js'], 'styles' => ['wizard.css']],
    'mata-pelajaran' => ['view' => 'mata-pelajaran/index', 'title' => 'Mata Pelajaran', 'scripts' => ['mata-pelajaran.js'], 'styles' => ['master.css']],
    'tahun-ajaran' => ['view' => 'tahun-ajaran/index', 'title' => 'Tahun Ajaran', 'scripts' => ['tahun-ajaran.js'], 'styles' => ['master.css']],
    'guru'               => ['view' => 'guru/index',               'title' => 'Guru', 'scripts' => ['guru.js'], 'styles' => ['master.css']],
    'siswa'              => ['view' => 'siswa/index',              'title' => 'Siswa', 'scripts' => ['siswa.js', 'siswa-impor.js'], 'styles' => ['master.css']],
    'kelas'              => ['view' => 'kelas/index',              'title' => 'Kelas', 'scripts' => ['kelas.js'], 'styles' => ['master.css']],
    'hari-libur'  => ['view' => 'hari-libur/index', 'title' => 'Hari Libur', 'scripts' => ['hari-libur.js'], 'styles' => ['master.css']],
    'ekstrakurikuler'    => ['view' => 'ekstrakurikuler/index',    'title' => 'Ekstrakurikuler', 'scripts' => ['ekstrakurikuler.js'], 'styles' => ['master.css']],
    'riwayat-kelas'      => ['view' => 'riwayat-kelas/index',      'title' => 'Penempatan Siswa', 'scripts' => ['riwayat-kelas.js'], 'styles' => ['master.css']],
    'event' => ['view' => 'event/index', 'title' => 'Event & Galeri', 'scripts' => ['event.js', 'event-galeri.js'], 'styles' => ['master.css', 'event.css']],
    'galeri-mading' => ['view' => 'galeri-mading/index', 'title' => 'Mading', 'crumb' => ['Konten Website'], 'scripts' => ['galeri-mading.js'], 'styles' => ['master.css', 'mading.css']],
    'program-pembiasaan' => ['view' => 'program-pembiasaan/index', 'title' => 'Program Pembiasaan', 'crumb' => ['Konten Website'], 'scripts' => ['program-pembiasaan.js'], 'styles' => ['master.css', 'pembiasaan.css']],
    'pengaturan' => ['view' => 'pengaturan/index', 'title' => 'Pengaturan Website', 'crumb' => ['Konten Website'], 'scripts' => ['pengaturan.js'], 'styles' => ['master.css', 'pengaturan.css']],
    'absensi'            => ['view' => 'absensi/index',            'title' => 'Data Akademik: Absensi'],
    'nilai'              => ['view' => 'nilai/index',              'title' => 'Data Akademik: Nilai'],
    'disiplin'           => ['view' => 'disiplin/index',           'title' => 'Data Akademik: Kedisiplinan'],
    'laporan' => ['view' => 'laporan/index', 'title' => 'Laporan Excel', 'scripts' => ['laporan.js'], 'styles' => ['master.css', 'laporan.css']],
    'akun' => ['view' => 'akun/index', 'title' => 'Akun', 'scripts' => ['akun.js'], 'styles' => ['master.css', 'akun.css']],
    'akademik' => ['view' => 'akademik/index', 'title' => 'Data Akademik', 'scripts' => ['akademik.js'], 'styles' => ['master.css', 'akademik.css']],

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
    // Halaman mandiri (login): dokumen HTML lengkap dari view, tanpa kerangka sidebar.
    require $viewFile;
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