<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\KelasService;

final class KelasController
{
    /** GET /api/admin/kelas?id_tahun_ajaran= */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new KelasService())->daftar(Pagination::angka($req, 'id_tahun_ajaran'), Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new KelasService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Kelas berhasil dibuat.', (new KelasService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Kelas diperbarui.', (new KelasService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new KelasService())->hapus($req->id());
        ResponseHelper::success('Kelas berhasil dihapus.');
    }
}
