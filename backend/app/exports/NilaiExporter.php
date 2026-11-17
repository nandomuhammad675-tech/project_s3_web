<?php
declare(strict_types=1);

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Nilai: SATU SHEET PER MATA PELAJARAN PER KELAS, nama sheet "<KODE MAPEL>-<KELAS>" (mis. MTK-4A).
 * Kelas dan mapel tidak pernah digabung dalam satu sheet. Jenis ujian menjadi kepala kolom:
 * No | NISN | Nama Siswa | UH 1 | UH 2 | ... | ASTS | ASAS.
 */
final class NilaiExporter
{
    public static function tambah(Spreadsheet $wb, array $laporan): void
    {
        $meta = $laporan['meta'];
        foreach ($laporan['kelas'] as $k) {
            $perMapel = [];
            foreach ($k['nilai'] as $r) {
                $perMapel[(int) $r['id_mapel']][] = $r;
            }
            foreach ($perMapel as $list) {
                self::sheetMapel(
                    $wb,
                    $k['nama_kelas'],
                    (string) $list[0]['nama_mapel'],
                    $list[0]['kode_mapel'] ?? null,
                    $list,
                    'Tahun Ajaran ' . $meta['tahun_ajaran'] . ' | Semester ' . ucfirst($meta['semester'])
                );
            }
        }
    }

    /**
     * Dari baris nilai mentah Data Akademik (satu baris per penilaian) menjadi sheet per kelas+mapel.
     * @param array<int, array<string, mixed>> $rows id_siswa,nisn,nama_siswa,nomor_absen,nama_kelas,id_mapel,nama_mapel,kode_mapel,jenis,nomor_uh,nilai
     */
    public static function tambahDariBaris(Spreadsheet $wb, array $meta, array $rows): void
    {
        $semuaSem = array_values(array_unique(array_map(static fn(array $r): string => (string) ($r['semester'] ?? ''), $rows)));
        $banyakSem = count($semuaSem) > 1;
        $kelompok = [];
        foreach ($rows as $r) {
            $kunciMapel = (int) $r['id_mapel'] . '|' . ($banyakSem ? (string) $r['semester'] : '');
            $g = &$kelompok[(string) $r['nama_kelas']][$kunciMapel];
            $g['semester'] = $banyakSem ? (string) $r['semester'] : null;
            $g['nama_mapel'] = $r['nama_mapel'];
            $g['kode_mapel'] = $r['kode_mapel'] ?? null;
            $kunci = (int) $r['id_siswa'];
            $g['baris'][$kunci] ??= [
                'nisn' => $r['nisn'], 'nama_siswa' => $r['nama_siswa'], 'nomor_absen' => $r['nomor_absen'] ?? null,
                'uh' => [], 'asts' => null, 'asas' => null,
            ];
            if ($r['jenis'] === 'UH') {
                $g['baris'][$kunci]['uh'][(int) $r['nomor_uh']] = (int) $r['nilai'];
            } else {
                $g['baris'][$kunci][strtolower((string) $r['jenis'])] = (int) $r['nilai'];
            }
            unset($g);
        }
        ksort($kelompok, SORT_NATURAL);
        foreach ($kelompok as $namaKelas => $mapel) {
            foreach ($mapel as $g) {
                self::sheetMapel($wb, (string) $namaKelas, (string) $g['nama_mapel'], $g['kode_mapel'], array_values($g['baris']), $meta['keterangan'], $g['semester'] === null ? '' : ($g['semester'] === 'ganjil' ? '-GJL' : '-GNP'));
            }
        }
    }

    /** @param array<int, array<string, mixed>> $list baris siswa: nisn, nama_siswa, nomor_absen, uh[nomor=>nilai], asts, asas */
    private static function sheetMapel(Spreadsheet $wb, string $namaKelas, string $namaMapel, ?string $kode, array $list, string $keterangan, string $akhiran = ''): void
    {
        $maksUh = 1;
        foreach ($list as $r) {
            foreach (array_keys($r['uh']) as $n) {
                $maksUh = max($maksUh, (int) $n);
            }
        }
        $judul = ['No', 'NISN', 'Nama Siswa'];
        for ($i = 1; $i <= $maksUh; $i++) {
            $judul[] = 'UH ' . $i;
        }
        $judul[] = 'ASTS';
        $judul[] = 'ASAS';
        $kolomAsts = 3 + $maksUh + 1;

        $s = $wb->createSheet();
        $s->setTitle(SheetHelper::namaSheet($wb, SheetHelper::kodeMapel($kode, $namaMapel) . '-' . $namaKelas . $akhiran));
        SheetHelper::judulSheet($s, 'NILAI ' . mb_strtoupper($namaMapel) . ' | KELAS ' . $namaKelas . ($akhiran === '' ? '' : ($akhiran === '-GJL' ? ' | SEMESTER GANJIL' : ' | SEMESTER GENAP')), $keterangan);
        SheetHelper::header($s, 4, $judul);

        usort($list, static fn(array $a, array $b): int =>
            [$a['nomor_absen'] === null ? PHP_INT_MAX : (int) $a['nomor_absen'], (string) $a['nama_siswa']]
            <=> [$b['nomor_absen'] === null ? PHP_INT_MAX : (int) $b['nomor_absen'], (string) $b['nama_siswa']]);

        $baris = 4;
        foreach ($list as $r) {
            $baris++;
            SheetHelper::angka($s, 1, $baris, $r['nomor_absen'] === null ? null : (int) $r['nomor_absen']);
            SheetHelper::teks($s, 2, $baris, (string) $r['nisn']);
            SheetHelper::teks($s, 3, $baris, (string) $r['nama_siswa']);
            for ($i = 1; $i <= $maksUh; $i++) {
                SheetHelper::angka($s, 3 + $i, $baris, isset($r['uh'][$i]) ? (int) $r['uh'][$i] : null);
            }
            SheetHelper::angka($s, $kolomAsts, $baris, $r['asts'] === null ? null : (int) $r['asts']);
            SheetHelper::angka($s, $kolomAsts + 1, $baris, $r['asas'] === null ? null : (int) $r['asas']);
        }
        SheetHelper::bingkai($s, count($judul), 5, $baris);
        SheetHelper::lebarOtomatis($s, count($judul));
    }
}
