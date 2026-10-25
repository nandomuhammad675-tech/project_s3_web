<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Audit kenaikan kelas (satu baris per siswa per tahun ajaran tujuan). Hanya ditulis oleh proses kenaikan kelas;
 * tidak ada jalur ubah atau hapus lewat API (koreksi oleh pengembang lewat basis data, BR-37).
 * Aturan CHECK di skema: naik = asal dan tujuan terisi; lulus = asal terisi, tujuan NULL; masuk = asal NULL, tujuan terisi.
 */
final class LogPromosiSiswaRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Kunci baris tahun ajaran tujuan: dua proses kenaikan kelas bersamaan akan antre, bukan bertabrakan. Panggil di dalam transaksi. */
    public function kunciTahun(int $idTahunTujuan): void
    {
        $st = $this->db->prepare('SELECT id_tahun_ajaran FROM tahun_ajaran WHERE id_tahun_ajaran = ? FOR UPDATE');
        $st->execute([$idTahunTujuan]);
    }

    public function adaUntukTahun(int $idTahunTujuan): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM log_promosi_siswa WHERE id_tahun_tujuan = ? LIMIT 1');
        $st->execute([$idTahunTujuan]);
        return $st->fetchColumn() !== false;
    }

    public function catat(int $idSiswa, ?int $idTahunAsal, int $idTahunTujuan, ?int $idKelasAsal, ?int $idKelasTujuan, string $hasil, int $idUser): void
    {
        $st = $this->db->prepare(
            'INSERT INTO log_promosi_siswa (id_siswa, id_tahun_asal, id_tahun_tujuan, id_kelas_asal, id_kelas_tujuan, hasil_proses, id_user_proses)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$idSiswa, $idTahunAsal, $idTahunTujuan, $idKelasAsal, $idKelasTujuan, $hasil, $idUser]);
    }

    /** @return array{naik:int, lulus:int, masuk:int} */
    public function ringkasan(int $idTahunTujuan): array
    {
        $hasil = ['naik' => 0, 'lulus' => 0, 'masuk' => 0];
        $st = $this->db->prepare('SELECT hasil_proses, COUNT(*) AS n FROM log_promosi_siswa WHERE id_tahun_tujuan = ? GROUP BY hasil_proses');
        $st->execute([$idTahunTujuan]);
        foreach ($st->fetchAll() as $r) {
            $hasil[(string) $r['hasil_proses']] = (int) $r['n'];
        }
        return $hasil;
    }

    /** Jumlah siswa per kelas tujuan dan hasilnya (lulusan tidak punya kelas tujuan). */
    public function perKelasTujuan(int $idTahunTujuan): array
    {
        $st = $this->db->prepare(
            'SELECT l.id_kelas_tujuan AS id_kelas, k.nama_kelas, k.tingkat_kelas, l.hasil_proses AS hasil, COUNT(*) AS jumlah
             FROM log_promosi_siswa l JOIN kelas k ON k.id_kelas = l.id_kelas_tujuan
             WHERE l.id_tahun_tujuan = ? AND l.id_kelas_tujuan IS NOT NULL
             GROUP BY l.id_kelas_tujuan, k.nama_kelas, k.tingkat_kelas, l.hasil_proses
             ORDER BY k.tingkat_kelas, k.nama_kelas, l.hasil_proses'
        );
        $st->execute([$idTahunTujuan]);
        return array_map(static function (array $r): array {
            $r['id_kelas']      = (int) $r['id_kelas'];
            $r['tingkat_kelas'] = (int) $r['tingkat_kelas'];
            $r['jumlah']        = (int) $r['jumlah'];
            return $r;
        }, $st->fetchAll());
    }

    public function daftar(int $idTahunTujuan, int $limit, int $offset): array
    {
        $st = $this->db->prepare(
            'SELECT l.id_log_promosi, l.id_siswa, s.nama_siswa, l.hasil_proses AS hasil,
                    ka.nama_kelas AS kelas_asal, kt.nama_kelas AS kelas_tujuan, l.diproses_pada, a.username AS diproses_oleh
             FROM log_promosi_siswa l
             JOIN data_siswa s ON s.id_siswa = l.id_siswa
             LEFT JOIN kelas ka ON ka.id_kelas = l.id_kelas_asal
             LEFT JOIN kelas kt ON kt.id_kelas = l.id_kelas_tujuan
             JOIN akun_pengguna a ON a.id_user = l.id_user_proses
             WHERE l.id_tahun_tujuan = ? ORDER BY l.id_log_promosi LIMIT ? OFFSET ?'
        );
        $st->bindValue(1, $idTahunTujuan, PDO::PARAM_INT);
        $st->bindValue(2, $limit, PDO::PARAM_INT);
        $st->bindValue(3, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(static function (array $r): array {
            $r['id_log_promosi'] = (int) $r['id_log_promosi'];
            $r['id_siswa']       = (int) $r['id_siswa'];
            return $r;
        }, $st->fetchAll());
    }

    public function total(int $idTahunTujuan): int
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM log_promosi_siswa WHERE id_tahun_tujuan = ?');
        $st->execute([$idTahunTujuan]);
        return (int) $st->fetchColumn();
    }
}
