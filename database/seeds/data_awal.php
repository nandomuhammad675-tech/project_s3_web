<?php
declare(strict_types=1);

/**
 * DATA AWAL yang dibutuhkan sistem (boleh untuk produksi): enam program pembiasaan (FR-WEB-02).
 * Jalankan dari folder si-kesa:   php database/seeds/data_awal.php
 * Aman dijalankan berulang: program yang sudah ada (per hari) dilewati, tidak ditimpa.
 * Admin mengubah nama/deskripsi/ikon lewat PUT /api/admin/program-pembiasaan/{id}.
 */
if (PHP_SAPI !== 'cli') {
    exit('Hanya bisa dijalankan dari terminal.');
}

$root = dirname(__DIR__, 2) . '/backend';
require $root . '/vendor/autoload.php';
\Dotenv\Dotenv::createImmutable($root)->safeLoad();

use App\Core\Database;

$program = [
    ['senin', 'Senin Disiplin'], ['selasa', 'Selasa Bugar'], ['rabu', 'Rabu Religi'],
    ['kamis', 'Kamis Berbudaya'], ['jumat', 'Jumat Ikhlas'], ['sabtu', 'Sabtu Ceria'],
];

$pdo = Database::connection();
$cek = $pdo->prepare('SELECT 1 FROM program_pembiasaan WHERE hari = ?');
$ins = $pdo->prepare('INSERT INTO program_pembiasaan (hari, nama_program) VALUES (?, ?)');
$baru = 0;
foreach ($program as [$hari, $nama]) {
    $cek->execute([$hari]);
    if ($cek->fetchColumn() === false) {
        $ins->execute([$hari, $nama]);
        $baru++;
    }
}
echo "Program pembiasaan: $baru ditambahkan, " . (count($program) - $baru) . " sudah ada.\n";
