<?php
declare(strict_types=1);

namespace App\Controllers\Wali;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\WaliService;

final class NilaiController
{
    /** GET /api/wali/nilai?semester=ganjil|genap (opsional): nilai siswa pada token, tahun ajaran aktif. */
    public function index(Request $req): void
    {
        $semester = $req->query('semester');
        if ($semester !== null && $semester !== '' && !in_array($semester, ['ganjil', 'genap'], true)) {
            throw HttpException::badRequest('Parameter semester harus ganjil atau genap.');
        }
        $wali = $req->get('wali');
        ResponseHelper::success('OK', (new WaliService())->nilai((int) $wali['id_siswa'], ($semester === null || $semester === '') ? null : (string) $semester));
    }
}
