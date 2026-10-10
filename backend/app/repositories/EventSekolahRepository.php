<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\SqlHelper;
use PDO;

final class EventSekolahRepository
{
    private const SELECT = 'SELECT id_event, nama_event, kategori, tanggal, deskripsi, banner, status_tampil FROM event_sekolah';
    private const URUT   = ' ORDER BY (tanggal IS NULL), tanggal DESC, id_event DESC';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Admin: semua event, termasuk yang disembunyikan. $kategori opsional. */
    public function daftar(?string $kategori, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($kategori, false);
        return $this->ambil($where, $params, $limit, $offset);
    }

    public function total(?string $kategori): int
    {
        [$where, $params] = $this->filter($kategori, false);
        return $this->hitung($where, $params);
    }

    /** Publik: hanya status_tampil = 1 (whitelist field). */
    public function daftarPublik(?string $kategori, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($kategori, true);
        $st = $this->db->prepare(
            'SELECT id_event, nama_event, kategori, tanggal, deskripsi, banner FROM event_sekolah' . $where . self::URUT . ' LIMIT ? OFFSET ?'
        );
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(static function (array $r): array {
            $r['id_event'] = (int) $r['id_event'];
            return $r;
        }, $st->fetchAll());
    }

    public function totalPublik(?string $kategori): int
    {
        [$where, $params] = $this->filter($kategori, true);
        return $this->hitung($where, $params);
    }

    public function cari(int $id): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE id_event = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    public function buat(array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO event_sekolah (nama_event, kategori, tanggal, deskripsi, banner, status_tampil) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $d['nama_event'], $d['kategori'], $d['tanggal'] ?? null, $d['deskripsi'] ?? null,
            $d['banner'] ?? null, (int) ($d['status_tampil'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        if (array_key_exists('status_tampil', $changes)) {
            $changes['status_tampil'] = (int) $changes['status_tampil'];
        }
        SqlHelper::update($this->db, 'event_sekolah', 'id_event', $id, $changes,
            ['nama_event', 'kategori', 'tanggal', 'deskripsi', 'banner', 'status_tampil']);
    }

    /** Galeri milik event ikut terhapus (CASCADE; data turunan milik event). */
    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM event_sekolah WHERE id_event = ?');
        $st->execute([$id]);
    }

    private function ambil(string $where, array $params, int $limit, int $offset): array
    {
        $st = $this->db->prepare(self::SELECT . $where . self::URUT . ' LIMIT ? OFFSET ?');
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    private function hitung(string $where, array $params): int
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM event_sekolah' . $where);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    private function filter(?string $kategori, bool $hanyaTampil): array
    {
        $w = [];
        $p = [];
        if ($hanyaTampil) {
            $w[] = 'status_tampil = 1';
        }
        if ($kategori !== null) {
            $w[] = 'kategori = ?';
            $p[] = $kategori;
        }
        return [$w ? ' WHERE ' . implode(' AND ', $w) : '', $p];
    }

    private function rapikan(array $r): array
    {
        $r['id_event']      = (int) $r['id_event'];
        $r['status_tampil'] = (bool) $r['status_tampil'];
        return $r;
    }
}
