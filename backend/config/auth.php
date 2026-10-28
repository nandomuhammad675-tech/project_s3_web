<?php
declare(strict_types=1);

use App\Core\Config;

return [
    'jwt_secret'   => (string) Config::env('JWT_SECRET', ''),
    'jwt_algo'     => 'HS256',
    'ttl_internal' => (int) Config::env('JWT_TTL_INTERNAL', 28800), // 8 jam (FR-AUTH-05)
    'ttl_wali'     => (int) Config::env('JWT_TTL_WALI', 3600),      // usulan 60 menit

    // Login Wali (NISN saja): batasi percobaan GAGAL per NISN dan per IP (NFR-SEC-07)
    'rate_limit_wali' => [
        'max'    => (int) Config::env('RATE_LIMIT_WALI_MAX', 5),
        'max_ip' => (int) Config::env('RATE_LIMIT_WALI_MAX_IP', 20),
        'window' => (int) Config::env('RATE_LIMIT_WALI_WINDOW', 300),
    ],
    // Login Admin/Guru dan ganti password (TDD §12: disarankan)
    'rate_limit_login' => [
        'max'    => (int) Config::env('RATE_LIMIT_LOGIN_MAX', 10),
        'max_ip' => (int) Config::env('RATE_LIMIT_LOGIN_MAX_IP', 30),
        'window' => (int) Config::env('RATE_LIMIT_LOGIN_WINDOW', 300),
    ],
];
