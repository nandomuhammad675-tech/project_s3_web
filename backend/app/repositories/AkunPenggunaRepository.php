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

    /** Login Admin/Guru: username dahulu; bila tidak ada, cocokkan NIP Guru (data_guru.nip, unik). */
    public function cariByUsernameAtauNip(string $identitas): ?AkunPengguna
    {
        $akun = $this->cariByUsername($identitas);
        if ($akun !== null || $identitas === '') {
            return $akun;
        }
        $st = $this->db->prepare(
            'SELECT a.id_user FROM akun_pengguna a JOIN data_guru g ON g.id_user = a.id_user WHERE g.nip = ? LIMIT 1'
        );
        $st->execute([$identitas]);
        $row = $st->fetch();
        return $row ? $this->cariById((int) $row['id_user']) : null;
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

    // ---- manajemen akun (Admin) ----

    /** @return array<int, array> tanpa kata_sandi_hash */
    public function daftar(int $limit, int $offset): array
    {
        $st = $this->db->prepare(
            'SELECT ' . $this->proyeksi() . ' FROM akun_pengguna a LEFT JOIN data_guru g ON g.id_user = a.id_user ORDER BY a.id_user LIMIT ? OFFSET ?'
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->bindValue(2, $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM akun_pengguna')->fetchColumn();
    }

    public function ringkas(int $id): ?array
    {
        $st = $this->db->prepare('SELECT ' . $this->proyeksi() . ' FROM akun_pengguna a LEFT JOIN data_guru g ON g.id_user = a.id_user WHERE a.id_user = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $r;
    }

    /** Kolom terakhir_login_pada ada? (skema v10). Dicek sekali agar basis data v9 lama tetap berjalan. */
    private function adaKolomLogin(): bool
    {
        static $ada = null;
        if ($ada === null) {
            $st = $this->db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'akun_pengguna' AND COLUMN_NAME = 'terakhir_login_pada'");
            $ada = (int) $st->fetchColumn() > 0;
        }
        return $ada;
    }

    /** Kolom untuk daftar akun: nama (nama Guru bila akun Guru) dan terakhir_aktif (waktu login terakhir). */
    private function proyeksi(): string
    {
        // Sesi basis data memakai UTC (lihat Database.php); waktu dikirim ber-zona (+00:00) agar klien
        // mengubahnya ke WIB dengan benar (sama seperti diubah_pada pada Pengaturan).
        $utc = static fn(string $kolom): string => "DATE_FORMAT($kolom, '%Y-%m-%dT%H:%i:%s+00:00')";
        return 'a.id_user, a.username, a.role, a.status_akun, ' . $utc('a.dibuat_pada') . ' AS dibuat_pada, g.nama_guru AS nama, '
            . ($this->adaKolomLogin() ? $utc('a.terakhir_login_pada') : 'NULL') . ' AS terakhir_aktif';
    }

    /** Catat waktu login berhasil (untuk kolom "Terakhir aktif" pada Kelola Akun). Kegagalan dicatat tidak menggagalkan login. */
    public function catatLogin(int $idUser): void
    {
        try {
            if ($this->adaKolomLogin()) {
                $this->db->prepare('UPDATE akun_pengguna SET terakhir_login_pada = NOW() WHERE id_user = ?')->execute([$idUser]);
            }
        } catch (\Throwable $e) {
            // abaikan
        }
    }

    public function buat(string $username, string $hash, string $role): int
    {
        $st = $this->db->prepare('INSERT INTO akun_pengguna (username, kata_sandi_hash, role) VALUES (?, ?, ?)');
        $st->execute([$username, $hash, $role]);
        return (int) $this->db->lastInsertId();
    }

    public function ubahStatus(int $id, string $status): void
    {
        $st = $this->db->prepare('UPDATE akun_pengguna SET status_akun = ? WHERE id_user = ?');
        $st->execute([$status, $id]);
    }

    /** Hitung Admin aktif DAN kunci barisnya; panggil di dalam transaksi agar dua permintaan bersamaan tidak sama-sama lolos. */
    public function hitungAdminAktifKunci(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM akun_pengguna WHERE role = 'admin' AND status_akun = 'aktif' FOR UPDATE")->fetchColumn();
    }

    /**
     * Hapus akun GURU saja (setelah data_guru-nya dihapus). Akun Admin tidak pernah dihapus (BR-27):
     * klausa role = 'guru' menjadikannya mustahil walau method ini salah dipanggil.
     */
    public function hapusAkunGuru(int $id): void
    {
        $st = $this->db->prepare("DELETE FROM akun_pengguna WHERE id_user = ? AND role = 'guru'");
        $st->execute([$id]);
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
