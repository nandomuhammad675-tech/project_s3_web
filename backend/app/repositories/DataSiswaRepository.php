<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Siswa;
use PDO;

final class DataSiswaRepository
{
    /**
     * Syarat akses Wali (FR-AUTH-08/14): siswa berstatus aktif DAN punya riwayat_kelas
     * pada tahun ajaran yang sedang aktif.
     */
    private const SYARAT_WALI = "s.status_siswa = 'aktif'
        AND EXISTS (SELECT 1 FROM riwayat_kelas r
                    JOIN tahun_ajaran t ON t.id_tahun_ajaran = r.id_tahun_ajaran
                    WHERE r.id_siswa = s.id_siswa AND t.status_aktif = 1)";

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function cariWaliAktifByNisn(string $nisn): ?Siswa
    {
        $st = $this->db->prepare(
            'SELECT s.id_siswa, s.nama_siswa, s.nisn_resmi, s.status_siswa FROM data_siswa s
             WHERE s.nisn_resmi = ? AND ' . self::SYARAT_WALI . ' LIMIT 1'
        );
        $st->execute([$nisn]);
        return $this->hidrasi($st->fetch());
    }

    public function cariWaliAktifById(int $id): ?Siswa
    {
        $st = $this->db->prepare(
            'SELECT s.id_siswa, s.nama_siswa, s.nisn_resmi, s.status_siswa FROM data_siswa s
             WHERE s.id_siswa = ? AND ' . self::SYARAT_WALI . ' LIMIT 1'
        );
        $st->execute([$id]);
        return $this->hidrasi($st->fetch());
    }

    /** Profil siswa untuk Wali: hanya field yang boleh tampil (tanpa kontak wali). */
    public function profilWali(int $idSiswa): ?array
    {
        $st = $this->db->prepare(
            'SELECT s.nama_siswa, s.nisn_resmi AS nisn,
                    k.nama_kelas, k.tingkat_kelas, r.nomor_absen,
                    t.nama_tahun AS tahun_ajaran, t.semester_aktif AS semester
             FROM data_siswa s
             JOIN riwayat_kelas r ON r.id_siswa = s.id_siswa
             JOIN tahun_ajaran t  ON t.id_tahun_ajaran = r.id_tahun_ajaran AND t.status_aktif = 1
             JOIN kelas k         ON k.id_kelas = r.id_kelas
             WHERE s.id_siswa = ? AND s.status_siswa = \'aktif\' LIMIT 1'
        );
        $st->execute([$idSiswa]);
        $r = $st->fetch();
        return $r === false ? null : $r;
    }

    private function hidrasi(array|false $r): ?Siswa
    {
        return $r === false ? null : new Siswa(
            (int) $r['id_siswa'], (string) $r['nama_siswa'], (string) $r['nisn_resmi'], (string) $r['status_siswa']
        );
    }
}
