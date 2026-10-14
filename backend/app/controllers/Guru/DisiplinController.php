<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\DisiplinService;

/** Tidak ada method destroy: catatan kedisiplinan tidak dapat dihapus (DELETE => 405). */
final class DisiplinController
{
    /** GET /api/guru/disiplin?id_siswa= (opsional) */
    public function index(Request $req): void
    {
        $hasil = (new DisiplinService())->daftar($req->get('guru'), Pagination::angka($req, 'id_siswa'), Pagination::dari($req, 100));
        ResponseHelper::success('OK', $hasil);
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new DisiplinService())->detail($req->get('guru'), $req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Catatan kedisiplinan berhasil disimpan.', (new DisiplinService())->buat($req->get('guru'), $req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Catatan kedisiplinan diperbarui.', (new DisiplinService())->ubah($req->get('guru'), $req->id(), $req->json()));
    }
}
