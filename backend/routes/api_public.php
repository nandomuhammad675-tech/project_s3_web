<?php
declare(strict_types=1);

use App\Controllers\Public\EkstrakurikulerController;
use App\Controllers\Public\EventController;
use App\Controllers\Public\GaleriController;
use App\Controllers\Public\GuruController;
use App\Controllers\Public\MadingController;
use App\Controllers\Public\PengaturanController;
use App\Controllers\Public\ProgramPembiasaanController;
use App\Core\Router;

return static function (Router $r): void {
    // Website publik (TDD §6): tanpa login, hanya GET, hanya field daftar putih.
    $r->get('/api/public/guru',                [GuruController::class, 'index']);
    $r->get('/api/public/program-pembiasaan',  [ProgramPembiasaanController::class, 'index']);
    $r->get('/api/public/ekstrakurikuler',     [EkstrakurikulerController::class, 'index']);
    $r->get('/api/public/event',               [EventController::class, 'index']);
    $r->get('/api/public/galeri',              [GaleriController::class, 'index']);
    $r->get('/api/public/mading',              [MadingController::class, 'index']);
    $r->get('/api/public/pengaturan',          [PengaturanController::class, 'index']);
};
