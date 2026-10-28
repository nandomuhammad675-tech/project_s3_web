<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Relasi Guru - ekstrakurikuler (FR-CONT-03). */
final class GuruEkskulRepository
{
    private const SELECT = 'SELECT ge.id_guru_ekskul, ge.id_guru, g.nama_guru, ge.id_ekskul, e.nama_ekskul
            FROM guru_ekskul ge
            JOIN data_guru g ON g.id_guru = ge.id_guru
            JOIN ekstrakurikuler e ON e.id_ekskul = ge.id_ekskul';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(?int $idGuru, ?int $idEkskul, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($idGuru, $idEkskul);
        $st = $this->db->prepare(self::SELECT . $where . ' ORDER BY e.nama_ekskul, g.nama_guru LIMIT ? OFFSET ?');
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p, PDO::PARAM_INT);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function total(?int $idGuru, ?int $idEkskul): int
    {
        [$where, $params] = $this->filter($idGuru, $idEkskul);
        $st = $this->db->prepare('SELECT COUNT(*) FROM guru_ekskul ge' . $where);
        foreach ($params as $i => $p) {
            $st->bindValue($i + 1, $p, PDO::PARAM_INT);
        }
        $st->execute();
        return (int) $st->fetchColumn();
    }

    public function cari(int $id): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE ge.id_guru_ekskul = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    /** Duplikat ditolak UNIQUE (1062 => 409); guru/ekskul tidak ada ditolak FK (1452 => 422). */
    public function buat(int $idGuru, int $idEkskul): int
    {
        $st = $this->db->prepare('INSERT INTO guru_ekskul (id_guru, id_ekskul) VALUES (?, ?)');
        $st->execute([$idGuru, $idEkskul]);
        return (int) $this->db->lastInsertId();
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM guru_ekskul WHERE id_guru_ekskul = ?');
        $st->execute([$id]);
    }

    private function filter(?int $idGuru, ?int $idEkskul): array
    {
        $w = [];
        $p = [];
        if ($idGuru !== null) {
            $w[] = 'ge.id_guru = ?';
            $p[] = $idGuru;
        }
        if ($idEkskul !== null) {
            $w[] = 'ge.id_ekskul = ?';
            $p[] = $idEkskul;
        }
        return [$w ? ' WHERE ' . implode(' AND ', $w) : '', $p];
    }

    private function rapikan(array $r): array
    {
        foreach (['id_guru_ekskul', 'id_guru', 'id_ekskul'] as $k) {
            $r[$k] = (int) $r[$k];
        }
        return $r;
    }
}
