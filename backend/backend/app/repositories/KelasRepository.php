<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\SqlHelper;
use App\Models\Kelas;
use PDO;

final class KelasRepository
{
    private const KOLOM_UBAH = ['tingkat_kelas', 'nama_kelas', 'id_guru_wali'];

    private const SELECT = 'SELECT k.id_kelas, k.id_tahun_ajaran, t.nama_tahun, k.tingkat_kelas, k.nama_kelas, k.id_guru_wali,
                g.nama_guru AS nama_guru_wali,
                (SELECT COUNT(*) FROM riwayat_kelas r WHERE r.id_kelas = k.id_kelas) AS jumlah_siswa
            FROM kelas k
            JOIN tahun_ajaran t ON t.id_tahun_ajaran = k.id_tahun_ajaran
            LEFT JOIN data_guru g ON g.id_guru = k.id_guru_wali';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return array<int, array> */
    public function daftar(?int $idTahun, int $limit, int $offset): array
    {
        $where = $idTahun !== null ? ' WHERE k.id_tahun_ajaran = ?' : '';
        $st = $this->db->prepare(self::SELECT . $where . ' ORDER BY t.nama_tahun DESC, k.tingkat_kelas, k.nama_kelas LIMIT ? OFFSET ?');
        $i = 1;
        if ($idTahun !== null) {
            $st->bindValue($i++, $idTahun, PDO::PARAM_INT);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(static fn(array $r): array => Kelas::dariBaris($r)->toArray(), $st->fetchAll());
    }

    public function total(?int $idTahun): int
    {
        if ($idTahun === null) {
            return (int) $this->db->query('SELECT COUNT(*) FROM kelas')->fetchColumn();
        }
        $st = $this->db->prepare('SELECT COUNT(*) FROM kelas WHERE id_tahun_ajaran = ?');
        $st->execute([$idTahun]);
        return (int) $st->fetchColumn();
    }

    public function cari(int $id): ?Kelas
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE k.id_kelas = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : Kelas::dariBaris($r);
    }

    public function buat(int $idTahun, int $tingkat, string $nama, ?int $idGuruWali): int
    {
        $st = $this->db->prepare('INSERT INTO kelas (id_tahun_ajaran, tingkat_kelas, nama_kelas, id_guru_wali) VALUES (?, ?, ?, ?)');
        $st->execute([$idTahun, $tingkat, $nama, $idGuruWali]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        SqlHelper::update($this->db, 'kelas', 'id_kelas', $id, $changes, self::KOLOM_UBAH);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM kelas WHERE id_kelas = ?');
        $st->execute([$id]);
    }

    /** Jumlah kelas pada tahun ajaran dan tingkat tertentu (aturan rombel), tanpa menghitung kelas $kecuali. */
    public function hitungRombel(int $idTahun, int $tingkat, ?int $kecuali): int
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM kelas WHERE id_tahun_ajaran = ? AND tingkat_kelas = ? AND id_kelas <> ?');
        $st->execute([$idTahun, $tingkat, $kecuali ?? 0]);
        return (int) $st->fetchColumn();
    }

    /** Guru sudah menjadi wali kelas lain pada tahun ajaran ini? */
    public function waliSudahDipakai(int $idTahun, int $idGuru, ?int $kecuali): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM kelas WHERE id_tahun_ajaran = ? AND id_guru_wali = ? AND id_kelas <> ? LIMIT 1');
        $st->execute([$idTahun, $idGuru, $kecuali ?? 0]);
        return $st->fetchColumn() !== false;
    }

    /** Kelas masih menjadi target hari libur? (database tidak menolak karena FK-nya CASCADE) */
    public function adaTargetLibur(int $id): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM libur_target_kelas WHERE id_kelas = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetchColumn() !== false;
    }
}
