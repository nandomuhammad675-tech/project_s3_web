<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Query read-only untuk laporan semester. Tidak memfilter status_siswa: siswa yang kemudian pindah
 * tetap dimuat selama punya penempatan/data pada tahun ajaran dan semester tersebut (FR-LAP-05).
 */
final class LaporanRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Kelas pada tahun ajaran; $idKelas opsional. */
    public function kelas(int $idTahun, ?int $idKelas): array
    {
        $sql = 'SELECT k.id_kelas, k.nama_kelas, k.tingkat_kelas, g.nama_guru AS nama_guru_wali
                FROM kelas k LEFT JOIN data_guru g ON g.id_guru = k.id_guru_wali
                WHERE k.id_tahun_ajaran = ?';
        $p = [$idTahun];
        if ($idKelas !== null) {
            $sql .= ' AND k.id_kelas = ?';
            $p[] = $idKelas;
        }
        $st = $this->db->prepare($sql . ' ORDER BY k.tingkat_kelas, k.nama_kelas, k.id_kelas');
        $st->execute($p);
        return array_map(static function (array $r): array {
            $r['id_kelas']      = (int) $r['id_kelas'];
            $r['tingkat_kelas'] = (int) $r['tingkat_kelas'];
            return $r;
        }, $st->fetchAll());
    }

    /**
     * Satu baris per siswa yang ditempatkan pada kelas tahun tsb, dengan hitungan status kehadiran semester tsb.
     * Siswa tanpa absensi muncul dengan hitungan 0.
     */
    public function rekapAbsensi(int $idTahun, string $semester, ?int $idKelas): array
    {
        $sql = "SELECT r.id_kelas, s.id_siswa, s.nisn_resmi AS nisn, s.nama_siswa, r.nomor_absen,
                    COALESCE(SUM(a.status_kehadiran = 'hadir'), 0) AS hadir,
                    COALESCE(SUM(a.status_kehadiran = 'sakit'), 0) AS sakit,
                    COALESCE(SUM(a.status_kehadiran = 'izin'),  0) AS izin,
                    COALESCE(SUM(a.status_kehadiran = 'alpa'),  0) AS alpa,
                    MIN(a.tanggal_absen) AS tanggal_awal, MAX(a.tanggal_absen) AS tanggal_akhir
                FROM riwayat_kelas r
                JOIN data_siswa s ON s.id_siswa = r.id_siswa
                LEFT JOIN absensi_siswa a ON a.id_siswa = r.id_siswa AND a.id_kelas = r.id_kelas
                    AND a.id_tahun_ajaran = r.id_tahun_ajaran AND a.semester = ?
                WHERE r.id_tahun_ajaran = ?";
        $p = [$semester, $idTahun];
        if ($idKelas !== null) {
            $sql .= ' AND r.id_kelas = ?';
            $p[] = $idKelas;
        }
        $st = $this->db->prepare($sql . ' GROUP BY r.id_kelas, s.id_siswa, s.nisn_resmi, s.nama_siswa, r.nomor_absen
                ORDER BY r.id_kelas, (r.nomor_absen IS NULL), r.nomor_absen, s.nama_siswa, s.id_siswa');
        $st->execute($p);
        return array_map(static function (array $r): array {
            foreach (['id_kelas', 'id_siswa', 'hadir', 'sakit', 'izin', 'alpa'] as $k) {
                $r[$k] = (int) $r[$k];
            }
            $r['nomor_absen'] = $r['nomor_absen'] === null ? null : (int) $r['nomor_absen'];
            return $r;
        }, $st->fetchAll());
    }

    /** Semua baris nilai pada semester dan tahun ajaran (satu baris per penilaian). */
    public function nilai(int $idTahun, string $semester, ?int $idKelas): array
    {
        $sql = "SELECT n.id_kelas, s.id_siswa, s.nisn_resmi AS nisn, s.nama_siswa, r.nomor_absen,
                    m.id_mapel, m.nama_mapel, n.jenis_penilaian AS jenis, n.nomor_uh, n.nilai_angka AS nilai
                FROM data_nilai n
                JOIN data_siswa s ON s.id_siswa = n.id_siswa
                JOIN mata_pelajaran m ON m.id_mapel = n.id_mapel
                LEFT JOIN riwayat_kelas r ON r.id_siswa = n.id_siswa AND r.id_tahun_ajaran = n.id_tahun_ajaran
                WHERE n.id_tahun_ajaran = ? AND n.semester = ?";
        $p = [$idTahun, $semester];
        if ($idKelas !== null) {
            $sql .= ' AND n.id_kelas = ?';
            $p[] = $idKelas;
        }
        $st = $this->db->prepare($sql . " ORDER BY n.id_kelas, (r.nomor_absen IS NULL), r.nomor_absen, s.nama_siswa, s.id_siswa,
                m.nama_mapel, m.id_mapel, FIELD(n.jenis_penilaian, 'UH', 'ASTS', 'ASAS'), n.nomor_uh");
        $st->execute($p);
        return array_map(static function (array $r): array {
            foreach (['id_kelas', 'id_siswa', 'id_mapel', 'nilai'] as $k) {
                $r[$k] = (int) $r[$k];
            }
            $r['nomor_absen'] = $r['nomor_absen'] === null ? null : (int) $r['nomor_absen'];
            $r['nomor_uh']    = $r['nomor_uh'] === null ? null : (int) $r['nomor_uh'];
            return $r;
        }, $st->fetchAll());
    }
}
