<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\SqlHelper;
use PDO;

/**
 * Jadwal pelajaran dikelola Guru untuk kelasnya sendiri. SEMUA query dibatasi id_kelas dan id_tahun_ajaran
 * milik Guru yang login, jadi id milik kelas lain selalu tidak ditemukan (404).
 */
final class JadwalPelajaranRepository
{
    private const KOLOM_UBAH = ['semester', 'id_mapel', 'hari', 'jam_mulai', 'jam_selesai', 'keterangan'];

    private const SELECT = 'SELECT j.id_jadwal, j.id_kelas, j.id_tahun_ajaran, j.semester, j.id_mapel, m.nama_mapel,
                j.hari, j.jam_mulai, j.jam_selesai, j.keterangan
            FROM jadwal_pelajaran j
            JOIN mata_pelajaran m ON m.id_mapel = j.id_mapel';

    private const URUT_HARI = "FIELD(j.hari, 'senin','selasa','rabu','kamis','jumat','sabtu')";

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(int $idKelas, int $idTahun, ?string $semester, ?string $hari, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($idKelas, $idTahun, $semester, $hari);
        $st = $this->db->prepare(
            self::SELECT . $where . ' ORDER BY j.semester, ' . self::URUT_HARI . ', j.jam_mulai LIMIT ? OFFSET ?'
        );
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function total(int $idKelas, int $idTahun, ?string $semester, ?string $hari): int
    {
        [$where, $params] = $this->filter($idKelas, $idTahun, $semester, $hari);
        $st = $this->db->prepare('SELECT COUNT(*) FROM jadwal_pelajaran j' . $where);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    public function cari(int $id, int $idKelas, int $idTahun): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE j.id_jadwal = ? AND j.id_kelas = ? AND j.id_tahun_ajaran = ?');
        $st->execute([$id, $idKelas, $idTahun]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    public function buat(int $idKelas, int $idTahun, array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO jadwal_pelajaran (id_kelas, id_tahun_ajaran, semester, id_mapel, hari, jam_mulai, jam_selesai, keterangan)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$idKelas, $idTahun, $d['semester'], $d['id_mapel'], $d['hari'], $d['jam_mulai'], $d['jam_selesai'], $d['keterangan'] ?? null]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        SqlHelper::update($this->db, 'jadwal_pelajaran', 'id_jadwal', $id, $changes, self::KOLOM_UBAH);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM jadwal_pelajaran WHERE id_jadwal = ?');
        $st->execute([$id]);
    }

    /** Slot lain pada hari dan semester yang sama yang jamnya beririsan (mulai < selesai lain DAN selesai > mulai lain). */
    public function bentrok(int $idKelas, int $idTahun, string $semester, string $hari, string $mulai, string $selesai, ?int $kecuali): ?array
    {
        $st = $this->db->prepare(
            self::SELECT . ' WHERE j.id_kelas = ? AND j.id_tahun_ajaran = ? AND j.semester = ? AND j.hari = ?
                AND j.jam_mulai < ? AND j.jam_selesai > ? AND j.id_jadwal <> ? LIMIT 1'
        );
        $st->execute([$idKelas, $idTahun, $semester, $hari, $selesai, $mulai, $kecuali ?? 0]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    private function filter(int $idKelas, int $idTahun, ?string $semester, ?string $hari): array
    {
        $w = ['j.id_kelas = ?', 'j.id_tahun_ajaran = ?'];
        $p = [$idKelas, $idTahun];
        if ($semester !== null) {
            $w[] = 'j.semester = ?';
            $p[] = $semester;
        }
        if ($hari !== null) {
            $w[] = 'j.hari = ?';
            $p[] = $hari;
        }
        return [' WHERE ' . implode(' AND ', $w), $p];
    }

    private function rapikan(array $r): array
    {
        foreach (['id_jadwal', 'id_kelas', 'id_tahun_ajaran', 'id_mapel'] as $k) {
            $r[$k] = (int) $r[$k];
        }
        return $r;
    }
}
