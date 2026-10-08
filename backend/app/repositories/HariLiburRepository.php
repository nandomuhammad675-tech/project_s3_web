<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class HariLiburRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(?string $dari, ?string $sampai, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($dari, $sampai);
        $st = $this->db->prepare(
            'SELECT id_libur, tanggal_libur, keterangan FROM hari_libur' . $where . ' ORDER BY tanggal_libur, id_libur LIMIT ? OFFSET ?'
        );
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function total(?string $dari, ?string $sampai): int
    {
        [$where, $params] = $this->filter($dari, $sampai);
        $st = $this->db->prepare('SELECT COUNT(*) FROM hari_libur' . $where);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    public function cari(int $id): ?array
    {
        $st = $this->db->prepare('SELECT id_libur, tanggal_libur, keterangan FROM hari_libur WHERE id_libur = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $r;
    }

    public function buat(string $tanggal, ?string $keterangan): int
    {
        $st = $this->db->prepare('INSERT INTO hari_libur (tanggal_libur, keterangan) VALUES (?, ?)');
        $st->execute([$tanggal, $keterangan]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, string $tanggal, ?string $keterangan): void
    {
        $st = $this->db->prepare('UPDATE hari_libur SET tanggal_libur = ?, keterangan = ? WHERE id_libur = ?');
        $st->execute([$tanggal, $keterangan, $id]);
    }

    /** Baris libur_target_kelas ikut terhapus (ON DELETE CASCADE). */
    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM hari_libur WHERE id_libur = ?');
        $st->execute([$id]);
    }

    private function filter(?string $dari, ?string $sampai): array
    {
        $w = [];
        $p = [];
        if ($dari !== null) {
            $w[] = 'tanggal_libur >= ?';
            $p[] = $dari;
        }
        if ($sampai !== null) {
            $w[] = 'tanggal_libur <= ?';
            $p[] = $sampai;
        }
        return [$w ? ' WHERE ' . implode(' AND ', $w) : '', $p];
    }
}
