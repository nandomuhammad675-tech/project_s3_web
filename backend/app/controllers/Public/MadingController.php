<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\MadingService;

final class MadingController
{
    /** GET /api/public/mading?kategori=&page=&per_page= : karya dengan status_tampil = 1; kategori = teks bebas (lihat `kategori_tersedia`). */
    public function index(Request $req): void
    {
        $k = $req->query('kategori');
        $kategori = is_string($k) && trim($k) !== '' ? mb_substr(trim($k), 0, 50) : null;
        ResponseHelper::success('OK', (new MadingService())->publik(Pagination::dari($req, 20, 100), $kategori));
    }
}
