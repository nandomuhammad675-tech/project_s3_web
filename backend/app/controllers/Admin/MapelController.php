<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\MapelService;

final class MapelController
{
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new MapelService())->daftar(Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new MapelService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Mata pelajaran berhasil dibuat.', (new MapelService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Mata pelajaran diperbarui.', (new MapelService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new MapelService())->hapus($req->id());
        ResponseHelper::success('Mata pelajaran berhasil dihapus.');
    }
}
