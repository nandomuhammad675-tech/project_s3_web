<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\GuruEkskulService;

final class GuruEkskulController
{
    /** GET /api/admin/guru-ekskul?id_guru=&id_ekskul= (opsional) */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new GuruEkskulService())->daftar(
            Pagination::angka($req, 'id_guru'), Pagination::angka($req, 'id_ekskul'), Pagination::dari($req)
        ));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Guru berhasil dikaitkan dengan ekstrakurikuler.', (new GuruEkskulService())->buat($req->json()));
    }

    public function destroy(Request $req): void
    {
        (new GuruEkskulService())->hapus($req->id());
        ResponseHelper::success('Keterkaitan Guru dan ekstrakurikuler dihapus.');
    }
}
