<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Enam program pembiasaan (Senin-Sabtu). Baris diisi data awal; hari tidak dapat diubah. */
final class ProgramPembiasaanRepository
{
    private const SELECT = "SELECT id_program, hari, nama_program, deskripsi, ikon FROM program_pembiasaan";
    private const URUT   = " ORDER BY FIELD(hari, 'senin','selasa','rabu','kamis','jumat','sabtu')";

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function daftar(): array
    {
        return array_map([$this, 'rapikan'], $this->db->query(self::SELECT . self::URUT)->fetchAll());
    }

    /** Whitelist publik: tanpa id. */
    public function publik(): array
    {
        return $this->db->query("SELECT hari, nama_program, deskripsi, ikon FROM program_pembiasaan" . self::URUT)->fetchAll();
    }

    public function cari(int $id): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE id_program = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    public function ubah(int $id, array $changes): void
    {
        \App\Helpers\SqlHelper::update($this->db, 'program_pembiasaan', 'id_program', $id, $changes, ['nama_program', 'deskripsi', 'ikon']);
    }

    private function rapikan(array $r): array
    {
        $r['id_program'] = (int) $r['id_program'];
        return $r;
    }
}
