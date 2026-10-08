<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class AkunValidator
{
    public static function username(Validasi $v): ?string
    {
        $u = $v->string('username', 'Username', true, 50, 3);
        if ($u !== null && !preg_match('/^[A-Za-z0-9._-]+$/', $u)) {
            $v->error('username', 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.');
            return null;
        }
        return $u;
    }

    /** Kebijakan password: 8 karakter (usulan URS 6.2) sampai 72 byte (batas bcrypt). */
    public static function password(Validasi $v, string $key): ?string
    {
        $p = $v->rahasia($key, 'Password');
        if ($p === null) {
            return null;
        }
        if (mb_strlen($p) < 8) {
            $v->error($key, 'Password minimal 8 karakter.');
            return null;
        }
        if (strlen($p) > 72) {
            $v->error($key, 'Password maksimal 72 byte.');
            return null;
        }
        return $p;
    }
}
