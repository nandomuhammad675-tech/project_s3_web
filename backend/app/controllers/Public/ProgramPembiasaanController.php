<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\ProgramPembiasaanService;

final class ProgramPembiasaanController
{
    /** GET /api/public/program-pembiasaan: enam program, urut Senin-Sabtu. */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new ProgramPembiasaanService())->publik());
    }
}
