<?php
declare(strict_types=1);

use App\Core\Config;

$origins = array_filter(array_map('trim', explode(',', (string) Config::env('CORS_ALLOWED_ORIGINS', '*'))));

return [
    'allowed_origins' => $origins,
    'allowed_methods' => 'GET, POST, PUT, DELETE, OPTIONS',
    'allowed_headers' => 'Content-Type, Authorization',
    'max_age'         => 600,
];
