<?php
declare(strict_types=1);

namespace App\Controllers\Wali;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\WaliService;

final class AbsensiController
{
    /** GET /api/wali/absensi?semester=ganjil|genap&bulan=YYYY-MM (keduanya opsional): rekap absensi siswa pada token. */
    public function index(Request $req): void
    {
        $semester = $req->query('semester');
        if ($semester !== null && $semester !== '' && !in_array($semester, ['ganjil', 'genap'], true)) {
            throw HttpException::badRequest('Parameter semester harus ganjil atau genap.');
        }
        $bulan = $req->query('bulan');
        if ($bulan !== null && $bulan !== '' && (!is_string($bulan) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan))) {
            throw HttpException::badRequest('Parameter bulan harus berformat YYYY-MM.');
        }
        $wali = $req->get('wali');
        ResponseHelper::success('OK', (new WaliService())->absensi(
            (int) $wali['id_siswa'],
            ($semester === null || $semester === '') ? null : (string) $semester,
            ($bulan === null || $bulan === '') ? null : (string) $bulan
        ));
    }
}
