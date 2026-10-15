<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\SiswaService;

final class SiswaController
{
    /** GET /api/admin/siswa?q=nama-atau-nisn&status=baru|aktif|lulus|pindah */
    public function index(Request $req): void
    {
        $q = $req->query('q');
        $status = $req->query('status');
        if ($status !== null && $status !== '' && !in_array($status, ['baru', 'aktif', 'lulus', 'pindah'], true)) {
            throw HttpException::badRequest('Parameter status harus salah satu dari: baru, aktif, lulus, pindah.');
        }
        $hasil = (new SiswaService())->daftar(
            is_string($q) ? trim($q) : null,
            ($status === null || $status === '') ? null : (string) $status,
            Pagination::dari($req)
        );
        ResponseHelper::success('OK', $hasil);
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new SiswaService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Siswa berhasil dicatat dengan status baru.', (new SiswaService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Data siswa diperbarui.', (new SiswaService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new SiswaService())->hapus($req->id());
        ResponseHelper::success('Siswa berhasil dihapus.');
    }
}
