<?php
declare(strict_types=1);

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/** Sheet "Rekap Absensi": satu baris per siswa; kelas ditulis di kolom (satu sheet untuk semua kelas). */
final class AbsensiExporter
{
    public const JUDUL = ['Tahun Ajaran', 'Semester', 'Kelas', 'No. Absen', 'NISN', 'Nama Siswa', 'Periode', 'Hadir', 'Sakit', 'Izin', 'Alpa', 'Total'];

    public static function tambah(Spreadsheet $wb, array $laporan): void
    {
        $meta = $laporan['meta'];
        $s = $wb->createSheet();
        $s->setTitle('Rekap Absensi');
        SheetHelper::header($s, 1, self::JUDUL);

        $baris = 1;
        $periode = SheetHelper::periode($meta);
        foreach ($laporan['kelas'] as $k) {
            foreach ($k['absensi'] as $r) {
                $baris++;
                SheetHelper::teks($s, 1, $baris, $meta['tahun_ajaran']);
                SheetHelper::teks($s, 2, $baris, ucfirst($meta['semester']));
                SheetHelper::teks($s, 3, $baris, $k['nama_kelas']);
                SheetHelper::angka($s, 4, $baris, $r['nomor_absen']);
                SheetHelper::teks($s, 5, $baris, $r['nisn']);
                SheetHelper::teks($s, 6, $baris, $r['nama_siswa']);
                SheetHelper::teks($s, 7, $baris, $periode);
                SheetHelper::angka($s, 8, $baris, $r['hadir']);
                SheetHelper::angka($s, 9, $baris, $r['sakit']);
                SheetHelper::angka($s, 10, $baris, $r['izin']);
                SheetHelper::angka($s, 11, $baris, $r['alpa']);
                SheetHelper::angka($s, 12, $baris, $r['total']);
            }
        }
        SheetHelper::bingkai($s, count(self::JUDUL), 2, $baris);
        SheetHelper::lebarOtomatis($s, count(self::JUDUL));
    }
}
