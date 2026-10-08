<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Target kelas hari libur. Tanpa baris = berlaku untuk semua kelas. */
final class LiburTargetKelasRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return int[] */
    public function untuk(int $idLibur): array
    {
        $st = $this->db->prepare('SELECT id_kelas FROM libur_target_kelas WHERE id_libur = ? ORDER BY id_kelas');
        $st->execute([$idLibur]);
        return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @param int[] $idLibur @return array<int, int[]> id_libur => daftar id_kelas */
    public function untukBanyak(array $idLibur): array
    {
        if (!$idLibur) {
            return [];
        }
        $in = implode(',', array_fill(0, count($idLibur), '?'));
        $st = $this->db->prepare("SELECT id_libur, id_kelas FROM libur_target_kelas WHERE id_libur IN ($in) ORDER BY id_kelas");
        $st->execute(array_values($idLibur));
        $hasil = [];
        foreach ($st->fetchAll() as $r) {
            $hasil[(int) $r['id_libur']][] = (int) $r['id_kelas'];
        }
        return $hasil;
    }

    /** Ganti seluruh target. Kelas yang tidak ada ditolak database (1452 => 422). */
    public function ganti(int $idLibur, array $idKelas): void
    {
        $del = $this->db->prepare('DELETE FROM libur_target_kelas WHERE id_libur = ?');
        $del->execute([$idLibur]);

        $ins = $this->db->prepare('INSERT INTO libur_target_kelas (id_libur, id_kelas) VALUES (?, ?)');
        foreach (array_unique($idKelas) as $k) {
            $ins->execute([$idLibur, $k]);
        }
    }
}
