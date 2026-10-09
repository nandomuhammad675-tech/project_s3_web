<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Penanda "kelas X sudah diabsen pada tanggal Y" untuk dasbor. BUKAN sumber data kehadiran siswa. */
final class JurnalAbsenKelasRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function tandaiSudah(int $idKelas, int $idTahun, string $tanggal): void
    {
        $st = $this->db->prepare(
            'INSERT INTO jurnal_absen_kelas (id_kelas, id_tahun_ajaran, tanggal, sudah_absen) VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE sudah_absen = 1'
        );
        $st->execute([$idKelas, $idTahun, $tanggal]);
    }

    public function sudahDiabsen(int $idKelas, int $idTahun, string $tanggal): bool
    {
        $st = $this->db->prepare(
            'SELECT 1 FROM jurnal_absen_kelas WHERE id_kelas = ? AND id_tahun_ajaran = ? AND tanggal = ? AND sudah_absen = 1 LIMIT 1'
        );
        $st->execute([$idKelas, $idTahun, $tanggal]);
        return $st->fetchColumn() !== false;
    }
}
