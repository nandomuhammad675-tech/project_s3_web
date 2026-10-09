<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Absensi harian (tanpa mata pelajaran). Semua query dibatasi kelas dan tahun ajaran milik Guru yang login. */
final class AbsensiSiswaRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /**
     * Seluruh siswa AKTIF kelas ini beserta status absensi pada $tanggal (null bila belum diisi).
     * @return array<int, array{id_siswa:int, nama_siswa:string, nomor_absen:?int, id_absensi:?int, status:?string}>
     */
    public function untukTanggal(int $idKelas, int $idTahun, string $tanggal): array
    {
        $st = $this->db->prepare(
            "SELECT s.id_siswa, s.nama_siswa, r.nomor_absen, a.id_absensi, a.status_kehadiran AS status
             FROM riwayat_kelas r
             JOIN data_siswa s ON s.id_siswa = r.id_siswa AND s.status_siswa = 'aktif'
             LEFT JOIN absensi_siswa a ON a.id_siswa = s.id_siswa AND a.tanggal_absen = ? AND a.id_kelas = r.id_kelas
             WHERE r.id_kelas = ? AND r.id_tahun_ajaran = ?
             ORDER BY (r.nomor_absen IS NULL), r.nomor_absen, s.nama_siswa"
        );
        $st->execute([$tanggal, $idKelas, $idTahun]);
        return array_map(static function (array $r): array {
            $r['id_siswa']    = (int) $r['id_siswa'];
            $r['nomor_absen'] = $r['nomor_absen'] === null ? null : (int) $r['nomor_absen'];
            $r['id_absensi']  = $r['id_absensi'] === null ? null : (int) $r['id_absensi'];
            return $r;
        }, $st->fetchAll());
    }

    /** @param int[] $idSiswa @return int[] id siswa yang SUDAH punya absensi pada tanggal ini */
    public function sudahAda(array $idSiswa, string $tanggal): array
    {
        if (!$idSiswa) {
            return [];
        }
        $in = implode(',', array_fill(0, count($idSiswa), '?'));
        $st = $this->db->prepare("SELECT id_siswa FROM absensi_siswa WHERE tanggal_absen = ? AND id_siswa IN ($in)");
        $st->execute(array_merge([$tanggal], array_values($idSiswa)));
        return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @param array<int, array{id_siswa:int, status:string}> $items */
    public function buatBanyak(int $idKelas, int $idTahun, string $semester, int $idGuru, string $tanggal, array $items): void
    {
        $st = $this->db->prepare(
            'INSERT INTO absensi_siswa (id_siswa, id_kelas, id_tahun_ajaran, semester, id_guru, tanggal_absen, status_kehadiran)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($items as $it) {
            $st->execute([$it['id_siswa'], $idKelas, $idTahun, $semester, $idGuru, $tanggal, $it['status']]);
        }
    }

    public function cari(int $id, int $idKelas, int $idTahun): ?array
    {
        $st = $this->db->prepare(
            'SELECT id_absensi, id_siswa, tanggal_absen, status_kehadiran AS status FROM absensi_siswa
             WHERE id_absensi = ? AND id_kelas = ? AND id_tahun_ajaran = ?'
        );
        $st->execute([$id, $idKelas, $idTahun]);
        $r = $st->fetch();
        if ($r === false) {
            return null;
        }
        $r['id_absensi'] = (int) $r['id_absensi'];
        $r['id_siswa']   = (int) $r['id_siswa'];
        return $r;
    }

    public function ubahStatus(int $id, string $status): void
    {
        $st = $this->db->prepare('UPDATE absensi_siswa SET status_kehadiran = ? WHERE id_absensi = ?');
        $st->execute([$status, $id]);
    }
}
