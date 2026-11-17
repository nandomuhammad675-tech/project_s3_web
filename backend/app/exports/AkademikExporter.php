<?php
declare(strict_types=1);

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Ekspor Data Akademik Admin (absensi, nilai, kedisiplinan) ke XLSX.
 * Aturan: kelas TIDAK PERNAH digabung dalam satu sheet. Absensi dan kedisiplinan = satu sheet per kelas;
 * nilai = satu sheet per mata pelajaran per kelas (mis. MTK-4A) dengan UH 1..n, ASTS, ASAS sebagai kepala kolom.
 */
final class AkademikExporter
{
    /** @param 'absensi'|'nilai'|'disiplin' $jenis */
    public static function buat(string $jenis, array $info, array $rows): Spreadsheet
    {
        if (!class_exists(Spreadsheet::class)) {
            throw new \RuntimeException('Library Excel belum terpasang.');
        }
        $wb = new Spreadsheet();
        $s  = $wb->getActiveSheet();
        $s->setTitle('Informasi');
        $judul = ['absensi' => 'DATA ABSENSI', 'nilai' => 'DATA NILAI', 'disiplin' => 'DATA KEDISIPLINAN'][$jenis];
        SheetHelper::teks($s, 1, 1, $judul);
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $baris = 3;
        foreach ($info['baris'] as [$k, $v]) {
            SheetHelper::teks($s, 1, $baris, $k);
            SheetHelper::teks($s, 2, $baris, $v);
            $s->getStyle('A' . $baris)->getFont()->setBold(true);
            $baris++;
        }
        SheetHelper::lebarOtomatis($s, 2);

        $meta = ['keterangan' => $info['keterangan']];
        match ($jenis) {
            'absensi'  => AbsensiExporter::tambahDetail($wb, $meta, $rows),
            'nilai'    => NilaiExporter::tambahDariBaris($wb, $meta, $rows),
            'disiplin' => DisiplinExporter::tambahDetail($wb, $meta, $rows),
        };
        $wb->setActiveSheetIndex(0);
        return $wb;
    }
}
