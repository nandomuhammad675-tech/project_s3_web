<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\NilaiService;

final class NilaiController
{
    /** GET /api/guru/nilai?id_mapel=&semester=&id_siswa= (semua opsional) */
    public function index(Request $req): void
    {
        $semester = $req->query('semester');
        if ($semester !== null && $semester !== '' && !in_array($semester, ['ganjil', 'genap'], true)) {
            throw HttpException::badRequest('Parameter semester harus ganjil atau genap.');
        }
        $filter = [
            'id_mapel' => Pagination::angka($req, 'id_mapel'),
            'id_siswa' => Pagination::angka($req, 'id_siswa'),
            'semester' => ($semester === null || $semester === '') ? null : (string) $semester,
        ];
        ResponseHelper::success('OK', (new NilaiService())->daftar($req->get('guru'), $filter, Pagination::dari($req, 100)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new NilaiService())->detail($req->get('guru'), $req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Nilai berhasil disimpan.', (new NilaiService())->buat($req->get('guru'), $req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Nilai diperbarui.', (new NilaiService())->ubah($req->get('guru'), $req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new NilaiService())->hapus($req->get('guru'), $req->id());
        ResponseHelper::success('Nilai UH berhasil dihapus.');
    }
}
