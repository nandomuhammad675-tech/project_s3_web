<?php
declare(strict_types=1);

/**
 * Uji skema database si_kesa_v9.sql (Test Plan 2.2 "Database Schema Testing"; TDD bab 14 baris Database).
 * Membuktikan CHECK, UNIQUE (termasuk kolom stored aktif_flag dan nomor_uh_kunci), FOREIGN KEY RESTRICT/CASCADE/SET NULL
 * benar-benar bekerja pada DBMS yang dipakai.
 *
 *   php tests/Database/uji_skema.php       (jalankan dari folder backend; hanya APP_ENV=local)
 *
 * Seluruh baris uji dibuat di dalam SATU transaksi lalu di-ROLLBACK di akhir, sehingga data tidak tertinggal.
 */
if (PHP_SAPI !== 'cli') {
    exit('Hanya bisa dijalankan dari terminal.');
}

$root = dirname(__DIR__, 2); // tests/Database -> backend
require $root . '/vendor/autoload.php';
\Dotenv\Dotenv::createImmutable($root)->safeLoad();

use App\Core\Config;
use App\Core\Database;

if (Config::env('APP_ENV', 'local') !== 'local') {
    exit("Ditolak: skrip ini hanya untuk APP_ENV=local.\n");
}

const KODE_CHECK = [3819, 4025];   // MySQL 3819, MariaDB 4025
const KODE_DUP   = [1062];
const KODE_FK_RESTRICT = [1451];
const KODE_FK_ANAK_HILANG = [1452];
const KODE_ENUM  = [1265, 1366];

$pdo = Database::connection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$gagal = 0;
$total = 0;

function q(PDO $pdo, string $sql, array $p = []): PDOStatement
{
    $st = $pdo->prepare($sql);
    $st->execute($p);
    return $st;
}
function baruId(PDO $pdo, string $sql, array $p = []): int
{
    q($pdo, $sql, $p);
    return (int) $pdo->lastInsertId();
}
function skalar(PDO $pdo, string $sql, array $p = []): mixed
{
    return q($pdo, $sql, $p)->fetchColumn();
}
/** Pernyataan harus DITOLAK database dengan salah satu kode MySQL yang diharapkan. */
function ditolak(string $nama, callable $fn, array $kode): void
{
    global $gagal, $total;
    $total++;
    try {
        $fn();
        echo "  GAGAL $nama (tidak ditolak database)\n";
        $gagal++;
    } catch (PDOException $e) {
        $k = (int) ($e->errorInfo[1] ?? 0);
        if (in_array($k, $kode, true)) {
            echo "  OK    $nama (kode $k)\n";
        } else {
            echo "  GAGAL $nama (harapan " . implode('/', $kode) . ", dapat $k: " . $e->getMessage() . ")\n";
            $gagal++;
        }
    }
}
/** Pernyataan harus BERHASIL (kontrol positif: membuktikan uji penolakan di atasnya tidak palsu). */
function berhasil(string $nama, callable $fn): void
{
    global $gagal, $total;
    $total++;
    try {
        $fn();
        echo "  OK    $nama\n";
    } catch (PDOException $e) {
        echo "  GAGAL $nama (seharusnya berhasil: " . $e->getMessage() . ")\n";
        $gagal++;
    }
}
function sama(string $nama, mixed $aktual, mixed $harapan): void
{
    global $gagal, $total;
    $total++;
    // null/bool dibandingkan ketat; angka dibandingkan sebagai teks. false (tidak ada baris) tidak pernah dianggap 0.
    if (is_bool($harapan) || $harapan === null) {
        $ok = $aktual === $harapan;
    } else {
        $ok = $aktual !== false && $aktual !== null && (string) $aktual === (string) $harapan;
    }
    if ($ok) {
        echo "  OK    $nama\n";
    } else {
        echo "  GAGAL $nama (harapan " . var_export($harapan, true) . ", dapat " . var_export($aktual, true) . ")\n";
        $gagal++;
    }
}

