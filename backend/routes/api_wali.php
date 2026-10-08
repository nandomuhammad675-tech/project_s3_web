<?php
declare(strict_types=1);

use App\Controllers\Wali\AuthController;
use App\Controllers\Wali\SiswaController;
use App\Core\Router;
use App\Middleware\WaliMiddleware;

return static function (Router $r): void {
    $r->post('/api/wali/login', [AuthController::class, 'login']);
    $r->get('/api/wali/siswa',  [SiswaController::class, 'show'], [WaliMiddleware::class]);

    // Rencana langkah 6: GET /api/wali/nilai, GET /api/wali/absensi
};
