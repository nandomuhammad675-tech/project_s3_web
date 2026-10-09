<?php
declare(strict_types=1);

use App\Controllers\Admin\AkunController;
use App\Controllers\Admin\DasborController;
use App\Controllers\Admin\GuruController;
use App\Controllers\Admin\HariLiburController;
use App\Controllers\Admin\KelasController;
use App\Controllers\Admin\KenaikanKelasController;
use App\Controllers\Admin\MapelController;
use App\Controllers\Admin\RiwayatKelasController;
use App\Controllers\Admin\SiswaController;
use App\Controllers\Admin\TahunAjaranController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

return static function (Router $r): void {
    $admin = [[AuthMiddleware::class, 'admin']];   // semua rute di sini hanya untuk role admin

    // Akun internal (FR-AUTH-10). Tidak ada DELETE akun.
    $r->get('/api/admin/akun',                [AkunController::class, 'index'],         $admin);
    $r->post('/api/admin/akun',               [AkunController::class, 'store'],         $admin);
    $r->put('/api/admin/akun/{id}/status',    [AkunController::class, 'ubahStatus'],    $admin);
    $r->put('/api/admin/akun/{id}/password',  [AkunController::class, 'resetPassword'], $admin);

    // Tahun ajaran
    $r->get('/api/admin/tahun-ajaran',                 [TahunAjaranController::class, 'index'],    $admin);
    $r->post('/api/admin/tahun-ajaran',                [TahunAjaranController::class, 'store'],    $admin);
    $r->get('/api/admin/tahun-ajaran/{id}',            [TahunAjaranController::class, 'show'],     $admin);
    $r->put('/api/admin/tahun-ajaran/{id}',            [TahunAjaranController::class, 'update'],   $admin);
    $r->delete('/api/admin/tahun-ajaran/{id}',         [TahunAjaranController::class, 'destroy'],  $admin);
    $r->put('/api/admin/tahun-ajaran/{id}/aktifkan',   [TahunAjaranController::class, 'aktifkan'], $admin);

    // Kenaikan kelas (satu transaksi) dan log hasilnya. Tidak ada rute ubah/hapus: koreksi oleh pengembang (BR-37).
    $r->post('/api/admin/kenaikan-kelas',      [KenaikanKelasController::class, 'store'], $admin);
    $r->get('/api/admin/kenaikan-kelas/log',   [KenaikanKelasController::class, 'log'],   $admin);

    // Mata pelajaran
    $r->get('/api/admin/mapel',          [MapelController::class, 'index'],   $admin);
    $r->post('/api/admin/mapel',         [MapelController::class, 'store'],   $admin);
    $r->get('/api/admin/mapel/{id}',     [MapelController::class, 'show'],    $admin);
    $r->put('/api/admin/mapel/{id}',     [MapelController::class, 'update'],  $admin);
    $r->delete('/api/admin/mapel/{id}',  [MapelController::class, 'destroy'], $admin);

    // Hari libur (+ kelas target)
    $r->get('/api/admin/hari-libur',          [HariLiburController::class, 'index'],   $admin);
    $r->post('/api/admin/hari-libur',         [HariLiburController::class, 'store'],   $admin);
    $r->get('/api/admin/hari-libur/{id}',     [HariLiburController::class, 'show'],    $admin);
    $r->put('/api/admin/hari-libur/{id}',     [HariLiburController::class, 'update'],  $admin);
    $r->delete('/api/admin/hari-libur/{id}',  [HariLiburController::class, 'destroy'], $admin);

    // Guru (+ akun internalnya)
    $r->get('/api/admin/guru',          [GuruController::class, 'index'],   $admin);
    $r->post('/api/admin/guru',         [GuruController::class, 'store'],   $admin);
    $r->get('/api/admin/guru/{id}',     [GuruController::class, 'show'],    $admin);
    $r->put('/api/admin/guru/{id}',     [GuruController::class, 'update'],  $admin);
    $r->delete('/api/admin/guru/{id}',  [GuruController::class, 'destroy'], $admin);

    // Kelas (per tahun ajaran)
    $r->get('/api/admin/kelas',          [KelasController::class, 'index'],   $admin);
    $r->post('/api/admin/kelas',         [KelasController::class, 'store'],   $admin);
    $r->get('/api/admin/kelas/{id}',     [KelasController::class, 'show'],    $admin);
    $r->put('/api/admin/kelas/{id}',     [KelasController::class, 'update'],  $admin);
    $r->delete('/api/admin/kelas/{id}',  [KelasController::class, 'destroy'], $admin);

    // Siswa
    $r->get('/api/admin/siswa',          [SiswaController::class, 'index'],   $admin);
    $r->post('/api/admin/siswa',         [SiswaController::class, 'store'],   $admin);
    $r->get('/api/admin/siswa/{id}',     [SiswaController::class, 'show'],    $admin);
    $r->put('/api/admin/siswa/{id}',     [SiswaController::class, 'update'],  $admin);
    $r->delete('/api/admin/siswa/{id}',  [SiswaController::class, 'destroy'], $admin);

    // Penempatan siswa ke kelas (riwayat kelas)
    $r->get('/api/admin/riwayat-kelas',          [RiwayatKelasController::class, 'index'],   $admin);
    $r->post('/api/admin/riwayat-kelas',         [RiwayatKelasController::class, 'store'],   $admin);
    $r->get('/api/admin/riwayat-kelas/{id}',     [RiwayatKelasController::class, 'show'],    $admin);
    $r->put('/api/admin/riwayat-kelas/{id}',     [RiwayatKelasController::class, 'update'],  $admin);
    $r->delete('/api/admin/riwayat-kelas/{id}',  [RiwayatKelasController::class, 'destroy'], $admin);

    // Dasbor status absensi seluruh kelas (read-only)
    $r->get('/api/admin/dasbor/absensi', [DasborController::class, 'absensi'], $admin);

    // Rencana langkah 6: akademik (read-only absensi/nilai/disiplin), laporan, konten website, pengaturan
};
