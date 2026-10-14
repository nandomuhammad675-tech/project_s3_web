<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\GuruService;

final class GuruController
{
    /** GET /api/public/guru: nama, NIP, jabatan, foto (tanpa email/telepon; NFR-SEC-08). */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new GuruService())->publik());
    }
}
