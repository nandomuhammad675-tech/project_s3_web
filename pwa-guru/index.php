<?php
declare(strict_types=1);

/**
 * SI-KESA PWA Guru | front controller sederhana.
 * Rute: index.php?hal=dashboard|absensi|nilai|jadwal|piket|disiplin|laporan|profil|login
 * Hak akses sebenarnya dijaga server (API); pengecekan token di browser ada di assets/js/app.js.
 */
require __DIR__ . '/config.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: SAMEORIGIN');

$routes = sk_routes();
$hal    = (string) ($_GET['hal'] ?? 'dashboard');

if (!isset($routes[$hal])) {
    http_response_code(404);
    $hal = 'dashboard';
    header('Location: ' . sk_url('dashboard'), true, 302);
    exit;
}

require __DIR__ . '/views/' . $routes[$hal];
