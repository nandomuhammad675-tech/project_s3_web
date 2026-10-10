<?php
declare(strict_types=1);

/**
 * Menghapus sisa DATA UJI otomatis (hanya untuk pengembangan). Dipanggil otomatis oleh tests/smoke_langkah4b.sh,
 * dan boleh dijalankan manual:   php database/seeds/bersihkan_data_uji.php
 *
 * Absensi, nilai ASTS/ASAS, dan catatan kedisiplinan memang tidak dapat dihapus lewat API, jadi uji otomatis
 * memberinya penanda yang dikenali skrip ini:
 *   - absensi dan jurnal bertanggal >= 2035-01-01
 *   - mata pelajaran berkode NUJI% beserta nilainya
 *   - catatan kedisiplinan berjenis UJI-%
 * Data lain (data_uji.php, data sekolah sungguhan) tidak disentuh.
 */
if (PHP_SAPI !== 'cli') {
    exit('Hanya bisa dijalankan dari terminal.');
}

$root = dirname(__DIR__, 2) . '/backend';
require $root . '/vendor/autoload.php';
\Dotenv\Dotenv::createImmutable($root)->safeLoad();

use App\Core\Config;
use App\Core\Database;

if (Config::env('APP_ENV', 'local') !== 'local') {
    exit("Ditolak: skrip ini hanya untuk APP_ENV=local.\n");
}

$pdo = Database::connection();
$pdo->beginTransaction();
try {
    $n = [];
    $n['absensi']       = $pdo->exec("DELETE FROM absensi_siswa WHERE tanggal_absen >= '2035-01-01'");
    $n['jurnal']        = $pdo->exec("DELETE FROM jurnal_absen_kelas WHERE tanggal >= '2035-01-01'");
    $n['nilai']         = $pdo->exec("DELETE FROM data_nilai WHERE id_mapel IN (SELECT id_mapel FROM mata_pelajaran WHERE kode_mapel LIKE 'NUJI%')");
    $n['mata_pelajaran'] = $pdo->exec("DELETE FROM mata_pelajaran WHERE kode_mapel LIKE 'NUJI%'");
    $n['kedisiplinan']  = $pdo->exec("DELETE FROM catatan_kedisiplinan WHERE jenis_pelanggaran LIKE 'UJI-%'");
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'GAGAL: ' . $e->getMessage() . "\n");
    exit(1);
}

echo 'Data uji dibersihkan: ' . implode(', ', array_map(static fn($k, $v) => "$k=$v", array_keys($n), $n)) . "\n";
