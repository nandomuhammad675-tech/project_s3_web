<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\TahunAjaran;
use PDO;

final class TahunAjaranRepository
{
    private const KOLOM = 'id_tahun_ajaran, nama_tahun, semester_aktif, status_aktif';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return TahunAjaran[] terbaru lebih dulu */
    public function daftar(): array
    {
        $rows = $this->db->query('SELECT ' . self::KOLOM . ' FROM tahun_ajaran ORDER BY nama_tahun DESC')->fetchAll();
        return array_map([$this, 'hidrasi'], $rows);
    }

    public function cari(int $id): ?TahunAjaran
    {
        $st = $this->db->prepare('SELECT ' . self::KOLOM . ' FROM tahun_ajaran WHERE id_tahun_ajaran = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $this->hidrasi($r);
    }

    public function aktif(): ?TahunAjaran
    {
        $r = $this->db->query('SELECT ' . self::KOLOM . ' FROM tahun_ajaran WHERE status_aktif = 1 LIMIT 1')->fetch();
        return $r === false ? null : $this->hidrasi($r);
    }

    /** Tahun ajaran baru selalu belum aktif dan dimulai dari semester ganjil. */
    public function buat(string $nama): int
    {
        $st = $this->db->prepare("INSERT INTO tahun_ajaran (nama_tahun, semester_aktif, status_aktif) VALUES (?, 'ganjil', 0)");
        $st->execute([$nama]);
        return (int) $this->db->lastInsertId();
    }

    public function ubahNama(int $id, string $nama): void
    {
        $st = $this->db->prepare('UPDATE tahun_ajaran SET nama_tahun = ? WHERE id_tahun_ajaran = ?');
        $st->execute([$nama, $id]);
    }

    public function ubahSemester(int $id, string $semester): void
    {
        $st = $this->db->prepare('UPDATE tahun_ajaran SET semester_aktif = ? WHERE id_tahun_ajaran = ?');
        $st->execute([$semester, $id]);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM tahun_ajaran WHERE id_tahun_ajaran = ?');
        $st->execute([$id]);
    }

    /** Sudah ada log kenaikan kelas dengan tahun ini sebagai tujuan? */
    public function adaLogPromosiKeTahun(int $id): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM log_promosi_siswa WHERE id_tahun_tujuan = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetchColumn() !== false;
    }

    public function nonaktifkanSemua(): void
    {
        $this->db->exec('UPDATE tahun_ajaran SET status_aktif = 0 WHERE status_aktif = 1');
    }

    public function aktifkan(int $id): void
    {
        $st = $this->db->prepare('UPDATE tahun_ajaran SET status_aktif = 1 WHERE id_tahun_ajaran = ?');
        $st->execute([$id]);
    }

    private function hidrasi(array $r): TahunAjaran
    {
        return new TahunAjaran((int) $r['id_tahun_ajaran'], (string) $r['nama_tahun'], (string) $r['semester_aktif'], (bool) $r['status_aktif']);
    }
}
