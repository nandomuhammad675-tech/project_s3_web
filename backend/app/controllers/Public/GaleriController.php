<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\GaleriService;

final class GaleriController
{
    /** GET /api/public/galeri : hanya galeri mandiri (id_event NULL); foto event datang bersama /api/public/event. */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new GaleriService())->publikMandiri(Pagination::dari($req, 20, 100)));
    }
}
