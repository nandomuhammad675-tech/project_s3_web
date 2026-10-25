<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Query bahan dan penulisan untuk proses kenaikan kelas. Dipanggil di dalam satu transaksi oleh KenaikanKelasService. */
final class KenaikanKelasRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Kelas pada suatu tahun ajaran beserta wali dan status akun walinya. */
    public function kelasTahun(int $idTahun): array
    {
        $st = $this->db->prepare(
            'SELECT k.id_kelas, k.nama_kelas, k.tingkat_kelas, k.id_guru_wali, g.nama_guru AS nama_guru_wali, a.status_akun AS status_akun_wali
             FROM kelas k
             LEFT JOIN data_guru g ON g.id_guru = k.id_guru_wali
             LEFT JOIN akun_pengguna a ON a.id_user = g.id_user
             WHERE k.id_tahun_ajaran = ? ORDER BY k.tingkat_kelas, k.nama_kelas'
        );
        $st->execute([$idTahun]);
        return array_map(static function (array $r): array {
            $r['id_kelas']      = (int) $r['id_kelas'];
            $r['tingkat_kelas'] = (int) $r['tingkat_kelas'];
            $r['id_guru_wali']  = $r['id_guru_wali'] === null ? null : (int) $r['id_guru_wali'];
            return $r;
        }, $st->fetchAll());
    }

    /** Siswa berstatus aktif yang punya penempatan pada tahun asal, beserta kelas dan tingkatnya. */
    public function siswaAktifPada(int $idTahunAsal): array
    {
        $st = $this->db->prepare(
            "SELECT s.id_siswa, s.nama_siswa, r.id_kelas, k.tingkat_kelas
             FROM riwayat_kelas r
             JOIN data_siswa s ON s.id_siswa = r.id_siswa AND s.status_siswa = 'aktif'
             JOIN kelas k ON k.id_kelas = r.id_kelas
             WHERE r.id_tahun_ajaran = ? ORDER BY k.tingkat_kelas, s.nama_siswa, s.id_siswa"
        );
        $st->execute([$idTahunAsal]);
        return array_map(static function (array $r): array {
            $r['id_siswa']      = (int) $r['id_siswa'];
            $r['id_kelas']      = (int) $r['id_kelas'];
            $r['tingkat_kelas'] = (int) $r['tingkat_kelas'];
            return $r;
        }, $st->fetchAll());
    }

    /** Siswa aktif TANPA penempatan pada tahun asal: memblokir seluruh proses (FR-KELAS-04). Siswa baru tidak dihitung. */
    public function siswaAktifTanpaPenempatan(int $idTahunAsal): array
    {
        $st = $this->db->prepare(
            "SELECT s.id_siswa, s.nama_siswa FROM data_siswa s
             WHERE s.status_siswa = 'aktif'
               AND NOT EXISTS (SELECT 1 FROM riwayat_kelas r WHERE r.id_siswa = s.id_siswa AND r.id_tahun_ajaran = ?)
             ORDER BY s.nama_siswa"
        );
        $st->execute([$idTahunAsal]);
        return array_map(static function (array $r): array {
            $r['id_siswa'] = (int) $r['id_siswa'];
            return $r;
        }, $st->fetchAll());
    }

    /** @param int[] $ids @return array<int, array{id_siswa:int, nama_siswa:string, status_siswa:string}> dikunci id_siswa */
    public function siswaByIds(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $st = $this->db->prepare("SELECT id_siswa, nama_siswa, status_siswa FROM data_siswa WHERE id_siswa IN ($in)");
        $st->execute(array_values($ids));
        $hasil = [];
        foreach ($st->fetchAll() as $r) {
            $r['id_siswa'] = (int) $r['id_siswa'];
            $hasil[$r['id_siswa']] = $r;
        }
        return $hasil;
    }

    public function hitungSiswaBaru(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM data_siswa WHERE status_siswa = 'baru'")->fetchColumn();
    }

    /** Penempatan baru tanpa nomor absen (NULL); Admin menetapkannya kemudian lewat PUT /api/admin/riwayat-kelas. */
    public function buatRiwayat(int $idSiswa, int $idKelas, int $idTahun): void
    {
        $st = $this->db->prepare('INSERT INTO riwayat_kelas (id_siswa, id_kelas, id_tahun_ajaran, nomor_absen) VALUES (?, ?, ?, NULL)');
        $st->execute([$idSiswa, $idKelas, $idTahun]);
    }

    public function setStatus(int $idSiswa, string $status): void
    {
        $st = $this->db->prepare('UPDATE data_siswa SET status_siswa = ? WHERE id_siswa = ?');
        $st->execute([$status, $idSiswa]);
    }
}
