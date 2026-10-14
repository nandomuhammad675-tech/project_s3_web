<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\DasborAbsensiService;
use App\Validators\AbsensiValidator;

final class DasborController
{
    /** GET /api/admin/dasbor/absensi?date= : status absensi seluruh kelas pada tahun ajaran aktif (read-only). */
    public function absensi(Request $req): void
    {
        $tanggal = AbsensiValidator::tanggalQuery($req->query('date'));
        ResponseHelper::success('OK', (new DasborAbsensiService())->untukAdmin($tanggal));
    }
}
