<?php
declare(strict_types=1);

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/** Sheet "Rekap Nilai": satu baris per siswa dan mata pelajaran; kolom UH-1..UH-n mengikuti nomor UH tertinggi. */
final class NilaiExporter
{
    public static function tambah(Spreadsheet $wb, array $laporan): void
    {
        $meta   = $laporan['meta'];
        $maksUh = max(1, (int) $meta['maks_uh']);

        $judul = ['Tahun Ajaran', 'Semester', 'Kelas', 'No. Absen', 'NISN', 'Nama Siswa', 'Mata Pelajaran'];
        for ($i = 1; $i <= $maksUh; $i++) {
            $judul[] = 'UH-' . $i;
        }
        $judul[] = 'ASTS';
        $judul[] = 'ASAS';
        $kolomAsts = 7 + $maksUh + 1;

        $s = $wb->createSheet();
        $s->setTitle('Rekap Nilai');
        SheetHelper::header($s, 1, $judul);

        $baris = 1;
        foreach ($laporan['kelas'] as $k) {
            foreach ($k['nilai'] as $r) {
                $baris++;
                SheetHelper::teks($s, 1, $baris, $meta['tahun_ajaran']);
                SheetHelper::teks($s, 2, $baris, ucfirst($meta['semester']));
                SheetHelper::teks($s, 3, $baris, $k['nama_kelas']);
                SheetHelper::angka($s, 4, $baris, $r['nomor_absen']);
                SheetHelper::teks($s, 5, $baris, $r['nisn']);
                SheetHelper::teks($s, 6, $baris, $r['nama_siswa']);
                SheetHelper::teks($s, 7, $baris, $r['nama_mapel']);
                for ($i = 1; $i <= $maksUh; $i++) {
                    SheetHelper::angka($s, 7 + $i, $baris, $r['uh'][$i] ?? null);
                }
                SheetHelper::angka($s, $kolomAsts, $baris, $r['asts']);
                SheetHelper::angka($s, $kolomAsts + 1, $baris, $r['asas']);
            }
        }
        SheetHelper::bingkai($s, count($judul), 2, $baris);
        SheetHelper::lebarOtomatis($s, count($judul));
    }
}
