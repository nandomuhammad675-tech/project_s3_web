<?php
declare(strict_types=1);

namespace App\Controllers\Guru;

use App\Core\Request;
use App\Exports\LaporanExporter;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Repositories\MataPelajaranRepository;
use App\Repositories\RiwayatKelasRepository;
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
     * `mapel` (id mata pelajaran) dan `siswa` (id siswa) opsional: mempersempit rekap nilai.
     * Rekap nilai memuat SEMUA siswa aktif x SEMUA mapel; yang belum ada nilainya dibiarkan kosong.
     */
    public function excel(Request $req): void
    {
        $p = AkademikValidator::laporan($req);
        $laporan = (new LaporanService())->untukGuru($req->get('guru'), $p);

        if ($p['jenis'] === 'nilai' || $p['jenis'] === 'semua') {
            $laporan = self::lengkapiNilai(
                $laporan,
                (int) $p['id_tahun_ajaran'],
                Pagination::angka($req, 'mapel'),
                Pagination::angka($req, 'siswa')
            );
        }

        LaporanExporter::kirim(LaporanExporter::buat($laporan, $p['jenis']), LaporanExporter::namaFile($laporan['meta']));
    }

    /**
     * Rekap nilai yang lengkap: tiap siswa aktif kelas x tiap mata pelajaran, urut nomor absen lalu nama mapel.
     * Kombinasi tanpa nilai tetap ditulis (sel nilai kosong), supaya laporan utuh.
     */
    private static function lengkapiNilai(array $laporan, int $idTahun, ?int $idMapel, ?int $idSiswa): array
    {
        $mapel = array_map(
            static fn(array $m): array => ['id_mapel' => (int) $m['id_mapel'], 'nama_mapel' => (string) $m['nama_mapel']],
            (new MataPelajaranRepository())->daftar(1000, 0)
        );
        if ($idMapel !== null) {
            $mapel = array_values(array_filter($mapel, static fn(array $m): bool => $m['id_mapel'] === $idMapel));
        }

        $repoSiswa = new RiwayatKelasRepository();
        foreach ($laporan['kelas'] as &$k) {
            $ada = [];
            foreach ($k['nilai'] as $r) {
                $ada[$r['id_siswa'] . '|' . $r['id_mapel']] = $r;
            }

            $baris = [];
            foreach ($repoSiswa->siswaAktifKelas((int) $k['id_kelas'], $idTahun) as $s) {
                if ($idSiswa !== null && $s['id_siswa'] !== $idSiswa) {
                    continue;
                }
                foreach ($mapel as $m) {
                    $baris[] = $ada[$s['id_siswa'] . '|' . $m['id_mapel']] ?? [
                        'id_siswa'    => $s['id_siswa'],
                        'nisn'        => $s['nisn_resmi'],
                        'nama_siswa'  => $s['nama_siswa'],
                        'nomor_absen' => $s['nomor_absen'],
                        'id_mapel'    => $m['id_mapel'],
                        'nama_mapel'  => $m['nama_mapel'],
                        'uh'          => [],
                        'asts'        => null,
                        'asas'        => null,
                    ];
                }
            }
            $k['nilai'] = $baris;
        }
        unset($k);

        return $laporan;
    }
}