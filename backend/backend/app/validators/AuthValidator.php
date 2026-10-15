<?php
declare(strict_types=1);

namespace App\Validators;

use App\Core\HttpException;

final class AuthValidator
{
    /** Field wajib berupa string tidak kosong, bila tidak 400. */
    public static function wajibString(array $body, string $key, string $label): string
    {
        $v = $body[$key] ?? null;
        if (!is_string($v) || trim($v) === '') {
            throw HttpException::badRequest($label . ' wajib diisi.', [$key => $label . ' wajib diisi.']);
        }
        return $v;
    }

    /**
     * Kebijakan password: minimal 8 karakter (usulan URS 6.2, masih terbuka),
     * maksimal 72 byte (batas bcrypt), dan tidak sama dengan password lama.
     */
    public static function passwordBaru(string $lama, string $baru): void
    {
        $err = [];
        if (mb_strlen($baru) < 8) {
            $err['password_baru'] = 'Password baru minimal 8 karakter.';
        } elseif (strlen($baru) > 72) {
            $err['password_baru'] = 'Password baru maksimal 72 byte.';
        } elseif ($baru === $lama) {
            $err['password_baru'] = 'Password baru tidak boleh sama dengan password lama.';
        }
        if ($err) {
            throw HttpException::unprocessable('Validasi gagal.', $err);
        }
    }
}
