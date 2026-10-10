<?php
declare(strict_types=1);

use App\Controllers\Admin\AkademikController;
use App\Controllers\Admin\AkunController;
use App\Controllers\Admin\LaporanController;
use App\Controllers\Admin\EkstrakurikulerController;
use App\Controllers\Admin\EventController;
use App\Controllers\Admin\GaleriController;
use App\Controllers\Admin\GuruEkskulController;
use App\Controllers\Admin\MadingController;
use App\Controllers\Admin\PengaturanController;
use App\Controllers\Admin\ProgramPembiasaanController;
use App\Controllers\Admin\UploadController;
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

    // --- Langkah 6a: konten website ---
    // Unggah gambar (multipart, field "file") -> path untuk banner/ikon/file_gambar/foto_profil
    $r->post('/api/admin/upload', [UploadController::class, 'store'], $admin);

    // Ekstrakurikuler (+ relasi Guru-ekskul)
    $r->get('/api/admin/ekstrakurikuler',          [EkstrakurikulerController::class, 'index'],   $admin);
    $r->post('/api/admin/ekstrakurikuler',         [EkstrakurikulerController::class, 'store'],   $admin);
    $r->get('/api/admin/ekstrakurikuler/{id}',     [EkstrakurikulerController::class, 'show'],    $admin);
    $r->put('/api/admin/ekstrakurikuler/{id}',     [EkstrakurikulerController::class, 'update'],  $admin);
    $r->delete('/api/admin/ekstrakurikuler/{id}',  [EkstrakurikulerController::class, 'destroy'], $admin);

    $r->get('/api/admin/guru-ekskul',          [GuruEkskulController::class, 'index'],   $admin);
    $r->post('/api/admin/guru-ekskul',         [GuruEkskulController::class, 'store'],   $admin);
    $r->delete('/api/admin/guru-ekskul/{id}',  [GuruEkskulController::class, 'destroy'], $admin);

    // Kegiatan/event/prestasi/kolaborasi, galeri, mading
    $r->get('/api/admin/event',          [EventController::class, 'index'],   $admin);
    $r->post('/api/admin/event',         [EventController::class, 'store'],   $admin);
    $r->get('/api/admin/event/{id}',     [EventController::class, 'show'],    $admin);
    $r->put('/api/admin/event/{id}',     [EventController::class, 'update'],  $admin);
    $r->delete('/api/admin/event/{id}',  [EventController::class, 'destroy'], $admin);

    $r->get('/api/admin/galeri',          [GaleriController::class, 'index'],   $admin);
    $r->post('/api/admin/galeri',         [GaleriController::class, 'store'],   $admin);
    $r->get('/api/admin/galeri/{id}',     [GaleriController::class, 'show'],    $admin);
    $r->put('/api/admin/galeri/{id}',     [GaleriController::class, 'update'],  $admin);
    $r->delete('/api/admin/galeri/{id}',  [GaleriController::class, 'destroy'], $admin);

    $r->get('/api/admin/mading',          [MadingController::class, 'index'],   $admin);
    $r->post('/api/admin/mading',         [MadingController::class, 'store'],   $admin);
    $r->get('/api/admin/mading/{id}',     [MadingController::class, 'show'],    $admin);
    $r->put('/api/admin/mading/{id}',     [MadingController::class, 'update'],  $admin);
    $r->delete('/api/admin/mading/{id}',  [MadingController::class, 'destroy'], $admin);

    // Enam program pembiasaan: hanya GET dan PUT
    $r->get('/api/admin/program-pembiasaan',       [ProgramPembiasaanController::class, 'index'],  $admin);
    $r->get('/api/admin/program-pembiasaan/{id}',  [ProgramPembiasaanController::class, 'show'],   $admin);
    $r->put('/api/admin/program-pembiasaan/{id}',  [ProgramPembiasaanController::class, 'update'], $admin);

    // URL Google Form PPDB dan video profil
    $r->get('/api/admin/pengaturan',  [PengaturanController::class, 'show'],   $admin);
    $r->put('/api/admin/pengaturan',  [PengaturanController::class, 'update'], $admin);

    // --- Langkah 6b ---
    // Data akademik Guru: HANYA GET (read-only); tidak ada rute tulis
    $r->get('/api/admin/akademik/absensi',   [AkademikController::class, 'absensi'],  $admin);
    $r->get('/api/admin/akademik/nilai',     [AkademikController::class, 'nilai'],    $admin);
    $r->get('/api/admin/akademik/disiplin',  [AkademikController::class, 'disiplin'], $admin);

    // Laporan semester: preview JSON dan unduhan Excel (seluruh tahun ajaran)
    $r->get('/api/admin/laporan',        [LaporanController::class, 'index'], $admin);
    $r->get('/api/admin/laporan/excel',  [LaporanController::class, 'excel'], $admin);
};
