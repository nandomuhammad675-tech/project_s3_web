<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\MapelService;

final class MapelController
{
    /**
     * GET /api/guru/mapel : daftar mata pelajaran (read-only) untuk pilihan pada form Jadwal dan Nilai.
     * Mapel adalah data master milik Admin; Guru hanya membaca. Tidak ada rute tulis di sini.
     */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new MapelService())->daftar(Pagination::dari($req, 200, 500)));
    }
}
