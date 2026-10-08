<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\TahunAjaranService;

final class TahunAjaranController
{
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new TahunAjaranService())->daftar());
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new TahunAjaranService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Tahun ajaran berhasil dibuat.', (new TahunAjaranService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Tahun ajaran diperbarui.', (new TahunAjaranService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new TahunAjaranService())->hapus($req->id());
        ResponseHelper::success('Tahun ajaran berhasil dihapus.');
    }

    public function aktifkan(Request $req): void
    {
        ResponseHelper::success('Tahun ajaran diaktifkan.', (new TahunAjaranService())->aktifkan($req->id()));
    }
}
