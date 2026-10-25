<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Core\Config;
use App\Core\HttpException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtHelper
{
    public static function issue(array $claims, int $ttl): string
    {
        $now = time();
        $payload = array_merge($claims, ['iat' => $now, 'exp' => $now + $ttl]);
        return JWT::encode($payload, self::secret(), (string) Config::get('auth.jwt_algo', 'HS256'));
    }

    /** @return array<string, mixed> claims; lempar 401 bila rusak/kedaluwarsa/tanda tangan salah */
    public static function verify(string $token): array
    {
        $key  = self::secret();                         // di luar try: secret kosong = error server, bukan 401
        $algo = (string) Config::get('auth.jwt_algo', 'HS256');
        try {
            return (array) JWT::decode($token, new Key($key, $algo));
        } catch (\Throwable $e) {
            throw HttpException::unauthorized('Token tidak valid atau sudah kedaluwarsa.');
        }
    }

    private static function secret(): string
    {
        $s = (string) Config::get('auth.jwt_secret', '');
        if (strlen($s) < 32) {
            throw new \RuntimeException('JWT_SECRET belum diatur di .env (minimal 32 karakter).');
        }
        return $s;
    }
}
