<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\SqlHelper;
use PDO;

/**
 * Catatan kedisiplinan. TIDAK ada method hapus: catatan tidak dapat dihapus oleh siapa pun (FR-DIS-05, BR-29).
 * Semua query dibatasi kelas dan tahun ajaran milik Guru yang login.
 */
final class CatatanKedisiplinanRepository
{
    private const KOLOM_UBAH = ['tanggal', 'jenis_pelanggaran', 'catatan'];

    private const SELECT = 'SELECT c.id_catatan, c.id_siswa, s.nama_siswa, c.tanggal, c.jenis_pelanggaran, c.catatan
            FROM catatan_kedisiplinan c JOIN data_siswa s ON s.id_siswa = c.id_siswa';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(int $idKelas, int $idTahun, ?int $idSiswa, int $limit, int $offset): array
    {
        $where  = ' WHERE c.id_kelas = ? AND c.id_tahun_ajaran = ?' . ($idSiswa !== null ? ' AND c.id_siswa = ?' : '');
        $params = $idSiswa !== null ? [$idKelas, $idTahun, $idSiswa] : [$idKelas, $idTahun];
        $st = $this->db->prepare(self::SELECT . $where . ' ORDER BY c.tanggal DESC, c.id_catatan DESC LIMIT ? OFFSET ?');
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p, PDO::PARAM_INT);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function total(int $idKelas, int $idTahun, ?int $idSiswa): int
    {
        $sql = 'SELECT COUNT(*) FROM catatan_kedisiplinan c WHERE c.id_kelas = ? AND c.id_tahun_ajaran = ?' . ($idSiswa !== null ? ' AND c.id_siswa = ?' : '');
        $st = $this->db->prepare($sql);
        $st->execute($idSiswa !== null ? [$idKelas, $idTahun, $idSiswa] : [$idKelas, $idTahun]);
        return (int) $st->fetchColumn();
    }

    public function cari(int $id, int $idKelas, int $idTahun): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE c.id_catatan = ? AND c.id_kelas = ? AND c.id_tahun_ajaran = ?');
        $st->execute([$id, $idKelas, $idTahun]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    public function buat(int $idKelas, int $idTahun, int $idGuru, array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO catatan_kedisiplinan (id_siswa, id_guru, id_kelas, id_tahun_ajaran, tanggal, jenis_pelanggaran, catatan)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$d['id_siswa'], $idGuru, $idKelas, $idTahun, $d['tanggal'], $d['jenis_pelanggaran'], $d['catatan']]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        SqlHelper::update($this->db, 'catatan_kedisiplinan', 'id_catatan', $id, $changes, self::KOLOM_UBAH);
    }

    private function rapikan(array $r): array
    {
        $r['id_catatan'] = (int) $r['id_catatan'];
        $r['id_siswa']   = (int) $r['id_siswa'];
        return $r;
    }
}
