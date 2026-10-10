<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\HttpException;
use App\Repositories\KelasRepository;
use App\Repositories\LaporanRepository;
use App\Repositories\TahunAjaranRepository;

/**
 * Laporan semester (rekap absensi + rekap nilai), dibuat saat diminta dari data operasional (tanpa tabel rekap).
 * Dipakai untuk preview JSON dan ekspor Excel; struktur datanya sama.
 */
final class LaporanService
{
    /**
     * Guru: hanya kelas sendiri dan hanya tahun ajaran aktif (selain itu 403). $g = konteks GuruMiddleware.
     * Parameter $p dari AkademikValidator::laporan; 'kelas' dari klien diabaikan untuk Guru.
     */
    public function untukGuru(array $g, array $p): array
    {
        if ($g['id_tahun_ajaran'] === null || (int) $p['id_tahun_ajaran'] !== (int) $g['id_tahun_ajaran']) {
            throw HttpException::forbidden('Guru hanya dapat melihat atau mengekspor laporan tahun ajaran aktif.');
        }
        return $this->susun((int) $g['id_tahun_ajaran'], $p['semester'], (int) $g['id_kelas']);
    }

    /** Admin: seluruh tahun ajaran; kelas opsional (harus milik tahun ajaran tsb). */
    public function untukAdmin(array $p): array
    {
        if ($p['id_kelas'] !== null) {
            $k = (new KelasRepository())->cari($p['id_kelas']);
            if ($k === null || $k->idTahunAjaran !== (int) $p['id_tahun_ajaran']) {
                throw HttpException::unprocessable('Kelas tidak ditemukan pada tahun ajaran tersebut.', ['kelas' => 'Kelas tidak valid untuk tahun ajaran ini.']);
            }
        }
        return $this->susun((int) $p['id_tahun_ajaran'], $p['semester'], $p['id_kelas']);
    }

    private function susun(int $idTahun, string $semester, ?int $idKelas): array
    {
        $tahun = (new TahunAjaranRepository())->cari($idTahun) ?? throw HttpException::notFound('Tahun ajaran tidak ditemukan.');
        $repo  = new LaporanRepository();

        $kelasList = $repo->kelas($idTahun, $idKelas);
        $peta = [];
        foreach ($kelasList as $k) {
            $peta[$k['id_kelas']] = $k + ['absensi' => [], 'nilai' => []];
        }

        $awal = $akhir = null;
        foreach ($repo->rekapAbsensi($idTahun, $semester, $idKelas) as $r) {
            $r['total'] = $r['hadir'] + $r['sakit'] + $r['izin'] + $r['alpa'];
            if ($r['tanggal_awal'] !== null && ($awal === null || $r['tanggal_awal'] < $awal)) {
                $awal = $r['tanggal_awal'];
            }
            if ($r['tanggal_akhir'] !== null && ($akhir === null || $r['tanggal_akhir'] > $akhir)) {
                $akhir = $r['tanggal_akhir'];
            }
            unset($r['tanggal_awal'], $r['tanggal_akhir']);
            if (isset($peta[$r['id_kelas']])) {
                $peta[$r['id_kelas']]['absensi'][] = $r;
            }
        }

        // Nilai: satu baris per siswa+mapel; UH sebagai peta nomor => nilai.
        $maksUh = 0;
        foreach ($repo->nilai($idTahun, $semester, $idKelas) as $r) {
            if (!isset($peta[$r['id_kelas']])) {
                continue;
            }
            $kunci = $r['id_siswa'] . '|' . $r['id_mapel'];
            $baris = &$peta[$r['id_kelas']]['nilai'];
            $baris[$kunci] ??= [
                'nisn' => $r['nisn'], 'nama_siswa' => $r['nama_siswa'], 'nomor_absen' => $r['nomor_absen'],
                'id_mapel' => $r['id_mapel'], 'nama_mapel' => $r['nama_mapel'], 'uh' => [], 'asts' => null, 'asas' => null,
            ];
            if ($r['jenis'] === 'UH') {
                $baris[$kunci]['uh'][$r['nomor_uh']] = $r['nilai'];
                $maksUh = max($maksUh, (int) $r['nomor_uh']);
            } else {
                $baris[$kunci][strtolower($r['jenis'])] = $r['nilai'];
            }
            unset($baris);
        }

        $kelas = array_values(array_map(static function (array $k): array {
            $k['nilai'] = array_values($k['nilai']);
            return $k;
        }, $peta));

        return [
            'meta' => [
                'sekolah'        => (string) Config::env('SCHOOL_NAME', 'SDN Kedungsari 1'),
                'tahun_ajaran'   => $tahun->namaTahun,
                'id_tahun_ajaran' => $tahun->idTahunAjaran,
                'semester'       => $semester,
                'kelas'          => $idKelas === null ? 'Semua kelas' : ($kelasList[0]['nama_kelas'] ?? '-'),
                'periode_absensi' => ['awal' => $awal, 'akhir' => $akhir],
                'maks_uh'        => $maksUh,
                'tanggal_ekspor' => (new \DateTimeImmutable('now'))->format('Y-m-d H:i'),
            ],
            'kelas' => $kelas,
        ];
    }
}
