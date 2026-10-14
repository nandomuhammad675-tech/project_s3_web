<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Baca saja (read-only) data akademik untuk Admin lintas kelas. Tidak ada metode tulis di sini (FR-PWA-ADMIN-04).
 * Filter selalu memuat id_tahun_ajaran; kolom lain opsional.
 */
final class AkademikAdminRepository
{
    private const BASE = [
        'absensi' => [
            'alias' => 'a',
            'tanggal' => 'a.tanggal_absen',
            'select' => 'SELECT a.id_absensi, a.tanggal_absen AS tanggal, a.status_kehadiran AS status, a.semester,
                    a.id_siswa, s.nisn_resmi AS nisn, s.nama_siswa, a.id_kelas, k.nama_kelas, g.nama_guru',
            'from' => ' FROM absensi_siswa a
                    JOIN data_siswa s ON s.id_siswa = a.id_siswa
                    JOIN kelas k ON k.id_kelas = a.id_kelas
                    JOIN data_guru g ON g.id_guru = a.id_guru',
            'order' => ' ORDER BY a.tanggal_absen DESC, k.nama_kelas, s.nama_siswa, a.id_absensi',
        ],
        'nilai' => [
            'alias' => 'n',
            'tanggal' => null,
            'select' => 'SELECT n.id_nilai, n.id_siswa, s.nisn_resmi AS nisn, s.nama_siswa, n.id_kelas, k.nama_kelas,
                    n.id_mapel, m.nama_mapel, n.semester, n.jenis_penilaian AS jenis, n.nomor_uh, n.nilai_angka AS nilai, g.nama_guru',
            'from' => ' FROM data_nilai n
                    JOIN data_siswa s ON s.id_siswa = n.id_siswa
                    JOIN kelas k ON k.id_kelas = n.id_kelas
                    JOIN mata_pelajaran m ON m.id_mapel = n.id_mapel
                    JOIN data_guru g ON g.id_guru = n.id_guru',
            'order' => " ORDER BY k.nama_kelas, s.nama_siswa, m.nama_mapel, n.semester,
                    FIELD(n.jenis_penilaian, 'UH', 'ASTS', 'ASAS'), n.nomor_uh",
        ],
        'disiplin' => [
            'alias' => 'c',
            'tanggal' => 'c.tanggal',
            'select' => 'SELECT c.id_catatan, c.id_siswa, s.nisn_resmi AS nisn, s.nama_siswa, c.id_kelas, k.nama_kelas,
                    c.tanggal, c.jenis_pelanggaran, c.catatan, g.nama_guru',
            'from' => ' FROM catatan_kedisiplinan c
                    JOIN data_siswa s ON s.id_siswa = c.id_siswa
                    JOIN kelas k ON k.id_kelas = c.id_kelas
                    JOIN data_guru g ON g.id_guru = c.id_guru',
            'order' => ' ORDER BY c.tanggal DESC, k.nama_kelas, s.nama_siswa, c.id_catatan',
        ],
    ];

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @param 'absensi'|'nilai'|'disiplin' $jenis */
    public function daftar(string $jenis, array $f, int $limit, int $offset): array
    {
        $c = self::BASE[$jenis];
        [$where, $params] = $this->filter($c, $f);
        $st = $this->db->prepare($c['select'] . $c['from'] . $where . $c['order'] . ' LIMIT ? OFFSET ?');
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(fn(array $r): array => $this->rapikan($r), $st->fetchAll());
    }

    public function total(string $jenis, array $f): int
    {
        $c = self::BASE[$jenis];
        [$where, $params] = $this->filter($c, $f);
        $st = $this->db->prepare('SELECT COUNT(*)' . $c['from'] . $where);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    private function filter(array $c, array $f): array
    {
        $a = $c['alias'];
        $w = ["$a.id_tahun_ajaran = ?"];
        $p = [$f['id_tahun_ajaran']];
        foreach (['id_kelas', 'id_siswa', 'semester', 'id_mapel'] as $k) {
            if (($f[$k] ?? null) !== null) {
                $w[] = "$a.$k = ?";
                $p[] = $f[$k];
            }
        }
        if (($f['status'] ?? null) !== null) {
            $w[] = "$a.status_kehadiran = ?";
            $p[] = $f['status'];
        }
        if (($f['jenis'] ?? null) !== null) {
            $w[] = "$a.jenis_penilaian = ?";
            $p[] = $f['jenis'];
        }
        if ($c['tanggal'] !== null) {
            if (($f['dari'] ?? null) !== null) {
                $w[] = $c['tanggal'] . ' >= ?';
                $p[] = $f['dari'];
            }
            if (($f['sampai'] ?? null) !== null) {
                $w[] = $c['tanggal'] . ' <= ?';
                $p[] = $f['sampai'];
            }
        }
        return [' WHERE ' . implode(' AND ', $w), $p];
    }

    private function rapikan(array $r): array
    {
        foreach (['id_absensi', 'id_nilai', 'id_catatan', 'id_siswa', 'id_kelas', 'id_mapel', 'nilai'] as $k) {
            if (isset($r[$k])) {
                $r[$k] = (int) $r[$k];
            }
        }
        if (array_key_exists('nomor_uh', $r)) {
            $r['nomor_uh'] = $r['nomor_uh'] === null ? null : (int) $r['nomor_uh'];
        }
        return $r;
    }
}
