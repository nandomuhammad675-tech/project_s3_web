<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\DasborAbsensiService;
use App\Validators\AbsensiValidator;

final class DasborController
{
    /** GET /api/guru/dasbor/absensi?date= : status absensi kelas sendiri (sudah/belum/libur). */
    public function absensi(Request $req): void
    {
        $tanggal = AbsensiValidator::tanggalQuery($req->query('date'));
        ResponseHelper::success('OK', (new DasborAbsensiService())->untukGuru($req->get('guru'), $tanggal));
    }
}
