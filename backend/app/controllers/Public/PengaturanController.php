<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\PengaturanService;

final class PengaturanController
{
    /** GET /api/public/pengaturan : hanya ppdb_form_url dan video_profil_url (daftar putih). */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new PengaturanService())->publik());
    }
}
