<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\GuruKelasService;

final class ProfileController
{
    /** GET /api/guru/me: tetap 200 dengan kelas = null bila Guru belum menjadi wali kelas (TDD §3.1). */
    public function me(Request $req): void
    {
        ResponseHelper::success('OK', (new GuruKelasService())->profil($req->get('guru'), (string) $req->get('auth')['username']));
    }
}
