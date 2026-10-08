<?php
declare(strict_types=1);

use App\Controllers\Auth\AuthController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

return static function (Router $r): void {
    $r->post('/api/auth/login',    [AuthController::class, 'login']);
    $r->post('/api/auth/logout',   [AuthController::class, 'logout'],         [AuthMiddleware::class]);
    $r->put('/api/auth/password',  [AuthController::class, 'changePassword'], [AuthMiddleware::class]);

    // SEMENTARA (uji middleware); hapus di langkah 4
    $r->get('/api/auth/me',        [AuthController::class, 'me'],             [AuthMiddleware::class]);
};
