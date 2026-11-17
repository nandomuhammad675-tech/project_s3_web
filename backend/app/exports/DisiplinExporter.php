<?php
declare(strict_types=1);

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/** Kedisiplinan: SATU SHEET PER KELAS ("DIS-<KELAS>"); kelas tidak pernah digabung. */
final class DisiplinExporter
{
    /** @param array<int, array<string, mixed>> $rows tanggal, nisn, nama_siswa, nama_kelas, jenis_pelanggaran, catatan, nama_guru */
    public static function tambahDetail(Spreadsheet $wb, array $meta, array $rows): void
    {
        $judul = ['Tanggal', 'NISN', 'Nama Siswa', 'Jenis Pelanggaran', 'Catatan', 'Dicatat oleh'];
        foreach (AbsensiExporter::perKelas($rows) as $nama => $list) {
            $s = $wb->createSheet();
            $s->setTitle(SheetHelper::namaSheet($wb, 'DIS-' . $nama));
            SheetHelper::judulSheet($s, 'CATATAN KEDISIPLINAN KELAS ' . $nama, $meta['keterangan']);
            SheetHelper::header($s, 4, $judul);
            $baris = 4;
            foreach ($list as $r) {
                $baris++;
                SheetHelper::teks($s, 1, $baris, SheetHelper::tanggal($r['tanggal']));
                SheetHelper::teks($s, 2, $baris, (string) $r['nisn']);
                SheetHelper::teks($s, 3, $baris, (string) $r['nama_siswa']);
                SheetHelper::teks($s, 4, $baris, (string) $r['jenis_pelanggaran']);
                SheetHelper::teks($s, 5, $baris, (string) $r['catatan']);
                SheetHelper::teks($s, 6, $baris, (string) ($r['nama_guru'] ?? ''));
            }
            SheetHelper::bingkai($s, count($judul), 5, $baris);
            SheetHelper::lebarOtomatis($s, count($judul));
            $s->getColumnDimension('E')->setAutoSize(false)->setWidth(60);
            $s->getStyle('E5:E' . $baris)->getAlignment()->setWrapText(true);
        }
    }
}
