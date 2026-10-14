<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\RiwayatKelas;
use PDO;

final class RiwayatKelasRepository
{
    private const SELECT = 'SELECT r.id_riwayat, r.id_siswa, s.nama_siswa, s.nisn_resmi, r.id_kelas, k.nama_kelas, k.tingkat_kelas,
                r.id_tahun_ajaran, t.nama_tahun, r.nomor_absen
            FROM riwayat_kelas r
            JOIN data_siswa s ON s.id_siswa = r.id_siswa
            JOIN kelas k ON k.id_kelas = r.id_kelas
            JOIN tahun_ajaran t ON t.id_tahun_ajaran = r.id_tahun_ajaran';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @param array{id_kelas:?int, id_tahun_ajaran:?int, id_siswa:?int} $f @return array<int, array> */
    public function daftar(array $f, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($f);
        $st = $this->db->prepare(self::SELECT . $where . ' ORDER BY t.nama_tahun DESC, k.tingkat_kelas, k.nama_kelas, r.nomor_absen, s.nama_siswa LIMIT ? OFFSET ?');
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p, PDO::PARAM_INT);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(static fn(array $r): array => RiwayatKelas::dariBaris($r)->toArray(), $st->fetchAll());
    }

    public function total(array $f): int
    {
        [$where, $params] = $this->filter($f);
        $st = $this->db->prepare('SELECT COUNT(*) FROM riwayat_kelas r' . $where);
        foreach ($params as $i => $p) {
            $st->bindValue($i + 1, $p, PDO::PARAM_INT);
        }
        $st->execute();
        return (int) $st->fetchColumn();
    }

    public function cari(int $id): ?RiwayatKelas
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE r.id_riwayat = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : RiwayatKelas::dariBaris($r);
    }

    public function buat(int $idSiswa, int $idKelas, int $idTahun, ?int $nomorAbsen): int
    {
        $st = $this->db->prepare('INSERT INTO riwayat_kelas (id_siswa, id_kelas, id_tahun_ajaran, nomor_absen) VALUES (?, ?, ?, ?)');
        $st->execute([$idSiswa, $idKelas, $idTahun, $nomorAbsen]);
        return (int) $this->db->lastInsertId();
    }

    public function ubahNomorAbsen(int $id, ?int $nomorAbsen): void
    {
        $st = $this->db->prepare('UPDATE riwayat_kelas SET nomor_absen = ? WHERE id_riwayat = ?');
        $st->execute([$nomorAbsen, $id]);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM riwayat_kelas WHERE id_riwayat = ?');
        $st->execute([$id]);
    }

    public function sudahDitempatkan(int $idSiswa, int $idTahun): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM riwayat_kelas WHERE id_siswa = ? AND id_tahun_ajaran = ? LIMIT 1');
        $st->execute([$idSiswa, $idTahun]);
        return $st->fetchColumn() !== false;
    }

    public function jumlahRiwayatSiswa(int $idSiswa): int
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM riwayat_kelas WHERE id_siswa = ?');
        $st->execute([$idSiswa]);
        return (int) $st->fetchColumn();
    }

    /**
     * Daftar siswa yang dipakai Guru (FR-SISWA-07): hanya siswa berstatus AKTIF pada kelas dan tahun ajaran ini.
     * Siswa baru, lulus, dan pindah tidak tampil (datanya tetap ada untuk laporan).
     * @return array<int, array{id_siswa:int, nama_siswa:string, nisn_resmi:string, nomor_absen:?int}>
     */
    public function siswaAktifKelas(int $idKelas, int $idTahun): array
    {
        $st = $this->db->prepare(
            "SELECT s.id_siswa, s.nama_siswa, s.nisn_resmi, r.nomor_absen
             FROM riwayat_kelas r
             JOIN data_siswa s ON s.id_siswa = r.id_siswa
             WHERE r.id_kelas = ? AND r.id_tahun_ajaran = ? AND s.status_siswa = 'aktif'
             ORDER BY (r.nomor_absen IS NULL), r.nomor_absen, s.nama_siswa"
        );
        $st->execute([$idKelas, $idTahun]);
        return array_map(static function (array $r): array {
            $r['id_siswa']    = (int) $r['id_siswa'];
            $r['nomor_absen'] = $r['nomor_absen'] === null ? null : (int) $r['nomor_absen'];
            return $r;
        }, $st->fetchAll());
    }

    /**
     * Alasan penempatan TIDAK boleh dihapus (FR-KELAS-12). Database tidak menolaknya sendiri karena
     * tabel akademik berelasi ke siswa dan kelas, bukan ke riwayat_kelas.
     * @return string[] kosong = boleh dihapus
     */
    public function alasanTerkait(RiwayatKelas $r): array
    {
        $alasan = [];
        $params = [$r->idSiswa, $r->idKelas, $r->idTahunAjaran];

        $cek = [
            'absensi'      => 'SELECT 1 FROM absensi_siswa WHERE id_siswa = ? AND id_kelas = ? AND id_tahun_ajaran = ? LIMIT 1',
            'nilai'        => 'SELECT 1 FROM data_nilai WHERE id_siswa = ? AND id_kelas = ? AND id_tahun_ajaran = ? LIMIT 1',
            'kedisiplinan' => 'SELECT 1 FROM catatan_kedisiplinan WHERE id_siswa = ? AND id_kelas = ? AND id_tahun_ajaran = ? LIMIT 1',
            'piket'        => 'SELECT 1 FROM piket_siswa ps JOIN jadwal_piket jp ON jp.id_piket = ps.id_piket
                               WHERE ps.id_siswa = ? AND jp.id_kelas = ? AND jp.id_tahun_ajaran = ? LIMIT 1',
        ];
        foreach ($cek as $nama => $sql) {
            $st = $this->db->prepare($sql);
            $st->execute($params);
            if ($st->fetchColumn() !== false) {
                $alasan[] = $nama;
            }
        }

        $st = $this->db->prepare('SELECT 1 FROM log_promosi_siswa WHERE id_siswa = ? AND (id_tahun_asal = ? OR id_tahun_tujuan = ?) LIMIT 1');
        $st->execute([$r->idSiswa, $r->idTahunAjaran, $r->idTahunAjaran]);
        if ($st->fetchColumn() !== false) {
            $alasan[] = 'log kenaikan kelas';
        }
        return $alasan;
    }

    private function filter(array $f): array
    {
        $w = [];
        $p = [];
        foreach (['id_kelas' => 'r.id_kelas', 'id_tahun_ajaran' => 'r.id_tahun_ajaran', 'id_siswa' => 'r.id_siswa'] as $key => $kolom) {
            if (($f[$key] ?? null) !== null) {
                $w[] = $kolom . ' = ?';
                $p[] = (int) $f[$key];
            }
        }
        return [$w ? ' WHERE ' . implode(' AND ', $w) : '', $p];
    }
}
