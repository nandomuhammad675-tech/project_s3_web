<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\SqlHelper;
use PDO;

/** Header piket per kelas/tahun/hari. Query selalu dibatasi kelas dan tahun ajaran milik Guru yang login. */
final class JadwalPiketRepository
{
    private const KOLOM_UBAH = ['hari', 'keterangan', 'id_guru_penetap'];
    private const SELECT = 'SELECT p.id_piket, p.id_kelas, p.id_tahun_ajaran, p.hari, p.keterangan FROM jadwal_piket p';
    private const URUT_HARI = "FIELD(p.hari, 'senin','selasa','rabu','kamis','jumat','sabtu')";

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(int $idKelas, int $idTahun): array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE p.id_kelas = ? AND p.id_tahun_ajaran = ? ORDER BY ' . self::URUT_HARI);
        $st->execute([$idKelas, $idTahun]);
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function cari(int $id, int $idKelas, int $idTahun): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE p.id_piket = ? AND p.id_kelas = ? AND p.id_tahun_ajaran = ?');
        $st->execute([$id, $idKelas, $idTahun]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    public function buat(int $idKelas, int $idTahun, string $hari, ?string $keterangan, int $idGuru): int
    {
        $st = $this->db->prepare('INSERT INTO jadwal_piket (id_kelas, id_tahun_ajaran, hari, keterangan, id_guru_penetap) VALUES (?, ?, ?, ?, ?)');
        $st->execute([$idKelas, $idTahun, $hari, $keterangan, $idGuru]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        SqlHelper::update($this->db, 'jadwal_piket', 'id_piket', $id, $changes, self::KOLOM_UBAH);
    }

    /** Baris piket_siswa ikut terhapus (ON DELETE CASCADE). */
    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM jadwal_piket WHERE id_piket = ?');
        $st->execute([$id]);
    }

    public function adaHari(int $idKelas, int $idTahun, string $hari, ?int $kecuali): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM jadwal_piket WHERE id_kelas = ? AND id_tahun_ajaran = ? AND hari = ? AND id_piket <> ? LIMIT 1');
        $st->execute([$idKelas, $idTahun, $hari, $kecuali ?? 0]);
        return $st->fetchColumn() !== false;
    }

    private function rapikan(array $r): array
    {
        foreach (['id_piket', 'id_kelas', 'id_tahun_ajaran'] as $k) {
            $r[$k] = (int) $r[$k];
        }
        return $r;
    }
}
