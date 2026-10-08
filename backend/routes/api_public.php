<?php
declare(strict_types=1);

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

    // Rencana (TDD §6): GET /api/public/{guru, program-pembiasaan, ekstrakurikuler, event, galeri, mading, pengaturan}
};
