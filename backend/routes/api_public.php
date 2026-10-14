<?php
declare(strict_types=1);

use App\Controllers\Public\EkstrakurikulerController;
use App\Controllers\Public\EventController;
use App\Controllers\Public\GaleriController;
use App\Controllers\Public\GuruController;
use App\Controllers\Public\MadingController;
use App\Controllers\Public\PengaturanController;
use App\Controllers\Public\ProgramPembiasaanController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Helpers\ResponseHelper;

return static function (Router $r): void {
    // --- SEMENTARA untuk uji langkah 1; hapus setelah langkah 2 selesai ---
    $r->get('/api/public/ping', static function (Request $req): void {
        ResponseHelper::success('pong', ['waktu' => date('c')]);
    });
    $r->get('/api/public/ping-db', static function (Request $req): void {
        $row = Database::connection()->query('SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = DATABASE()')->fetch();
        ResponseHelper::success('Koneksi database OK.', ['jumlah_tabel' => (int) $row['n']]);
    });

    // Website publik (TDD §6): tanpa login, hanya GET, hanya field daftar putih.
    $r->get('/api/public/guru',                [GuruController::class, 'index']);
    $r->get('/api/public/program-pembiasaan',  [ProgramPembiasaanController::class, 'index']);
    $r->get('/api/public/ekstrakurikuler',     [EkstrakurikulerController::class, 'index']);
    $r->get('/api/public/event',               [EventController::class, 'index']);
    $r->get('/api/public/galeri',              [GaleriController::class, 'index']);
    $r->get('/api/public/mading',              [MadingController::class, 'index']);
    $r->get('/api/public/pengaturan',          [PengaturanController::class, 'index']);
};
