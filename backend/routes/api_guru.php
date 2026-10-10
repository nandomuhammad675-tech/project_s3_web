<?php
declare(strict_types=1);

use App\Controllers\Guru\AbsensiController;
use App\Controllers\Guru\DasborController;
use App\Controllers\Guru\DisiplinController;
use App\Controllers\Guru\JadwalController;
use App\Controllers\Guru\LaporanController;
use App\Controllers\Guru\NilaiController;
use App\Controllers\Guru\PiketController;
use App\Controllers\Guru\ProfileController;
use App\Controllers\Guru\SiswaController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuruMiddleware;

return static function (Router $r): void {
    // Role guru + wajib punya kelas pada tahun ajaran aktif (kelas dari database, bukan dari klien).
    // Admin ditolak 403 di semua rute ini; Admin hanya membaca data akademik (rute read-only menyusul).
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

    // Absensi harian (tanpa DELETE) dan dasbor status absensi
    $r->get('/api/guru/absensi',         [AbsensiController::class, 'index'],  $guru);
    $r->post('/api/guru/absensi',        [AbsensiController::class, 'store'],  $guru);
    $r->put('/api/guru/absensi/{id}',    [AbsensiController::class, 'update'], $guru);
    $r->get('/api/guru/dasbor/absensi',  [DasborController::class, 'absensi'], $guru);

    // Nilai: DELETE hanya untuk UH (ditegakkan di service)
    $r->get('/api/guru/nilai',          [NilaiController::class, 'index'],   $guru);
    $r->post('/api/guru/nilai',         [NilaiController::class, 'store'],   $guru);
    $r->get('/api/guru/nilai/{id}',     [NilaiController::class, 'show'],    $guru);
    $r->put('/api/guru/nilai/{id}',     [NilaiController::class, 'update'],  $guru);
    $r->delete('/api/guru/nilai/{id}',  [NilaiController::class, 'destroy'], $guru);

    // Kedisiplinan: sengaja TANPA rute DELETE (=> 405)
    $r->get('/api/guru/disiplin',        [DisiplinController::class, 'index'],  $guru);
    $r->post('/api/guru/disiplin',       [DisiplinController::class, 'store'],  $guru);
    $r->get('/api/guru/disiplin/{id}',   [DisiplinController::class, 'show'],   $guru);
    $r->put('/api/guru/disiplin/{id}',   [DisiplinController::class, 'update'], $guru);

    // Laporan kelas sendiri, hanya tahun ajaran aktif (selain itu 403)
    $r->get('/api/guru/laporan',        [LaporanController::class, 'index'], $guru);
    $r->get('/api/guru/laporan/excel',  [LaporanController::class, 'excel'], $guru);
};
