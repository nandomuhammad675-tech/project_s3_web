<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\GuruKelasService;

final class SiswaController
{
    /** GET /api/guru/siswa: siswa aktif kelas sendiri. */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new GuruKelasService())->siswa($req->get('guru')));
    }
}
