<?php
declare(strict_types=1);

use App\Core\Config;

return [
    'name'      => Config::env('APP_NAME', 'SI-KESA API'),
    'env'       => Config::env('APP_ENV', 'local'),
    'timezone'  => Config::env('APP_TIMEZONE', 'Asia/Jakarta'),
    'base_path' => rtrim((string) Config::env('APP_BASE_PATH', ''), '/'),
];
