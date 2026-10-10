<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\SqlHelper;
use PDO;

final class EkstrakurikulerRepository
{
    private const SELECT = 'SELECT id_ekskul, nama_ekskul, deskripsi, ikon FROM ekstrakurikuler';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(int $limit, int $offset): array
    {
        $st = $this->db->prepare(self::SELECT . ' ORDER BY nama_ekskul, id_ekskul LIMIT ? OFFSET ?');
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->bindValue(2, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM ekstrakurikuler')->fetchColumn();
    }

    /** Daftar lengkap tanpa paginasi untuk website publik (jumlah ekskul kecil). */
    public function semua(): array
    {
        return array_map([$this, 'rapikan'], $this->db->query(self::SELECT . ' ORDER BY nama_ekskul, id_ekskul')->fetchAll());
    }

    public function cari(int $id): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE id_ekskul = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    public function buat(array $d): int
    {
        $st = $this->db->prepare('INSERT INTO ekstrakurikuler (nama_ekskul, deskripsi, ikon) VALUES (?, ?, ?)');
        $st->execute([$d['nama_ekskul'], $d['deskripsi'] ?? null, $d['ikon'] ?? null]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        SqlHelper::update($this->db, 'ekstrakurikuler', 'id_ekskul', $id, $changes, ['nama_ekskul', 'deskripsi', 'ikon']);
    }

    /** Relasi guru_ekskul ikut terhapus (CASCADE; data turunan milik ekskul). */
    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM ekstrakurikuler WHERE id_ekskul = ?');
        $st->execute([$id]);
    }

    private function rapikan(array $r): array
    {
        $r['id_ekskul'] = (int) $r['id_ekskul'];
        return $r;
    }
}
