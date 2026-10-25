<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\RiwayatKelasService;

final class RiwayatKelasController
{
    /** GET /api/admin/riwayat-kelas?id_kelas=&id_tahun_ajaran=&id_siswa= */
    public function index(Request $req): void
    {
        $filter = [
            'id_kelas'        => Pagination::angka($req, 'id_kelas'),
            'id_tahun_ajaran' => Pagination::angka($req, 'id_tahun_ajaran'),
            'id_siswa'        => Pagination::angka($req, 'id_siswa'),
        ];
        ResponseHelper::success('OK', (new RiwayatKelasService())->daftar($filter, Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new RiwayatKelasService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Siswa berhasil ditempatkan.', (new RiwayatKelasService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Nomor absen diperbarui.', (new RiwayatKelasService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new RiwayatKelasService())->hapus($req->id());
        ResponseHelper::success('Penempatan berhasil dihapus.');
    }
}
