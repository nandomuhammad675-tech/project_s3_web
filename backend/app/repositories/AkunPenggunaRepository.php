<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\AkunPengguna;
use PDO;

final class AkunPenggunaRepository
{
    private const KOLOM = 'id_user, username, kata_sandi_hash, role, status_akun,
                           UNIX_TIMESTAMP(kata_sandi_diubah_pada) AS pw_ts';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function cariByUsername(string $username): ?AkunPengguna
    {
        $st = $this->db->prepare('SELECT ' . self::KOLOM . ' FROM akun_pengguna WHERE username = ? LIMIT 1');
        $st->execute([$username]);
        return $this->hidrasi($st->fetch());
    }

    public function cariById(int $id): ?AkunPengguna
    {
        $st = $this->db->prepare('SELECT ' . self::KOLOM . ' FROM akun_pengguna WHERE id_user = ? LIMIT 1');
        $st->execute([$id]);
        return $this->hidrasi($st->fetch());
    }

    /** Simpan hash baru dan tandai waktu ganti password (token lama akan ditolak). */
    public function ubahPassword(int $idUser, string $hashBaru): void
    {
        $st = $this->db->prepare('UPDATE akun_pengguna SET kata_sandi_hash = ?, kata_sandi_diubah_pada = NOW() WHERE id_user = ?');
        $st->execute([$hashBaru, $idUser]);
    }

    private function hidrasi(array|false $r): ?AkunPengguna
    {
        if ($r === false) {
            return null;
        }
        return new AkunPengguna(
            (int) $r['id_user'],
            (string) $r['username'],
            (string) $r['kata_sandi_hash'],
            (string) $r['role'],
            (string) $r['status_akun'],
            $r['pw_ts'] === null ? null : (int) $r['pw_ts']
        );
    }
}
