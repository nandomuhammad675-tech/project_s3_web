<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\PiketService;

final class PiketController
{
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new PiketService())->daftar($req->get('guru')));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new PiketService())->detail($req->get('guru'), $req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Jadwal piket berhasil dibuat.', (new PiketService())->buat($req->get('guru'), $req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Jadwal piket diperbarui.', (new PiketService())->ubah($req->get('guru'), $req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new PiketService())->hapus($req->get('guru'), $req->id());
        ResponseHelper::success('Jadwal piket berhasil dihapus.');
    }
}
