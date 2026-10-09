<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\AbsensiService;
use App\Validators\AbsensiValidator;

final class AbsensiController
{
    /** GET /api/guru/absensi?date=YYYY-MM-DD (kosong = hari ini) */
    public function index(Request $req): void
    {
        $tanggal = AbsensiValidator::tanggalQuery($req->query('date'));
        ResponseHelper::success('OK', (new AbsensiService())->lihat($req->get('guru'), $tanggal));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Absensi berhasil disimpan.', (new AbsensiService())->simpan($req->get('guru'), $req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Absensi diperbarui.', (new AbsensiService())->koreksi($req->get('guru'), $req->id(), $req->json()));
    }
}
