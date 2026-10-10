<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Repositories\AbsensiSiswaRepository;
use App\Repositories\DataNilaiRepository;
use App\Repositories\TahunAjaranRepository;

/**
 * Data akademik untuk Wali Murid (read-only). Scope SELALU id_siswa dari token (WaliMiddleware)
 * dan tahun ajaran aktif; tidak ada id dari klien. Kontak wali dan data siswa lain tidak pernah dikembalikan.
 */
final class WaliService
{
    /**
     * Nilai dikelompokkan per mata pelajaran dan semester: UH (daftar bernomor), ASTS, ASAS.
     * @return array{tahun_ajaran:string, semester_aktif:string, items:array}
     */
    public function nilai(int $idSiswa, ?string $semester): array
    {
        $tahun = $this->tahunAktif();
        $baris = (new DataNilaiRepository())->untukSiswa($idSiswa, $tahun->idTahunAjaran, $semester);

        $peta = [];
        foreach ($baris as $r) {
            $k = $r['id_mapel'] . '|' . $r['semester'];
            $peta[$k] ??= [
                'id_mapel' => $r['id_mapel'], 'nama_mapel' => $r['nama_mapel'], 'semester' => $r['semester'],
                'uh' => [], 'asts' => null, 'asas' => null,
            ];
            if ($r['jenis'] === 'UH') {
                $peta[$k]['uh'][] = ['nomor_uh' => $r['nomor_uh'], 'nilai' => $r['nilai']];
            } else {
                $peta[$k][strtolower($r['jenis'])] = $r['nilai'];
            }
        }

        return ['tahun_ajaran' => $tahun->namaTahun, 'semester_aktif' => $tahun->semesterAktif, 'items' => array_values($peta)];
    }

    /**
     * Rekap absensi harian + daftar per tanggal. Filter opsional: semester dan/atau bulan (YYYY-MM).
     * @return array{tahun_ajaran:string, semester_aktif:string, rekap:array, items:array}
     */
    public function absensi(int $idSiswa, ?string $semester, ?string $bulan): array
    {
        $tahun = $this->tahunAktif();
        $items = (new AbsensiSiswaRepository())->untukSiswa($idSiswa, $tahun->idTahunAjaran, $semester, $bulan);

        $rekap = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'total' => count($items)];
        foreach ($items as $r) {
            $rekap[$r['status']]++;
        }

        return ['tahun_ajaran' => $tahun->namaTahun, 'semester_aktif' => $tahun->semesterAktif, 'rekap' => $rekap, 'items' => $items];
    }

    private function tahunAktif(): \App\Models\TahunAjaran
    {
        // WaliMiddleware sudah menjamin siswa punya penempatan di tahun aktif; ini hanya pengaman.
        return (new TahunAjaranRepository())->aktif() ?? throw HttpException::unauthorized(AuthService::PESAN_WALI_GAGAL);
    }
}
