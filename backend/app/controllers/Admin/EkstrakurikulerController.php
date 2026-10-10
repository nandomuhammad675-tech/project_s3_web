<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\EkstrakurikulerService;

final class EkstrakurikulerController
{
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new EkstrakurikulerService())->daftar(Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new EkstrakurikulerService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Ekstrakurikuler berhasil dibuat.', (new EkstrakurikulerService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Ekstrakurikuler diperbarui.', (new EkstrakurikulerService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new EkstrakurikulerService())->hapus($req->id());
        ResponseHelper::success('Ekstrakurikuler berhasil dihapus.');
    }
}
