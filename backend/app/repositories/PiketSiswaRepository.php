<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PiketSiswaRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Ganti seluruh siswa bertugas pada satu piket. Kosong = tidak ada siswa. */
    public function ganti(int $idPiket, array $idSiswa): void
    {
        $del = $this->db->prepare('DELETE FROM piket_siswa WHERE id_piket = ?');
        $del->execute([$idPiket]);

        $ins = $this->db->prepare('INSERT INTO piket_siswa (id_piket, id_siswa) VALUES (?, ?)');
        foreach (array_unique($idSiswa) as $s) {
            $ins->execute([$idPiket, $s]);
        }
    }

    /**
     * Siswa bertugas per piket. Hanya siswa berstatus AKTIF yang ditampilkan; baris milik siswa yang kemudian
     * pindah tetap tersimpan di database (FR-SISWA-07).
     * @param int[] $idPiket @return array<int, array<int, array{id_siswa:int, nama_siswa:string}>> id_piket => siswa
     */
    public function untuk(array $idPiket): array
    {
        if (!$idPiket) {
            return [];
        }
        $in = implode(',', array_fill(0, count($idPiket), '?'));
        $st = $this->db->prepare(
            "SELECT ps.id_piket, s.id_siswa, s.nama_siswa
             FROM piket_siswa ps JOIN data_siswa s ON s.id_siswa = ps.id_siswa
             WHERE ps.id_piket IN ($in) AND s.status_siswa = 'aktif'
             ORDER BY s.nama_siswa"
        );
        $st->execute(array_values($idPiket));
        $hasil = [];
        foreach ($st->fetchAll() as $r) {
            $hasil[(int) $r['id_piket']][] = ['id_siswa' => (int) $r['id_siswa'], 'nama_siswa' => (string) $r['nama_siswa']];
        }
        return $hasil;
    }
}
