<?php
declare(strict_types=1);

use App\Controllers\Wali\AbsensiController;
use App\Controllers\Wali\AuthController;
use App\Controllers\Wali\NilaiController;
use App\Controllers\Wali\SiswaController;
use App\Core\Router;
use App\Middleware\WaliMiddleware;

return static function (Router $r): void {
    $r->post('/api/wali/login', [AuthController::class, 'login']);
    $r->get('/api/wali/siswa',  [SiswaController::class, 'show'], [WaliMiddleware::class]);

    // Read-only; scope dari token (id_siswa), tahun ajaran aktif
    $r->get('/api/wali/nilai',    [NilaiController::class, 'index'],    [WaliMiddleware::class]);
    $r->get('/api/wali/absensi',  [AbsensiController::class, 'index'],  [WaliMiddleware::class]);
};
