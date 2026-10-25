<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\GaleriService;

final class GaleriController
{
    /** GET /api/admin/galeri?id_event=N atau ?mandiri=1 (hanya galeri tanpa event) */
    public function index(Request $req): void
    {
        $mandiri = in_array($req->query('mandiri'), ['1', 'true'], true);
        ResponseHelper::success('OK', (new GaleriService())->daftar(Pagination::angka($req, 'id_event'), $mandiri, Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new GaleriService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Foto galeri berhasil dibuat.', (new GaleriService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Foto galeri diperbarui.', (new GaleriService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new GaleriService())->hapus($req->id());
        ResponseHelper::success('Foto galeri berhasil dihapus.');
    }
}
