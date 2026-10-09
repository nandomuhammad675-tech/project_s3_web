<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Nilai UH/ASTS/ASAS. Semua query dibatasi kelas dan tahun ajaran milik Guru yang login. */
final class DataNilaiRepository
{
    private const SELECT = 'SELECT n.id_nilai, n.id_siswa, s.nama_siswa, n.id_mapel, m.nama_mapel, n.semester,
                n.jenis_penilaian AS jenis, n.nomor_uh, n.nilai_angka AS nilai
            FROM data_nilai n
            JOIN data_siswa s ON s.id_siswa = n.id_siswa
            JOIN mata_pelajaran m ON m.id_mapel = n.id_mapel';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @param array{id_mapel:?int, semester:?string, id_siswa:?int} $f */
    public function daftar(int $idKelas, int $idTahun, array $f, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($idKelas, $idTahun, $f);
        $st = $this->db->prepare(
            self::SELECT . $where . " ORDER BY m.nama_mapel, n.semester, s.nama_siswa,
                FIELD(n.jenis_penilaian, 'UH', 'ASTS', 'ASAS'), n.nomor_uh LIMIT ? OFFSET ?"
        );
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function total(int $idKelas, int $idTahun, array $f): int
    {
        [$where, $params] = $this->filter($idKelas, $idTahun, $f);
        $st = $this->db->prepare('SELECT COUNT(*) FROM data_nilai n' . $where);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    public function cari(int $id, int $idKelas, int $idTahun): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE n.id_nilai = ? AND n.id_kelas = ? AND n.id_tahun_ajaran = ?');
        $st->execute([$id, $idKelas, $idTahun]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    /** nomor_uh null untuk ASTS/ASAS. Kolom virtual nomor_uh_kunci diisi database. */
    public function buat(int $idKelas, int $idTahun, int $idGuru, array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO data_nilai (id_siswa, id_kelas, id_mapel, id_tahun_ajaran, semester, id_guru, jenis_penilaian, nomor_uh, nilai_angka)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $d['id_siswa'], $idKelas, $d['id_mapel'], $idTahun, $d['semester'], $idGuru,
            $d['jenis'], $d['jenis'] === 'UH' ? $d['nomor_uh'] : null, $d['nilai'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function ubahNilai(int $id, int $nilai): void
    {
        $st = $this->db->prepare('UPDATE data_nilai SET nilai_angka = ? WHERE id_nilai = ?');
        $st->execute([$nilai, $id]);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM data_nilai WHERE id_nilai = ?');
        $st->execute([$id]);
    }

    /** Sudah ada nilai dengan kombinasi unik yang sama? (ASTS/ASAS: satu per siswa-mapel-semester; UH: per nomor) */
    public function ada(int $idSiswa, int $idMapel, int $idTahun, string $semester, string $jenis, ?int $nomorUh): bool
    {
        $st = $this->db->prepare(
            'SELECT 1 FROM data_nilai WHERE id_siswa = ? AND id_mapel = ? AND id_tahun_ajaran = ? AND semester = ?
               AND jenis_penilaian = ? AND IFNULL(nomor_uh, 0) = ? LIMIT 1'
        );
        $st->execute([$idSiswa, $idMapel, $idTahun, $semester, $jenis, $nomorUh ?? 0]);
        return $st->fetchColumn() !== false;
    }

    private function filter(int $idKelas, int $idTahun, array $f): array
    {
        $w = ['n.id_kelas = ?', 'n.id_tahun_ajaran = ?'];
        $p = [$idKelas, $idTahun];
        if (($f['id_mapel'] ?? null) !== null) {
            $w[] = 'n.id_mapel = ?';
            $p[] = (int) $f['id_mapel'];
        }
        if (($f['semester'] ?? null) !== null) {
            $w[] = 'n.semester = ?';
            $p[] = (string) $f['semester'];
        }
        if (($f['id_siswa'] ?? null) !== null) {
            $w[] = 'n.id_siswa = ?';
            $p[] = (int) $f['id_siswa'];
        }
        return [' WHERE ' . implode(' AND ', $w), $p];
    }

    private function rapikan(array $r): array
    {
        foreach (['id_nilai', 'id_siswa', 'id_mapel', 'nilai'] as $k) {
            $r[$k] = (int) $r[$k];
        }
        $r['nomor_uh'] = $r['nomor_uh'] === null ? null : (int) $r['nomor_uh'];
        return $r;
    }
}