$versi = (string) skalar($pdo, 'SELECT VERSION()');
echo "DBMS: $versi\n";
if (stripos($versi, 'mariadb') !== false) {
    echo "CATATAN: ini MariaDB. Dokumen menargetkan MySQL 8.0.16+; hasil di bawah menentukan apakah MariaDB layak dipakai.\n";
}
$mode = (string) skalar($pdo, 'SELECT @@sql_mode');
echo "sql_mode: $mode\n";
if (stripos($mode, 'STRICT') === false) {
    echo "CATATAN: sql_mode TIDAK ketat (bawaan my.ini XAMPP). Nilai tidak valid bisa diterima diam-diam; aktifkan STRICT_TRANS_TABLES di my.ini atau pada koneksi.\n";
}

$pdo->beginTransaction();
try {
    echo "== Fixture (di dalam transaksi, di-rollback di akhir)\n";
    $uA = baruId($pdo, "INSERT INTO akun_pengguna (username, kata_sandi_hash, role) VALUES ('zz_uji_a', 'x', 'guru')");
    $uB = baruId($pdo, "INSERT INTO akun_pengguna (username, kata_sandi_hash, role) VALUES ('zz_uji_b', 'x', 'guru')");
    $gA = baruId($pdo, 'INSERT INTO data_guru (id_user, nama_guru) VALUES (?, ?)', [$uA, 'Guru ZZ A']);
    $gB = baruId($pdo, 'INSERT INTO data_guru (id_user, nama_guru) VALUES (?, ?)', [$uB, 'Guru ZZ B']);
    $t1 = baruId($pdo, "INSERT INTO tahun_ajaran (nama_tahun, semester_aktif, status_aktif) VALUES ('2098/2099', 'ganjil', 0)");
    $t2 = baruId($pdo, "INSERT INTO tahun_ajaran (nama_tahun, semester_aktif, status_aktif) VALUES ('2097/2098', 'ganjil', 0)");
    $k1 = baruId($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas, id_guru_wali) VALUES (?, 1, 'ZZ1', ?)", [$t1, $gA]);
    $k2 = baruId($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas, id_guru_wali) VALUES (?, 2, 'ZZ2', ?)", [$t1, $gB]);
    $s1 = baruId($pdo, "INSERT INTO data_siswa (nama_siswa, nisn_resmi) VALUES ('Siswa ZZ 1', '9999999991')");
    $s2 = baruId($pdo, "INSERT INTO data_siswa (nama_siswa, nisn_resmi) VALUES ('Siswa ZZ 2', '9999999992')");
    $s3 = baruId($pdo, "INSERT INTO data_siswa (nama_siswa, nisn_resmi) VALUES ('Siswa ZZ 3', '9999999993')");
    $m1 = baruId($pdo, "INSERT INTO mata_pelajaran (kode_mapel, nama_mapel) VALUES ('ZZ', 'Mapel ZZ')");
    $r1 = baruId($pdo, 'INSERT INTO riwayat_kelas (id_siswa, id_kelas, id_tahun_ajaran, nomor_absen) VALUES (?, ?, ?, 1)', [$s1, $k1, $t1]);
    echo "  fixture dibuat\n";

    echo "== Nilai bawaan dan kolom stored\n";
    sama('status_siswa bawaan = baru', skalar($pdo, 'SELECT status_siswa FROM data_siswa WHERE id_siswa = ?', [$s1]), 'baru');
    sama('aktif_flag NULL untuk tahun tidak aktif', skalar($pdo, 'SELECT aktif_flag FROM tahun_ajaran WHERE id_tahun_ajaran = ?', [$t1]), null);
    sama('sql_mode ketat (STRICT)', stripos($mode, 'STRICT') !== false, true);

    echo "== CHECK\n";
    ditolak('kelas tingkat 7', fn () => q($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas) VALUES (?, 7, 'ZZ7')", [$t1]), KODE_CHECK);
    ditolak('kelas tingkat 0', fn () => q($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas) VALUES (?, 0, 'ZZ0')", [$t1]), KODE_CHECK);
    $nilaiSql = 'INSERT INTO data_nilai (id_siswa, id_kelas, id_mapel, id_tahun_ajaran, semester, id_guru, jenis_penilaian, nomor_uh, nilai_angka) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $nilai = fn (string $sem, string $jenis, ?int $nomor, int $angka) => q($pdo, $nilaiSql, [$s1, $k1, $m1, $t1, $sem, $gA, $jenis, $nomor, $angka]);
    ditolak('nilai 101', fn () => $nilai('ganjil', 'UH', 1, 101), KODE_CHECK);
    ditolak('UH tanpa nomor_uh', fn () => $nilai('ganjil', 'UH', null, 80), KODE_CHECK);
    ditolak('UH nomor_uh = 0', fn () => $nilai('ganjil', 'UH', 0, 80), KODE_CHECK);
    ditolak('ASTS dengan nomor_uh', fn () => $nilai('ganjil', 'ASTS', 1, 80), KODE_CHECK);
    ditolak('jadwal jam_selesai <= jam_mulai', fn () => q($pdo, "INSERT INTO jadwal_pelajaran (id_kelas, id_tahun_ajaran, semester, id_mapel, hari, jam_mulai, jam_selesai) VALUES (?, ?, 'ganjil', ?, 'senin', '08:00', '07:00')", [$k1, $t1, $m1]), KODE_CHECK);
    $promosi = 'INSERT INTO log_promosi_siswa (id_siswa, id_tahun_asal, id_tahun_tujuan, id_kelas_asal, id_kelas_tujuan, hasil_proses, id_user_proses) VALUES (?, ?, ?, ?, ?, ?, ?)';
    ditolak('promosi naik tanpa kelas tujuan', fn () => q($pdo, $promosi, [$s2, $t1, $t2, $k1, null, 'naik', $uA]), KODE_CHECK);
    ditolak('promosi lulus dengan kelas tujuan', fn () => q($pdo, $promosi, [$s2, $t1, $t2, $k1, $k2, 'lulus', $uA]), KODE_CHECK);
    ditolak('promosi masuk dengan tahun asal terisi', fn () => q($pdo, $promosi, [$s2, $t1, $t2, null, $k2, 'masuk', $uA]), KODE_CHECK);
    ditolak('promosi masuk dengan kelas asal terisi', fn () => q($pdo, $promosi, [$s2, null, $t2, $k1, $k2, 'masuk', $uA]), KODE_CHECK);
    berhasil('kontrol: promosi naik lengkap', fn () => q($pdo, $promosi, [$s2, $t1, $t2, $k1, $k2, 'naik', $uA]));
    berhasil('kontrol: promosi lulus sah', fn () => q($pdo, $promosi, [$s3, $t1, $t2, $k1, null, 'lulus', $uA]));
    berhasil('kontrol: promosi masuk sah', fn () => q($pdo, $promosi, [$s1, null, $t2, null, $k2, 'masuk', $uA]));
    ditolak('status_siswa di luar enum (butuh sql_mode ketat)', fn () => q($pdo, "INSERT INTO data_siswa (nama_siswa, nisn_resmi, status_siswa) VALUES ('Z', '9999999990', 'xyz')"), KODE_ENUM);

    echo "== UNIQUE\n";
    ditolak('username ganda', fn () => q($pdo, "INSERT INTO akun_pengguna (username, kata_sandi_hash, role) VALUES ('zz_uji_a', 'x', 'guru')"), KODE_DUP);
    ditolak('NISN ganda', fn () => q($pdo, "INSERT INTO data_siswa (nama_siswa, nisn_resmi) VALUES ('Z', '9999999991')"), KODE_DUP);
    ditolak('data_guru satu akun dua guru', fn () => q($pdo, 'INSERT INTO data_guru (id_user, nama_guru) VALUES (?, ?)', [$uA, 'Dobel']), KODE_DUP);
    $aktif = (int) skalar($pdo, 'SELECT COUNT(*) FROM tahun_ajaran WHERE status_aktif = 1');
    if ($aktif === 0) {
        berhasil('kontrol: tahun aktif pertama', fn () => q($pdo, "INSERT INTO tahun_ajaran (nama_tahun, semester_aktif, status_aktif) VALUES ('2096/2097', 'ganjil', 1)"));
    }
    ditolak('dua tahun ajaran aktif sekaligus (aktif_flag)', fn () => q($pdo, "INSERT INTO tahun_ajaran (nama_tahun, semester_aktif, status_aktif) VALUES ('2095/2096', 'ganjil', 1)"), KODE_DUP);
    berhasil('kontrol: banyak tahun tidak aktif diizinkan (t1 dan t2 sudah ada)', fn () => q($pdo, "INSERT INTO tahun_ajaran (nama_tahun, semester_aktif, status_aktif) VALUES ('2094/2095', 'ganjil', 0)"));
    ditolak('nama tahun ajaran ganda', fn () => q($pdo, "INSERT INTO tahun_ajaran (nama_tahun, semester_aktif, status_aktif) VALUES ('2098/2099', 'ganjil', 0)"), KODE_DUP);
    ditolak('kelas: nama ganda pada tahun yang sama', fn () => q($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas) VALUES (?, 3, 'ZZ1')", [$t1]), KODE_DUP);
    ditolak('kelas: satu Guru dua kelas pada satu tahun', fn () => q($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas, id_guru_wali) VALUES (?, 3, 'ZZ3', ?)", [$t1, $gA]), KODE_DUP);
    berhasil('kontrol: dua kelas tanpa wali (NULL) diizinkan', function () use ($pdo, $t1) {
        q($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas) VALUES (?, 4, 'ZZ4')", [$t1]);
        q($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas) VALUES (?, 5, 'ZZ5')", [$t1]);
    });
    ditolak('riwayat kelas ganda (siswa, tahun)', fn () => q($pdo, 'INSERT INTO riwayat_kelas (id_siswa, id_kelas, id_tahun_ajaran, nomor_absen) VALUES (?, ?, ?, 9)', [$s1, $k2, $t1]), KODE_DUP);
    ditolak('nomor absen ganda dalam satu kelas', fn () => q($pdo, 'INSERT INTO riwayat_kelas (id_siswa, id_kelas, id_tahun_ajaran, nomor_absen) VALUES (?, ?, ?, 1)', [$s2, $k1, $t1]), KODE_DUP);
    berhasil('kontrol: nomor absen NULL berulang diizinkan', function () use ($pdo, $s2, $s3, $k1, $t1) {
        q($pdo, 'INSERT INTO riwayat_kelas (id_siswa, id_kelas, id_tahun_ajaran, nomor_absen) VALUES (?, ?, ?, NULL)', [$s2, $k1, $t1]);
        q($pdo, 'INSERT INTO riwayat_kelas (id_siswa, id_kelas, id_tahun_ajaran, nomor_absen) VALUES (?, ?, ?, NULL)', [$s3, $k1, $t1]);
    });
    berhasil('kontrol: UH-1, UH-2, ASTS, ASAS pada semester yang sama', function () use ($nilai) {
        $nilai('ganjil', 'UH', 1, 80);
        $nilai('ganjil', 'UH', 2, 90);
        $nilai('ganjil', 'ASTS', null, 85);
        $nilai('ganjil', 'ASAS', null, 88);
    });
    ditolak('UH-1 ganda', fn () => $nilai('ganjil', 'UH', 1, 70), KODE_DUP);
    ditolak('ASTS ganda (nomor_uh_kunci)', fn () => $nilai('ganjil', 'ASTS', null, 70), KODE_DUP);
    ditolak('ASAS ganda (nomor_uh_kunci)', fn () => $nilai('ganjil', 'ASAS', null, 70), KODE_DUP);
    berhasil('kontrol: ASTS semester genap diizinkan', fn () => $nilai('genap', 'ASTS', null, 70));
    sama('nomor_uh_kunci ASTS = 0', skalar($pdo, "SELECT nomor_uh_kunci FROM data_nilai WHERE id_siswa = ? AND jenis_penilaian = 'ASTS' AND semester = 'ganjil'", [$s1]), 0);
    sama('nomor_uh_kunci UH-2 = 2', skalar($pdo, "SELECT nomor_uh_kunci FROM data_nilai WHERE id_siswa = ? AND jenis_penilaian = 'UH' AND nomor_uh = 2", [$s1]), 2);
    $abs = fn (string $tgl) => q($pdo, "INSERT INTO absensi_siswa (id_siswa, id_kelas, id_tahun_ajaran, semester, id_guru, tanggal_absen, status_kehadiran) VALUES (?, ?, ?, 'ganjil', ?, ?, 'hadir')", [$s1, $k1, $t1, $gA, $tgl]);
    berhasil('kontrol: absensi pertama', fn () => $abs('2098-01-05'));
    ditolak('absensi ganda (siswa, tanggal)', fn () => $abs('2098-01-05'), KODE_DUP);
    $jad = fn (string $hari, string $mulai, string $selesai) => q($pdo, "INSERT INTO jadwal_pelajaran (id_kelas, id_tahun_ajaran, semester, id_mapel, hari, jam_mulai, jam_selesai) VALUES (?, ?, 'ganjil', ?, ?, ?, ?)", [$k1, $t1, $m1, $hari, $mulai, $selesai]);
    berhasil('kontrol: jadwal pertama', fn () => $jad('senin', '07:00', '07:35'));
    ditolak('slot jadwal ganda', fn () => $jad('senin', '07:00', '08:00'), KODE_DUP);
    $piket = baruId($pdo, "INSERT INTO jadwal_piket (id_kelas, id_tahun_ajaran, hari, id_guru_penetap) VALUES (?, ?, 'senin', ?)", [$k1, $t1, $gA]);
    ditolak('piket ganda (kelas, tahun, hari)', fn () => q($pdo, "INSERT INTO jadwal_piket (id_kelas, id_tahun_ajaran, hari, id_guru_penetap) VALUES (?, ?, 'senin', ?)", [$k1, $t1, $gA]), KODE_DUP);
    q($pdo, 'INSERT INTO piket_siswa (id_piket, id_siswa) VALUES (?, ?)', [$piket, $s1]);
    ditolak('siswa ganda pada satu piket', fn () => q($pdo, 'INSERT INTO piket_siswa (id_piket, id_siswa) VALUES (?, ?)', [$piket, $s1]), KODE_DUP);
    ditolak('log promosi ganda (siswa, tahun tujuan)', fn () => q($pdo, $promosi, [$s2, $t1, $t2, $k1, $k2, 'naik', $uA]), KODE_DUP);

    echo "== FOREIGN KEY: RESTRICT (data master yang masih terkait tidak boleh dihapus)\n";
    ditolak('hapus siswa yang punya riwayat kelas', fn () => q($pdo, 'DELETE FROM data_siswa WHERE id_siswa = ?', [$s1]), KODE_FK_RESTRICT);
    ditolak('hapus kelas yang punya riwayat kelas', fn () => q($pdo, 'DELETE FROM kelas WHERE id_kelas = ?', [$k1]), KODE_FK_RESTRICT);
    ditolak('hapus mapel yang punya nilai', fn () => q($pdo, 'DELETE FROM mata_pelajaran WHERE id_mapel = ?', [$m1]), KODE_FK_RESTRICT);
    ditolak('hapus tahun ajaran yang punya kelas', fn () => q($pdo, 'DELETE FROM tahun_ajaran WHERE id_tahun_ajaran = ?', [$t1]), KODE_FK_RESTRICT);
    ditolak('hapus akun yang punya data guru', fn () => q($pdo, 'DELETE FROM akun_pengguna WHERE id_user = ?', [$uA]), KODE_FK_RESTRICT);
    ditolak('hapus Guru yang punya absensi', fn () => q($pdo, 'DELETE FROM data_guru WHERE id_guru = ?', [$gA]), KODE_FK_RESTRICT);
    ditolak('hapus akun pemroses kenaikan kelas', fn () => q($pdo, 'DELETE FROM akun_pengguna WHERE id_user = ?', [$uA]), KODE_FK_RESTRICT);
    ditolak('hapus siswa yang masuk piket', fn () => q($pdo, 'DELETE FROM data_siswa WHERE id_siswa = ?', [$s1]), KODE_FK_RESTRICT);
    ditolak('nilai untuk siswa yang tidak ada', fn () => q($pdo, $nilaiSql, [999999999, $k1, $m1, $t1, 'ganjil', $gA, 'ASAS', null, 80]), KODE_FK_ANAK_HILANG);
    ditolak('absensi untuk kelas yang tidak ada', fn () => q($pdo, "INSERT INTO absensi_siswa (id_siswa, id_kelas, id_tahun_ajaran, semester, id_guru, tanggal_absen, status_kehadiran) VALUES (?, 999999, ?, 'ganjil', ?, '2098-02-02', 'hadir')", [$s1, $t1, $gA]), KODE_FK_ANAK_HILANG);

    echo "== FOREIGN KEY: CASCADE dan SET NULL (hanya data turunan milik induknya)\n";
    q($pdo, 'DELETE FROM jadwal_piket WHERE id_piket = ?', [$piket]);
    sama('hapus piket ikut menghapus piket_siswa', skalar($pdo, 'SELECT COUNT(*) FROM piket_siswa WHERE id_piket = ?', [$piket]), 0);
    $ev = baruId($pdo, "INSERT INTO event_sekolah (nama_event, kategori) VALUES ('Event ZZ', 'event')");
    q($pdo, "INSERT INTO galeri_kegiatan (id_event, file_gambar) VALUES (?, 'uploads/zz.jpg')", [$ev]);
    q($pdo, 'DELETE FROM event_sekolah WHERE id_event = ?', [$ev]);
    sama('hapus event ikut menghapus galerinya', skalar($pdo, 'SELECT COUNT(*) FROM galeri_kegiatan WHERE id_event = ?', [$ev]), 0);
    $lib = baruId($pdo, "INSERT INTO hari_libur (tanggal_libur, keterangan) VALUES ('2098-03-03', 'Libur ZZ')");
    q($pdo, 'INSERT INTO libur_target_kelas (id_libur, id_kelas) VALUES (?, ?)', [$lib, $k2]);
    q($pdo, 'DELETE FROM hari_libur WHERE id_libur = ?', [$lib]);
    sama('hapus hari libur ikut menghapus target kelas', skalar($pdo, 'SELECT COUNT(*) FROM libur_target_kelas WHERE id_libur = ?', [$lib]), 0);
    $k9 = baruId($pdo, "INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas) VALUES (?, 6, 'ZZ9')", [$t1]);
    $lib2 = baruId($pdo, "INSERT INTO hari_libur (tanggal_libur, keterangan) VALUES ('2098-04-04', 'Libur ZZ2')");
    q($pdo, 'INSERT INTO libur_target_kelas (id_libur, id_kelas) VALUES (?, ?)', [$lib2, $k9]);
    q($pdo, 'DELETE FROM kelas WHERE id_kelas = ?', [$k9]);
    sama('PERILAKU DIKETAHUI: hapus kelas menghapus targetnya (database tidak menolak; penjaga ada di service, 409)', skalar($pdo, 'SELECT COUNT(*) FROM libur_target_kelas WHERE id_libur = ?', [$lib2]), 0);
    q($pdo, 'DELETE FROM data_guru WHERE id_guru = ?', [$gB]);
    sama('hapus Guru wali: kelas.id_guru_wali menjadi NULL', skalar($pdo, 'SELECT id_guru_wali FROM kelas WHERE id_kelas = ?', [$k2]), null);
} catch (Throwable $e) {
    echo '  GAGAL uji berhenti karena kesalahan tak terduga: ' . $e->getMessage() . "\n";
    $gagal++;
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

$sisa = (int) skalar($pdo, "SELECT COUNT(*) FROM data_siswa WHERE nisn_resmi LIKE '99999999%'");
echo "== Pembersihan\n";
sama('tidak ada data uji tersisa (rollback berhasil)', $sisa, 0);

echo "\nTotal pemeriksaan: $total; gagal: $gagal\n";
echo $gagal === 0 ? "SEMUA UJI SKEMA LOLOS.\n" : "ADA UJI SKEMA YANG GAGAL (lihat baris GAGAL di atas).\n";
exit($gagal === 0 ? 0 : 1);
