<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Helpers\Validasi;
use App\Services\HariLiburService;

final class HariLiburController
{
    /** GET /api/admin/hari-libur?dari=YYYY-MM-DD&sampai=YYYY-MM-DD (keduanya opsional) */
    public function index(Request $req): void
    {
        $dari   = $this->tanggalQuery($req, 'dari');
        $sampai = $this->tanggalQuery($req, 'sampai');
        ResponseHelper::success('OK', (new HariLiburService())->daftar($dari, $sampai, Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new HariLiburService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Hari libur berhasil dibuat.', (new HariLiburService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Hari libur diperbarui.', (new HariLiburService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new HariLiburService())->hapus($req->id());
        ResponseHelper::success('Hari libur berhasil dihapus.');
    }

    private function tanggalQuery(Request $req, string $nama): ?string
    {
        $v = $req->query($nama);
        if ($v === null || $v === '') {
            return null;
        }
        if (!is_string($v) || !Validasi::adalahTanggal($v)) {
            throw HttpException::badRequest('Parameter ' . $nama . ' harus berformat YYYY-MM-DD.');
        }
        return $v;
    }
}
