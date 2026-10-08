<?php
declare(strict_types=1);

namespace App\Helpers;

use PDO;

final class SqlHelper
{
    /**
     * UPDATE parsial: hanya kolom di $changes. Nama kolom WAJIB ada di daftar putih $kolomBoleh
     * (nama kolom tidak bisa di-bind, jadi tidak boleh pernah berasal dari input pengguna).
     * Nilai tetap lewat prepared statement.
     */
    public static function update(PDO $db, string $tabel, string $pk, int $id, array $changes, array $kolomBoleh): void
    {
        $set  = [];
        $vals = [];
        foreach ($changes as $k => $v) {
            if (!in_array($k, $kolomBoleh, true)) {
                throw new \InvalidArgumentException('Kolom tidak diizinkan: ' . $k);
            }
            $set[]  = '`' . $k . '` = ?';
            $vals[] = $v;
        }
        if (!$set) {
            return;
        }
        $vals[] = $id;
        $db->prepare('UPDATE `' . $tabel . '` SET ' . implode(', ', $set) . ' WHERE `' . $pk . '` = ?')->execute($vals);
    }
}
