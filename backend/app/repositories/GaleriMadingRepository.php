<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\SqlHelper;
use PDO;

/** Mading / karya siswa. */
final class GaleriMadingRepository
{
    private const SELECT = 'SELECT id_mading, judul_karya, kategori, deskripsi, nama_penulis, file_gambar, status_tampil FROM galeri_mading';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(int $limit, int $offset): array
    {
        $st = $this->db->prepare(self::SELECT . ' ORDER BY id_mading DESC LIMIT ? OFFSET ?');
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->bindValue(2, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM galeri_mading')->fetchColumn();
    }

    public function daftarPublik(int $limit, int $offset): array
    {
        $st = $this->db->prepare(
            'SELECT id_mading, judul_karya, kategori, deskripsi, nama_penulis, file_gambar FROM galeri_mading
             WHERE status_tampil = 1 ORDER BY id_mading DESC LIMIT ? OFFSET ?'
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->bindValue(2, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(static function (array $r): array {
            $r['id_mading'] = (int) $r['id_mading'];
            return $r;
        }, $st->fetchAll());
    }

    public function totalPublik(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM galeri_mading WHERE status_tampil = 1')->fetchColumn();
    }

    public function cari(int $id): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE id_mading = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    public function buat(array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO galeri_mading (judul_karya, kategori, deskripsi, nama_penulis, file_gambar, status_tampil) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $d['judul_karya'], $d['kategori'] ?? null, $d['deskripsi'] ?? null, $d['nama_penulis'] ?? null,
            $d['file_gambar'], (int) ($d['status_tampil'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        if (array_key_exists('status_tampil', $changes)) {
            $changes['status_tampil'] = (int) $changes['status_tampil'];
        }
        SqlHelper::update($this->db, 'galeri_mading', 'id_mading', $id, $changes,
            ['judul_karya', 'kategori', 'deskripsi', 'nama_penulis', 'file_gambar', 'status_tampil']);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM galeri_mading WHERE id_mading = ?');
        $st->execute([$id]);
    }

    private function rapikan(array $r): array
    {
        $r['id_mading']     = (int) $r['id_mading'];
        $r['status_tampil'] = (bool) $r['status_tampil'];
        return $r;
    }
}
