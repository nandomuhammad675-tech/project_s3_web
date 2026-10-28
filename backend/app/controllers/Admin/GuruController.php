<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\GuruService;

final class GuruController
{
    /** GET /api/admin/guru?q=nama-atau-nip */
    public function index(Request $req): void
    {
        $q = $req->query('q');
        ResponseHelper::success('OK', (new GuruService())->daftar(is_string($q) ? trim($q) : null, Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new GuruService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Guru dan akunnya berhasil dibuat.', (new GuruService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Data guru diperbarui.', (new GuruService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new GuruService())->hapus($req->id());
        ResponseHelper::success('Guru berhasil dihapus.');
    }
}
