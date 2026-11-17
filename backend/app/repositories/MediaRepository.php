<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Media (gambar) disimpan di DATABASE (tabel media_file) sebagai salinan utama.
 * Kolom gambar di tabel lain berisi path "assets/img/<jenis>/<nama>-<n>.<ext>" (sama dengan lokasi berkas di
 * folder website public/). Tabel dibuat otomatis bila belum ada (skema v10 juga memuatnya).
 */
final class MediaRepository
{
    private PDO $db;
    private static bool $siap = false;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
        if (!self::$siap) {
            $this->db->exec(
                'CREATE TABLE IF NOT EXISTS media_file (
                    id_media int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    path varchar(190) NOT NULL,
                    jenis varchar(30) NOT NULL,
                    mime varchar(30) NOT NULL,
                    ukuran int(10) UNSIGNED NOT NULL,
                    data mediumblob NOT NULL,
                    dibuat_pada timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (id_media),
                    UNIQUE KEY uq_media_path (path)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
            );
            self::$siap = true;
        }
    }

    public function ada(string $path): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM media_file WHERE path = ? LIMIT 1');
        $st->execute([$path]);
        return (bool) $st->fetchColumn();
    }

    public function simpan(string $path, string $jenis, string $mime, string $data): int
    {
        $st = $this->db->prepare('INSERT INTO media_file (path, jenis, mime, ukuran, data) VALUES (?, ?, ?, ?, ?)');
        $st->bindValue(1, $path);
        $st->bindValue(2, $jenis);
        $st->bindValue(3, $mime);
        $st->bindValue(4, strlen($data), PDO::PARAM_INT);
        $st->bindValue(5, $data, PDO::PARAM_LOB);
        $st->execute();
        return (int) $this->db->lastInsertId();
    }

    /** @return array{mime:string, ukuran:int, data:string}|null */
    public function baca(string $path): ?array
    {
        $st = $this->db->prepare('SELECT mime, ukuran, data FROM media_file WHERE path = ? LIMIT 1');
        $st->execute([$path]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function hapus(string $path): void
    {
        $this->db->prepare('DELETE FROM media_file WHERE path = ?')->execute([$path]);
    }
}
