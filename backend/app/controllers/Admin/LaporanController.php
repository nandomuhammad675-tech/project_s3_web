<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Exports\LaporanExporter;
use App\Helpers\ResponseHelper;
use App\Services\LaporanService;
use App\Validators\AkademikValidator;

final class LaporanController
{
    /** GET /api/admin/laporan?semester=&tahun_ajaran=&kelas= : preview JSON (read-only). */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new LaporanService())->untukAdmin(AkademikValidator::laporan($req)));
    }

    /** GET /api/admin/laporan/excel?semester=&tahun_ajaran=&kelas=&jenis=absensi|nilai|semua : unduh XLSX. */
    public function excel(Request $req): void
    {
        $p = AkademikValidator::laporan($req);
        $laporan = (new LaporanService())->untukAdmin($p);
        LaporanExporter::kirim(LaporanExporter::buat($laporan, $p['jenis']), LaporanExporter::namaFile($laporan['meta']));
    }
}
