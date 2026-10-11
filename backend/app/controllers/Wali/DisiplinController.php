<?php
declare(strict_types=1);

namespace App\Controllers\Wali;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\WaliService;

final class DisiplinController
{
    /** GET /api/wali/disiplin?bulan=YYYY-MM (opsional): catatan kedisiplinan siswa pada token, tahun ajaran aktif. */
    public function index(Request $req): void
    {
        $bulan = $req->query('bulan');
        if ($bulan !== null && $bulan !== '' && (!is_string($bulan) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan))) {
            throw HttpException::badRequest('Parameter bulan harus berformat YYYY-MM.');
        }
        $wali = $req->get('wali');
        ResponseHelper::success('OK', (new WaliService())->disiplin(
            (int) $wali['id_siswa'],
            ($bulan === null || $bulan === '') ? null : (string) $bulan
        ));
    }
}
