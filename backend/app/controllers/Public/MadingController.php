<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\MadingService;

final class MadingController
{
    /** GET /api/public/mading : karya dengan status_tampil = 1. */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new MadingService())->publik(Pagination::dari($req, 20, 100)));
    }
}
