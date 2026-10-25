<?php
declare(strict_types=1);

/**
 * Uji tanpa database: nama siswa/mapel berawalan = + - @ harus tersimpan sebagai TEKS (bukan rumus).
 *   php tests/Laporan/export_test.php      (exit 0 = lolos)
 */
require dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Exports\LaporanExporter;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

$bahaya = ['=HYPERLINK("http://x","klik")', '+1+1', '-2+3', '@SUM(A1)'];

$laporan = [
    'meta' => [
        'sekolah' => 'SDN Kedungsari 1', 'tahun_ajaran' => '2035/2036', 'id_tahun_ajaran' => 1, 'semester' => 'ganjil',
        'kelas' => 'Semua kelas', 'periode_absensi' => ['awal' => '2035-03-06', 'akhir' => '2035-03-07'],
        'maks_uh' => 1, 'tanggal_ekspor' => '2035-03-10 10:00',
    ],
    'kelas' => [[
        'id_kelas' => 1, 'nama_kelas' => '=KELAS', 'tingkat_kelas' => 1, 'nama_guru_wali' => 'Wali',
        'absensi' => array_map(static fn($n) => [
            'id_kelas' => 1, 'id_siswa' => 1, 'nisn' => '0000000001', 'nama_siswa' => $n, 'nomor_absen' => 1,
            'hadir' => 1, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'total' => 1,
        ], $bahaya),
        'nilai' => array_map(static fn($n) => [
            'nisn' => '0000000001', 'nama_siswa' => $n, 'nomor_absen' => 1, 'id_mapel' => 1, 'nama_mapel' => $n,
            'uh' => [1 => 90], 'asts' => 80, 'asas' => null,
        ], $bahaya),
    ]],
];

$wb = LaporanExporter::buat($laporan);
$salah = 0;
foreach ($wb->getAllSheets() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {
        foreach ($row->getCellIterator() as $sel) {
            $v = $sel->getValue();
            if (is_string($v) && $v !== '' && in_array($v[0], ['=', '+', '-', '@'], true) && $sel->getDataType() !== DataType::TYPE_STRING) {
                fwrite(STDERR, "Sel {$sel->getCoordinate()} bertipe {$sel->getDataType()} (harus string): $v\n");
                $salah++;
            }
        }
    }
}
// NISN berawalan nol harus tetap string
$nisn = $wb->getSheetByName('Rekap Absensi')->getCell('E2');
if ($nisn->getDataType() !== DataType::TYPE_STRING || $nisn->getValue() !== '0000000001') {
    fwrite(STDERR, "NISN rusak: " . var_export($nisn->getValue(), true) . "\n");
    $salah++;
}
exit($salah === 0 ? 0 : 1);
