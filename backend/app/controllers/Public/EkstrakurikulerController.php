<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\EkstrakurikulerService;

final class EkstrakurikulerController
{
    /** GET /api/public/ekstrakurikuler */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new EkstrakurikulerService())->publik());
    }
}
