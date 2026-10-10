<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Exports\LaporanExporter;
use App\Helpers\ResponseHelper;
use App\Services\LaporanService;
use App\Validators\AkademikValidator;

final class LaporanController
{
    /** GET /api/guru/laporan?semester=&tahun_ajaran= : preview kelas sendiri; tahun ajaran selain aktif => 403. */
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new LaporanService())->untukGuru($req->get('guru'), AkademikValidator::laporan($req)));
    }

    /** GET /api/guru/laporan/excel?semester=&tahun_ajaran=&jenis= : unduh XLSX kelas sendiri. */
    public function excel(Request $req): void
    {
        $p = AkademikValidator::laporan($req);
        $laporan = (new LaporanService())->untukGuru($req->get('guru'), $p);
        LaporanExporter::kirim(LaporanExporter::buat($laporan, $p['jenis']), LaporanExporter::namaFile($laporan['meta']));
    }
}
