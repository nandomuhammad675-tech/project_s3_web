<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\Pagination;
use App\Helpers\SqlHelper;
use App\Models\Siswa;
use PDO;

final class DataSiswaRepository
{
    private const KOLOM_UBAH = ['nama_siswa', 'nisn_resmi', 'nama_wali', 'no_hp_wali', 'status_siswa'];

    /**
     * Syarat akses Wali (FR-AUTH-08/14): siswa berstatus aktif DAN punya riwayat_kelas
     * pada tahun ajaran yang sedang aktif.
     */
    private const SYARAT_WALI = "s.status_siswa = 'aktif'
        AND EXISTS (SELECT 1 FROM riwayat_kelas r
                    JOIN tahun_ajaran t ON t.id_tahun_ajaran = r.id_tahun_ajaran
                    WHERE r.id_siswa = s.id_siswa AND t.status_aktif = 1)";

    /** Siswa + kelas pada tahun ajaran aktif (bila ada). Urutan kolom dipertahankan: id dulu. */
    private const SELECT_ADMIN = "SELECT s.id_siswa, s.nama_siswa, s.nisn_resmi, s.status_siswa, s.nama_wali, s.no_hp_wali,
                k.id_kelas AS id_kelas_aktif, k.nama_kelas AS kelas_aktif
            FROM data_siswa s
            LEFT JOIN riwayat_kelas r ON r.id_siswa = s.id_siswa
                 AND r.id_tahun_ajaran = (SELECT id_tahun_ajaran FROM tahun_ajaran WHERE status_aktif = 1 LIMIT 1)
            LEFT JOIN kelas k ON k.id_kelas = r.id_kelas";

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    // ------------------------------------------------------------ Wali

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

    // ------------------------------------------------------------ Admin

    /** Siswa apa pun statusnya, sebagai model (untuk aturan penempatan). */
    public function cariById(int $id): ?Siswa
    {
        $st = $this->db->prepare('SELECT id_siswa, nama_siswa, nisn_resmi, status_siswa FROM data_siswa WHERE id_siswa = ?');
        $st->execute([$id]);
        return $this->hidrasi($st->fetch());
    }

    /** @return array<int, array> */
    public function daftarAdmin(?string $q, ?string $status, int $limit, int $offset): array
    {
        [$where, $params] = $this->filterAdmin($q, $status);
        $st = $this->db->prepare(self::SELECT_ADMIN . $where . ' ORDER BY s.nama_siswa, s.id_siswa LIMIT ? OFFSET ?');
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function totalAdmin(?string $q, ?string $status): int
    {
        [$where, $params] = $this->filterAdmin($q, $status);
        $st = $this->db->prepare('SELECT COUNT(*) FROM data_siswa s' . $where);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    public function detailAdmin(int $id): ?array
    {
        $st = $this->db->prepare(self::SELECT_ADMIN . ' WHERE s.id_siswa = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        if ($r === false) {
            return null;
        }
        $siswa = $this->rapikan($r);

        $h = $this->db->prepare(
            'SELECT r.id_riwayat, r.id_tahun_ajaran, t.nama_tahun, r.id_kelas, k.nama_kelas, k.tingkat_kelas, r.nomor_absen
             FROM riwayat_kelas r
             JOIN tahun_ajaran t ON t.id_tahun_ajaran = r.id_tahun_ajaran
             JOIN kelas k ON k.id_kelas = r.id_kelas
             WHERE r.id_siswa = ? ORDER BY t.nama_tahun DESC'
        );
        $h->execute([$id]);
        $siswa['riwayat_kelas'] = $h->fetchAll();
        return $siswa;
    }

    /** Siswa baru selalu berstatus baru (default kolom). */
    public function buat(string $nama, string $nisn, ?string $namaWali, ?string $noHpWali): int
    {
        $st = $this->db->prepare('INSERT INTO data_siswa (nama_siswa, nisn_resmi, nama_wali, no_hp_wali) VALUES (?, ?, ?, ?)');
        $st->execute([$nama, $nisn, $namaWali, $noHpWali]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        SqlHelper::update($this->db, 'data_siswa', 'id_siswa', $id, $changes, self::KOLOM_UBAH);
    }

    /** Ditolak database (1451 => 409) bila siswa sudah punya riwayat kelas atau data akademik. */
    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM data_siswa WHERE id_siswa = ?');
        $st->execute([$id]);
    }

    private function filterAdmin(?string $q, ?string $status): array
    {
        $w = [];
        $p = [];
        if ($q !== null && $q !== '') {
            $like = Pagination::like($q);
            $w[] = '(s.nama_siswa LIKE ? OR s.nisn_resmi LIKE ?)';
            $p[] = $like;
            $p[] = $like;
        }
        if ($status !== null) {
            $w[] = 's.status_siswa = ?';
            $p[] = $status;
        }
        return [$w ? ' WHERE ' . implode(' AND ', $w) : '', $p];
    }

    private function rapikan(array $r): array
    {
        $r['id_siswa']        = (int) $r['id_siswa'];
        $r['id_kelas_aktif']  = $r['id_kelas_aktif'] === null ? null : (int) $r['id_kelas_aktif'];
        return $r;
    }

    private function hidrasi(array|false $r): ?Siswa
    {
        return $r === false ? null : new Siswa(
            (int) $r['id_siswa'], (string) $r['nama_siswa'], (string) $r['nisn_resmi'], (string) $r['status_siswa']
        );
    }
}
