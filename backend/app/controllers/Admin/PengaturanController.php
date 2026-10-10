<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\PengaturanService;

final class PengaturanController
{
    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new PengaturanService())->admin());
    }

    /** PUT /api/admin/pengaturan {ppdb_form_url?, video_profil_url?}: pengubah dan waktu tercatat. */
    public function update(Request $req): void
    {
        ResponseHelper::success('Pengaturan diperbarui.', (new PengaturanService())->ubah((int) $req->get('auth')['id_user'], $req->json()));
    }
}
