<?php
declare(strict_types=1);

namespace App\Controllers\Wali;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Repositories\DataSiswaRepository;

final class SiswaController
{
    /** GET /api/wali/siswa: profil siswa dari token (id dari klien tidak dipakai). */
    public function show(Request $req): void
    {
        $wali   = $req->get('wali');
        $profil = (new DataSiswaRepository())->profilWali((int) $wali['id_siswa']);
        if ($profil === null) {
            throw HttpException::notFound();
        }
        ResponseHelper::success('OK', $profil);
    }
}
