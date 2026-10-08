<?php
declare(strict_types=1);

use App\Controllers\Guru\JadwalController;
use App\Controllers\Guru\PiketController;
use App\Controllers\Guru\ProfileController;
use App\Controllers\Guru\SiswaController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuruMiddleware;

return static function (Router $r): void {
    // Role guru + wajib punya kelas pada tahun ajaran aktif (kelas dari database, bukan dari klien).
    // Admin ditolak 403 di semua rute ini (AC-08).
    $guru = [[AuthMiddleware::class, 'guru'], GuruMiddleware::class];
    // Khusus /me: tetap 200 dengan kelas = null bila belum menjadi wali.
    $guruOpsional = [[AuthMiddleware::class, 'guru'], [GuruMiddleware::class, 'opsional']];

    $r->get('/api/guru/me',     [ProfileController::class, 'me'],     $guruOpsional);
    $r->get('/api/guru/siswa',  [SiswaController::class, 'index'],    $guru);

    // Jadwal pelajaran kelas sendiri
    $r->get('/api/guru/jadwal',          [JadwalController::class, 'index'],   $guru);
    $r->post('/api/guru/jadwal',         [JadwalController::class, 'store'],   $guru);
    $r->get('/api/guru/jadwal/{id}',     [JadwalController::class, 'show'],    $guru);
    $r->put('/api/guru/jadwal/{id}',     [JadwalController::class, 'update'],  $guru);
    $r->delete('/api/guru/jadwal/{id}',  [JadwalController::class, 'destroy'], $guru);

    // Piket kebersihan kelas sendiri
    $r->get('/api/guru/piket',          [PiketController::class, 'index'],   $guru);
    $r->post('/api/guru/piket',         [PiketController::class, 'store'],   $guru);
    $r->get('/api/guru/piket/{id}',     [PiketController::class, 'show'],    $guru);
    $r->put('/api/guru/piket/{id}',     [PiketController::class, 'update'],  $guru);
    $r->delete('/api/guru/piket/{id}',  [PiketController::class, 'destroy'], $guru);

    // Rencana langkah 4b: absensi, dasbor/absensi, nilai, disiplin
    // Rencana langkah 6: laporan
};
