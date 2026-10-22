<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Exports\LaporanExporter;
use App\Helpers\Pagination;
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

    /**
     * GET /api/guru/laporan/excel?semester=&tahun_ajaran=&jenis=&mapel=&siswa= : unduh XLSX kelas sendiri.
     * `mapel` (id mata pelajaran) dan `siswa` (id siswa) opsional: bila diisi, rekap nilai hanya memuat itu.
     */
    public function excel(Request $req): void
    {
        $p = AkademikValidator::laporan($req);
        $laporan = (new LaporanService())->untukGuru($req->get('guru'), $p);

        $idMapel = Pagination::angka($req, 'mapel');
        $idSiswa = Pagination::angka($req, 'siswa');
        if ($idMapel !== null || $idSiswa !== null) {
            foreach ($laporan['kelas'] as &$k) {
                $k['nilai'] = array_values(array_filter(
                    $k['nilai'],
                    static fn(array $r): bool =>
                        ($idMapel === null || (int) ($r['id_mapel'] ?? 0) === $idMapel)
                        && ($idSiswa === null || (int) ($r['id_siswa'] ?? 0) === $idSiswa)
                ));
            }
            unset($k);
        }

        LaporanExporter::kirim(LaporanExporter::buat($laporan, $p['jenis']), LaporanExporter::namaFile($laporan['meta']));
    }
}