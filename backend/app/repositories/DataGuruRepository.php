<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\Pagination;
use App\Helpers\SqlHelper;
use App\Models\Guru;
use PDO;

final class DataGuruRepository
{
    private const KOLOM_UBAH = ['nip', 'nama_guru', 'jabatan', 'foto_profil', 'email', 'no_telepon'];

    /** Guru + akun + kelas yang diwalikan pada tahun ajaran AKTIF (bila ada). */
    private const SELECT = "SELECT g.id_guru, g.id_user, a.username, a.status_akun, g.nip, g.nama_guru, g.jabatan,
                g.foto_profil, g.email, g.no_telepon, k.id_kelas AS id_kelas_aktif, k.nama_kelas AS kelas_aktif
            FROM data_guru g
            JOIN akun_pengguna a ON a.id_user = g.id_user
            LEFT JOIN kelas k ON k.id_guru_wali = g.id_guru
                 AND k.id_tahun_ajaran = (SELECT id_tahun_ajaran FROM tahun_ajaran WHERE status_aktif = 1 LIMIT 1)";

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return array<int, array> */
    public function daftar(?string $q, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($q);
        $st = $this->db->prepare(self::SELECT . $where . ' ORDER BY g.nama_guru, g.id_guru LIMIT ? OFFSET ?');
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(static fn(array $r): array => Guru::dariBaris($r)->toArray(), $st->fetchAll());
    }

    public function total(?string $q): int
    {
        [$where, $params] = $this->filter($q);
        $st = $this->db->prepare('SELECT COUNT(*) FROM data_guru g' . $where);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    public function cari(int $id): ?Guru
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE g.id_guru = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : Guru::dariBaris($r);
    }

    /** @param array $d kolom data_guru (kunci yang tidak ada dianggap NULL) */
    public function buat(int $idUser, array $d): int
    {
        $st = $this->db->prepare(
            'INSERT INTO data_guru (id_user, nip, nama_guru, jabatan, foto_profil, email, no_telepon) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $idUser, $d['nip'] ?? null, $d['nama_guru'], $d['jabatan'] ?? null,
            $d['foto_profil'] ?? null, $d['email'] ?? null, $d['no_telepon'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        SqlHelper::update($this->db, 'data_guru', 'id_guru', $id, $changes, self::KOLOM_UBAH);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM data_guru WHERE id_guru = ?');
        $st->execute([$id]);
    }

    /** Guru menjadi wali kelas mana pun (tahun ajaran mana pun)? */
    public function adaKelasWali(int $id): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM kelas WHERE id_guru_wali = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetchColumn() !== false;
    }

    /**
     * Konteks Guru yang sedang login: data guru + tahun ajaran aktif + kelas yang diwalikan di tahun itu.
     * null = akun ini tidak punya data_guru. id_kelas null = tidak ada tahun aktif / belum jadi wali.
     * @return array{id_guru:int, nama_guru:string, id_tahun_ajaran:?int, nama_tahun:?string, semester:?string,
     *               id_kelas:?int, nama_kelas:?string, tingkat_kelas:?int}|null
     */
    public function konteksAktif(int $idUser): ?array
    {
        $st = $this->db->prepare(
            'SELECT g.id_guru, g.nama_guru, t.id_tahun_ajaran, t.nama_tahun, t.semester_aktif,
                    k.id_kelas, k.nama_kelas, k.tingkat_kelas
             FROM data_guru g
             LEFT JOIN tahun_ajaran t ON t.status_aktif = 1
             LEFT JOIN kelas k ON k.id_guru_wali = g.id_guru AND k.id_tahun_ajaran = t.id_tahun_ajaran
             WHERE g.id_user = ? LIMIT 1'
        );
        $st->execute([$idUser]);
        $r = $st->fetch();
        if ($r === false) {
            return null;
        }
        return [
            'id_guru'         => (int) $r['id_guru'],
            'nama_guru'       => (string) $r['nama_guru'],
            'id_tahun_ajaran' => $r['id_tahun_ajaran'] === null ? null : (int) $r['id_tahun_ajaran'],
            'nama_tahun'      => $r['nama_tahun'],
            'semester'        => $r['semester_aktif'],
            'id_kelas'        => $r['id_kelas'] === null ? null : (int) $r['id_kelas'],
            'nama_kelas'      => $r['nama_kelas'],
            'tingkat_kelas'   => $r['tingkat_kelas'] === null ? null : (int) $r['tingkat_kelas'],
        ];
    }

    private function filter(?string $q): array
    {
        if ($q === null || $q === '') {
            return ['', []];
        }
        $like = Pagination::like($q);
        return [' WHERE (g.nama_guru LIKE ? OR g.nip LIKE ?)', [$like, $like]];
    }
}
