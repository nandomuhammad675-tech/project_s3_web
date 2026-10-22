<?php
declare(strict_types=1);

/**
 * ALAT PENGEMBANG: membalikkan SATU proses kenaikan kelas berdasarkan log_promosi_siswa.
 * Ini prosedur "koreksi hasil kenaikan kelas oleh pengembang melalui basis data" (URS BR-37).
 *
 *   php database/seeds/batalkan_kenaikan_kelas.php <id_tahun_ajaran_tujuan>
 *
 * Yang dilakukan (satu transaksi):
 *   naik  => hapus penempatan di tahun tujuan
 *   lulus => status siswa kembali aktif
 *   masuk => hapus penempatan di tahun tujuan dan status siswa kembali baru
 *   hapus log proses itu; bila tahun tujuan sudah diaktifkan, aktifkan kembali tahun asalnya
 * Ditolak bila di tahun tujuan sudah ada absensi, nilai, atau kedisiplinan (mencegah data sungguhan hilang).
 * Hanya untuk APP_ENV=local. Nomor absen yang sudah diatur di tahun tujuan ikut hilang.
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

$idTujuan = (int) ($argv[1] ?? 0);
if ($idTujuan < 1) {
    fwrite(STDERR, "Pemakaian: php database/seeds/batalkan_kenaikan_kelas.php <id_tahun_ajaran_tujuan>\n");
    exit(1);
}

$pdo = Database::connection();
$pdo->beginTransaction();
try {
    $st = $pdo->prepare('SELECT status_aktif FROM tahun_ajaran WHERE id_tahun_ajaran = ? FOR UPDATE');
    $st->execute([$idTujuan]);
    $tujuanAktif = $st->fetchColumn();
    if ($tujuanAktif === false) {
        throw new RuntimeException('Tahun ajaran tujuan tidak ditemukan.');
    }

    foreach (['absensi_siswa' => 'id_tahun_ajaran', 'data_nilai' => 'id_tahun_ajaran', 'catatan_kedisiplinan' => 'id_tahun_ajaran'] as $tabel => $kolom) {
        $c = $pdo->prepare("SELECT COUNT(*) FROM `$tabel` WHERE `$kolom` = ?");
        $c->execute([$idTujuan]);
        if ((int) $c->fetchColumn() > 0) {
            throw new RuntimeException("Ditolak: tahun tujuan sudah memiliki data di $tabel. Koreksi manual diperlukan.");
        }
    }

    $log = $pdo->prepare('SELECT id_siswa, id_tahun_asal, hasil_proses FROM log_promosi_siswa WHERE id_tahun_tujuan = ?');
    $log->execute([$idTujuan]);
    $baris = $log->fetchAll();
    if (!$baris) {
        throw new RuntimeException('Tidak ada log kenaikan kelas ke tahun ajaran ini.');
    }

    $hapusRiwayat = $pdo->prepare('DELETE FROM riwayat_kelas WHERE id_siswa = ? AND id_tahun_ajaran = ?');
    $ubahStatus   = $pdo->prepare('UPDATE data_siswa SET status_siswa = ? WHERE id_siswa = ?');
    $idAsal = null;
    $n = ['naik' => 0, 'lulus' => 0, 'masuk' => 0];
    foreach ($baris as $b) {
        $idAsal = $idAsal ?? ($b['id_tahun_asal'] !== null ? (int) $b['id_tahun_asal'] : null);
        switch ($b['hasil_proses']) {
            case 'naik':
                $hapusRiwayat->execute([$b['id_siswa'], $idTujuan]);
                break;
            case 'lulus':
                $ubahStatus->execute(['aktif', $b['id_siswa']]);
                break;
            case 'masuk':
                $hapusRiwayat->execute([$b['id_siswa'], $idTujuan]);
                $ubahStatus->execute(['baru', $b['id_siswa']]);
                break;
        }
        $n[$b['hasil_proses']]++;
    }

    $pdo->prepare('DELETE FROM log_promosi_siswa WHERE id_tahun_tujuan = ?')->execute([$idTujuan]);

    $aktifkanKembali = false;
    if ((int) $tujuanAktif === 1 && $idAsal !== null) {
        $pdo->prepare('UPDATE tahun_ajaran SET status_aktif = 0 WHERE id_tahun_ajaran = ?')->execute([$idTujuan]);
        $pdo->prepare('UPDATE tahun_ajaran SET status_aktif = 1 WHERE id_tahun_ajaran = ?')->execute([$idAsal]);
        $aktifkanKembali = true;
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'GAGAL: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "Kenaikan kelas ke tahun ajaran #$idTujuan dibatalkan: naik={$n['naik']}, lulus={$n['lulus']}, masuk={$n['masuk']}.\n";
echo $aktifkanKembali ? "Tahun ajaran asal (#$idAsal) diaktifkan kembali.\n" : "Status aktif tahun ajaran tidak diubah.\n";
