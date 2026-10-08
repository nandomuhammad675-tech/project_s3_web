<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Tabel sederhana: cukup array, tanpa model. */
final class MataPelajaranRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(int $limit, int $offset): array
    {
        $st = $this->db->prepare('SELECT id_mapel, kode_mapel, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel LIMIT ? OFFSET ?');
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->bindValue(2, $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM mata_pelajaran')->fetchColumn();
    }

    public function cari(int $id): ?array
    {
        $st = $this->db->prepare('SELECT id_mapel, kode_mapel, nama_mapel FROM mata_pelajaran WHERE id_mapel = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $r;
    }

    public function buat(?string $kode, string $nama): int
    {
        $st = $this->db->prepare('INSERT INTO mata_pelajaran (kode_mapel, nama_mapel) VALUES (?, ?)');
        $st->execute([$kode, $nama]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, ?string $kode, string $nama): void
    {
        $st = $this->db->prepare('UPDATE mata_pelajaran SET kode_mapel = ?, nama_mapel = ? WHERE id_mapel = ?');
        $st->execute([$kode, $nama, $id]);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM mata_pelajaran WHERE id_mapel = ?');
        $st->execute([$id]);
    }
}
