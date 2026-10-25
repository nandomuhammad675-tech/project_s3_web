<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\JadwalService;
use App\Validators\JadwalValidator;

final class JadwalController
{
    /** GET /api/guru/jadwal?semester=ganjil|genap&hari=senin..sabtu (opsional) */
    public function index(Request $req): void
    {
        $semester = $req->query('semester');
        $hari     = $req->query('hari');
        if ($semester !== null && $semester !== '' && !in_array($semester, ['ganjil', 'genap'], true)) {
            throw HttpException::badRequest('Parameter semester harus ganjil atau genap.');
        }
        if ($hari !== null && $hari !== '' && !in_array($hari, JadwalValidator::HARI, true)) {
            throw HttpException::badRequest('Parameter hari harus salah satu dari: ' . implode(', ', JadwalValidator::HARI) . '.');
        }

        $hasil = (new JadwalService())->daftar(
            $req->get('guru'),
            ($semester === null || $semester === '') ? null : (string) $semester,
            ($hari === null || $hari === '') ? null : (string) $hari,
            Pagination::dari($req, 100)
        );
        ResponseHelper::success('OK', $hasil);
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new JadwalService())->detail($req->get('guru'), $req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Jadwal berhasil dibuat.', (new JadwalService())->buat($req->get('guru'), $req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Jadwal diperbarui.', (new JadwalService())->ubah($req->get('guru'), $req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new JadwalService())->hapus($req->get('guru'), $req->id());
        ResponseHelper::success('Jadwal berhasil dihapus.');
    }
}
