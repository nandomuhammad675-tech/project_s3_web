<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\ProgramPembiasaanService;

/** Enam program tetap: hanya GET dan PUT (tidak ada tambah/hapus). */
final class ProgramPembiasaanController
{
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new ProgramPembiasaanService())->daftar());
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new ProgramPembiasaanService())->detail($req->id()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Program pembiasaan diperbarui.', (new ProgramPembiasaanService())->ubah($req->id(), $req->json()));
    }
}
