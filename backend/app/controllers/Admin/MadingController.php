<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\MadingService;

final class MadingController
{
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new MadingService())->daftar(Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new MadingService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Karya mading berhasil dibuat.', (new MadingService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Karya mading diperbarui.', (new MadingService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new MadingService())->hapus($req->id());
        ResponseHelper::success('Karya mading berhasil dihapus.');
    }
}
