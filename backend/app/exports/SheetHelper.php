<?php
declare(strict_types=1);

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Penulisan sel yang aman. Semua teks ditulis sebagai STRING eksplisit: nama/mapel yang diawali = + - @
 * tidak pernah dievaluasi sebagai rumus (mencegah formula injection) dan NISN tidak kehilangan nol di depan.
 */
final class SheetHelper
{
    public static function teks(Worksheet $s, int $kolom, int $baris, ?string $nilai): void
    {
        $s->setCellValueExplicit(Coordinate::stringFromColumnIndex($kolom) . $baris, (string) $nilai, DataType::TYPE_STRING);
    }

    /** Angka; null => sel dikosongkan (tidak ditulis). */
    public static function angka(Worksheet $s, int $kolom, int $baris, ?int $nilai): void
    {
        if ($nilai === null) {
            return;
        }
        $s->setCellValueExplicit(Coordinate::stringFromColumnIndex($kolom) . $baris, $nilai, DataType::TYPE_NUMERIC);
    }

    /** Baris judul kolom (tebal, abu-abu, berbingkai), dibekukan agar tetap terlihat saat menggulir. */
    public static function header(Worksheet $s, int $baris, array $judul): void
    {
        foreach ($judul as $i => $j) {
            self::teks($s, $i + 1, $baris, $j);
        }
        $rentang = 'A' . $baris . ':' . Coordinate::stringFromColumnIndex(count($judul)) . $baris;
        $s->getStyle($rentang)->getFont()->setBold(true);
        $s->getStyle($rentang)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E5E7EB');
        $s->getStyle($rentang)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $s->getStyle($rentang)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $s->freezePane('A' . ($baris + 1));
    }

    public static function lebarOtomatis(Worksheet $s, int $jumlahKolom): void
    {
        for ($i = 1; $i <= $jumlahKolom; $i++) {
            $s->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    public static function bingkai(Worksheet $s, int $kolomAkhir, int $barisAwal, int $barisAkhir): void
    {
        if ($barisAkhir < $barisAwal) {
            return;
        }
        $s->getStyle('A' . $barisAwal . ':' . Coordinate::stringFromColumnIndex($kolomAkhir) . $barisAkhir)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    /** Nama sheet Excel yang valid (maks 31 karakter, tanpa []:*?/\\) dan unik dalam workbook. */
    public static function namaSheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $wb, string $dasar): string
    {
        $nama = trim(str_replace(['\\', '/', '?', '*', '[', ']', ':', "'"], '-', $dasar));
        $nama = mb_substr($nama === '' ? 'Sheet' : $nama, 0, 31);
        $asli = $nama;
        $n = 2;
        while ($wb->sheetNameExists($nama)) {
            $akhiran = '-' . $n++;
            $nama = mb_substr($asli, 0, 31 - mb_strlen($akhiran)) . $akhiran;
        }
        return $nama;
    }

    /** Kode mapel untuk nama sheet: kode_mapel bila ada, selain itu singkatan dari nama (mis. "Matematika" => MAT). */
    public static function kodeMapel(?string $kode, string $nama): string
    {
        $kode = trim((string) $kode);
        if ($kode !== '') {
            return strtoupper($kode);
        }
        $kata = preg_split('/\s+/', trim($nama)) ?: [];
        if (count($kata) > 1) {
            $s = '';
            foreach ($kata as $k) {
                $s .= mb_strtoupper(mb_substr($k, 0, 1));
            }
            return mb_substr($s, 0, 5);
        }
        return mb_strtoupper(mb_substr($nama, 0, 3));
    }

    /** Dua baris judul di atas tabel (judul tebal + keterangan). Header tabel ditulis oleh pemanggil di baris 4. */
    public static function judulSheet(Worksheet $s, string $judul, string $keterangan): void
    {
        self::teks($s, 1, 1, $judul);
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        self::teks($s, 1, 2, $keterangan);
        $s->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('555555');
    }

    /** "2035-01-03" => "03/01/2035"; null => "-". */
    public static function tanggal(?string $ymd): string
    {
        return $ymd === null ? '-' : date('d/m/Y', (int) strtotime($ymd));
    }

    public static function periode(array $meta): string
    {
        $p = $meta['periode_absensi'];
        return $p['awal'] === null ? '-' : self::tanggal($p['awal']) . ' - ' . self::tanggal($p['akhir']);
    }
}
