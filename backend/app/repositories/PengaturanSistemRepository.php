<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/** Pengaturan key-value. Kunci yang dikelola Admin dibatasi daftar putih di PengaturanService. */
final class PengaturanSistemRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /**
     * Baris pengaturan untuk kunci tertentu, lengkap dengan metadata perubahan (FR-CONT-06).
     * @param string[] $kunci
     * @return array<string, array{kunci:string, nilai:?string, diubah_pada:?string, diubah_oleh:?string}>
     */
    public function untukKunci(array $kunci): array
    {
        $in = implode(',', array_fill(0, count($kunci), '?'));
        $st = $this->db->prepare(
            "SELECT p.kunci, p.nilai, p.diubah_pada, a.username AS diubah_oleh
             FROM pengaturan_sistem p LEFT JOIN akun_pengguna a ON a.id_user = p.id_user_pengubah
             WHERE p.kunci IN ($in)"
        );
        $st->execute(array_values($kunci));
        $hasil = [];
        foreach ($st->fetchAll() as $r) {
            $r['diubah_pada'] = $r['diubah_pada'] === null ? null : gmdate('c', strtotime($r['diubah_pada'] . ' UTC'));
            $hasil[$r['kunci']] = $r;
        }
        return $hasil;
    }

    /** Upsert: tetap bekerja walau baris seed belum ada. Mencatat waktu (otomatis) dan pengguna pengubah. */
    public function simpan(string $kunci, ?string $nilai, int $idUser): void
    {
        $st = $this->db->prepare(
            'INSERT INTO pengaturan_sistem (kunci, nilai, id_user_pengubah) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE nilai = VALUES(nilai), id_user_pengubah = VALUES(id_user_pengubah), diubah_pada = CURRENT_TIMESTAMP'
        );
        $st->execute([$kunci, $nilai, $idUser]);
    }
}
