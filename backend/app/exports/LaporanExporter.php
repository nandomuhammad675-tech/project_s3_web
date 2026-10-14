<?php
declare(strict_types=1);

namespace App\Exports;

use App\Helpers\LogHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/** Menyusun workbook laporan semester: sheet Informasi (metadata) + Rekap Absensi dan/atau Rekap Nilai. */
final class LaporanExporter
{
    public const MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /** @param 'absensi'|'nilai'|'semua' $jenis */
    public static function buat(array $laporan, string $jenis = 'semua'): Spreadsheet
    {
        if (!class_exists(Spreadsheet::class)) {
            LogHelper::error('phpoffice/phpspreadsheet belum terpasang. Jalankan: composer require phpoffice/phpspreadsheet');
            throw new \RuntimeException('Library Excel belum terpasang.');
        }

        $wb = new Spreadsheet();
        self::informasi($wb, $laporan['meta']);
        if ($jenis === 'absensi' || $jenis === 'semua') {
            AbsensiExporter::tambah($wb, $laporan);
        }
        if ($jenis === 'nilai' || $jenis === 'semua') {
            NilaiExporter::tambah($wb, $laporan);
        }
        $wb->setActiveSheetIndex(0);
        return $wb;
    }

    /** Nama berkas aman untuk header: huruf, angka, minus, garis bawah saja. */
    public static function namaFile(array $meta): string
    {
        $bagian = ['laporan', $meta['semester'], $meta['tahun_ajaran'], $meta['kelas'] === 'Semua kelas' ? null : $meta['kelas']];
        $nama = implode('-', array_filter($bagian, static fn($b) => $b !== null));
        return preg_replace('/[^A-Za-z0-9_-]+/', '-', $nama) . '.xlsx';
    }

    /** Kirim sebagai unduhan. Dipanggil setelah semua validasi selesai (belum ada output). */
    public static function kirim(Spreadsheet $wb, string $namaFile): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code(200);
        header('Content-Type: ' . self::MIME);
        header('Content-Disposition: attachment; filename="' . $namaFile . '"');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        (new Xlsx($wb))->save('php://output');
        $wb->disconnectWorksheets();
    }

    private static function informasi(Spreadsheet $wb, array $meta): void
    {
        $s = $wb->getActiveSheet();
        $s->setTitle('Informasi');
        SheetHelper::teks($s, 1, 1, 'LAPORAN AKADEMIK SEMESTER');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $baris = [
            ['Sekolah', $meta['sekolah']],
            ['Tahun Ajaran', $meta['tahun_ajaran']],
            ['Semester', ucfirst($meta['semester'])],
            ['Kelas', $meta['kelas']],
            ['Periode data absensi', SheetHelper::periode($meta)],
            ['Tanggal ekspor', $meta['tanggal_ekspor']],
        ];
        foreach ($baris as $i => [$k, $v]) {
            SheetHelper::teks($s, 1, 3 + $i, $k);
            SheetHelper::teks($s, 2, 3 + $i, $v);
            $s->getStyle('A' . (3 + $i))->getFont()->setBold(true);
        }
        SheetHelper::lebarOtomatis($s, 2);
    }
}
