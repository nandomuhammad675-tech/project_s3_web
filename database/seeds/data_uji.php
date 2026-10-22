<?php
declare(strict_types=1);

/**
 * DATA UJI UNTUK PENGEMBANGAN (bukan data produksi).
 * Jalankan dari folder si-kesa:   php database/seeds/data_uji.php
 * Aman dijalankan berulang (data yang sudah ada dilewati).
 * Membuat: tahun ajaran 2026/2027 (aktif), akun admin_uji + guru_uji, kelas 1A,
 * dan 3 siswa (aktif/baru/lulus) untuk menguji login Wali.
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

function cari(PDO $pdo, string $sql, array $p): ?int
{
    $st = $pdo->prepare($sql);
    $st->execute($p);
    $v = $st->fetchColumn();
    return $v === false ? null : (int) $v;
}

$pdo = Database::connection();
$pdo->beginTransaction();
try {
    // 1. tahun ajaran aktif
    $idTahun = cari($pdo, 'SELECT id_tahun_ajaran FROM tahun_ajaran WHERE nama_tahun = ?', ['2026/2027']);
    if ($idTahun === null) {
        $pdo->prepare("INSERT INTO tahun_ajaran (nama_tahun, semester_aktif, status_aktif) VALUES ('2026/2027', 'ganjil', 1)")->execute();
        $idTahun = (int) $pdo->lastInsertId();
    }

    // 2. akun
    $akun = [['admin_uji', 'admin', 'admin12345'], ['guru_uji', 'guru', 'guru12345']];
    $idUser = [];
    foreach ($akun as [$u, $role, $pw]) {
        $id = cari($pdo, 'SELECT id_user FROM akun_pengguna WHERE username = ?', [$u]);
        if ($id === null) {
            $pdo->prepare('INSERT INTO akun_pengguna (username, kata_sandi_hash, role) VALUES (?, ?, ?)')
                ->execute([$u, password_hash($pw, PASSWORD_DEFAULT), $role]);
            $id = (int) $pdo->lastInsertId();
        }
        $idUser[$u] = $id;
    }

    // 3. data guru
    $idGuru = cari($pdo, 'SELECT id_guru FROM data_guru WHERE id_user = ?', [$idUser['guru_uji']]);
    if ($idGuru === null) {
        $pdo->prepare("INSERT INTO data_guru (id_user, nama_guru, jabatan) VALUES (?, 'Guru Uji', 'Guru Kelas')")
            ->execute([$idUser['guru_uji']]);
        $idGuru = (int) $pdo->lastInsertId();
    }

    // 4. kelas 1A
    $idKelas = cari($pdo, "SELECT id_kelas FROM kelas WHERE id_tahun_ajaran = ? AND nama_kelas = '1A'", [$idTahun]);
    if ($idKelas === null) {
        $pdo->prepare("INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas, id_guru_wali) VALUES (?, 1, '1A', ?)")
            ->execute([$idTahun, $idGuru]);
        $idKelas = (int) $pdo->lastInsertId();
    }

    // 5. siswa
    $siswa = [['Siswa Uji Aktif', '0000000001', 'aktif'], ['Siswa Uji Baru', '0000000002', 'baru'], ['Siswa Uji Lulus', '0000000003', 'lulus']];
    $idSiswa = [];
    foreach ($siswa as [$nama, $nisn, $status]) {
        $id = cari($pdo, 'SELECT id_siswa FROM data_siswa WHERE nisn_resmi = ?', [$nisn]);
        if ($id === null) {
            $pdo->prepare('INSERT INTO data_siswa (nama_siswa, nisn_resmi, status_siswa) VALUES (?, ?, ?)')
                ->execute([$nama, $nisn, $status]);
            $id = (int) $pdo->lastInsertId();
        }
        $idSiswa[$nisn] = $id;
    }

    // 6. penempatan siswa aktif
    if (cari($pdo, 'SELECT id_riwayat FROM riwayat_kelas WHERE id_siswa = ? AND id_tahun_ajaran = ?', [$idSiswa['0000000001'], $idTahun]) === null) {
        $pdo->prepare('INSERT INTO riwayat_kelas (id_siswa, id_kelas, id_tahun_ajaran, nomor_absen) VALUES (?, ?, ?, 1)')
            ->execute([$idSiswa['0000000001'], $idKelas, $idTahun]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "GAGAL: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Data uji siap.\n";
echo "  Admin : admin_uji / admin12345\n";
echo "  Guru  : guru_uji  / guru12345\n";
echo "  Wali  : NISN 0000000001 (aktif, bisa login)\n";
echo "          NISN 0000000002 (baru) dan 0000000003 (lulus) harus DITOLAK\n";
echo "JANGAN dipakai di produksi.\n";
