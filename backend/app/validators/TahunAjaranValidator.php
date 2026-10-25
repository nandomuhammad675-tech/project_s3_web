<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class TahunAjaranValidator
{
    /** Format "2026/2027": dua tahun berurutan. */
    public static function nama(Validasi $v, bool $wajib): ?string
    {
        $n = $v->string('nama_tahun', 'Nama tahun ajaran', $wajib, 9, 9);
        if ($n === null) {
            return null;
        }
        if (!preg_match('/^(\d{4})\/(\d{4})$/', $n, $m) || (int) $m[2] !== (int) $m[1] + 1) {
            $v->error('nama_tahun', 'Nama tahun ajaran harus berformat 2026/2027 (dua tahun berurutan).');
            return null;
        }
        return $n;
    }
}
