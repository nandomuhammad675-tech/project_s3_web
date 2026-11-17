<?php
declare(strict_types=1);

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Absensi: SATU SHEET PER KELAS (kelas tidak pernah digabung).
 *  - tambah():       rekap semester (Hadir/Sakit/Izin/Alpa per siswa), dipakai Laporan Excel.
 *  - tambahDetail(): daftar catatan harian, dipakai Data Akademik.
 */
final class AbsensiExporter
{
    public const JUDUL = ['No', 'NISN', 'Nama Siswa', 'Hadir', 'Sakit', 'Izin', 'Alpa', 'Total'];

    public static function tambah(Spreadsheet $wb, array $laporan): void
    {
        $meta = $laporan['meta'];
        foreach ($laporan['kelas'] as $k) {
            if (!$k['absensi']) {
                continue;
            }
            $s = $wb->createSheet();
            $s->setTitle(SheetHelper::namaSheet($wb, 'ABS-' . $k['nama_kelas']));
            SheetHelper::judulSheet(
                $s,
                'REKAP ABSENSI KELAS ' . $k['nama_kelas'],
                'Tahun Ajaran ' . $meta['tahun_ajaran'] . ' | Semester ' . ucfirst($meta['semester']) . ' | Periode ' . SheetHelper::periode($meta)
            );
            SheetHelper::header($s, 4, self::JUDUL);
            $baris = 4;
            foreach ($k['absensi'] as $r) {
                $baris++;
                SheetHelper::angka($s, 1, $baris, $r['nomor_absen'] === null ? null : (int) $r['nomor_absen']);
                SheetHelper::teks($s, 2, $baris, (string) $r['nisn']);
                SheetHelper::teks($s, 3, $baris, (string) $r['nama_siswa']);
                SheetHelper::angka($s, 4, $baris, (int) $r['hadir']);
                SheetHelper::angka($s, 5, $baris, (int) $r['sakit']);
                SheetHelper::angka($s, 6, $baris, (int) $r['izin']);
                SheetHelper::angka($s, 7, $baris, (int) $r['alpa']);
                SheetHelper::angka($s, 8, $baris, (int) $r['total']);
            }
            SheetHelper::bingkai($s, count(self::JUDUL), 5, $baris);
            SheetHelper::lebarOtomatis($s, count(self::JUDUL));
        }
    }

    /** @param array<int, array<string, mixed>> $rows baris absensi harian (tanggal, nisn, nama_siswa, nama_kelas, status, semester) */
    public static function tambahDetail(Spreadsheet $wb, array $meta, array $rows): void
    {
        $judul = ['Tanggal', 'NISN', 'Nama Siswa', 'Status', 'Semester', 'Dicatat oleh'];
        foreach (self::perKelas($rows) as $nama => $list) {
            $s = $wb->createSheet();
            $s->setTitle(SheetHelper::namaSheet($wb, 'ABS-' . $nama));
            SheetHelper::judulSheet($s, 'DATA ABSENSI KELAS ' . $nama, $meta['keterangan']);
            SheetHelper::header($s, 4, $judul);
            $baris = 4;
            foreach ($list as $r) {
                $baris++;
                SheetHelper::teks($s, 1, $baris, SheetHelper::tanggal($r['tanggal']));
                SheetHelper::teks($s, 2, $baris, (string) $r['nisn']);
                SheetHelper::teks($s, 3, $baris, (string) $r['nama_siswa']);
                SheetHelper::teks($s, 4, $baris, ucfirst((string) $r['status']));
                SheetHelper::teks($s, 5, $baris, ucfirst((string) $r['semester']));
                SheetHelper::teks($s, 6, $baris, (string) ($r['nama_guru'] ?? ''));
            }
            SheetHelper::bingkai($s, count($judul), 5, $baris);
            SheetHelper::lebarOtomatis($s, count($judul));
        }
    }

    /** @return array<string, array<int, array<string, mixed>>> nama kelas => baris (urutan kelas mengikuti data) */
    public static function perKelas(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r['nama_kelas']][] = $r;
        }
        ksort($out, SORT_NATURAL);
        return $out;
    }
}
