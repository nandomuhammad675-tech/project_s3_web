<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * Pembatas percobaan berbasis berkas (storage/ratelimit/*.json). Skema database tidak punya
 * tabel untuk ini dan sengaja tidak diubah. Cukup untuk skala satu sekolah di satu server.
 * Berkas hanya dibuat saat ada percobaan GAGAL (hit), bukan saat pengecekan.
 */
final class RateLimiter
{
    public static function count(string $key, int $window): int
    {
        if (!is_file(self::path($key))) {
            return 0;
        }
        $fh = self::open($key);
        if ($fh === null) {
            return 0;
        }
        flock($fh, LOCK_EX);
        $n = count(self::read($fh, $window));
        flock($fh, LOCK_UN);
        fclose($fh);
        return $n;
    }

    public static function hit(string $key, int $window): void
    {
        $fh = self::open($key);
        if ($fh === null) {
            return;
        }
        flock($fh, LOCK_EX);
        $hits = self::read($fh, $window);
        $hits[] = time();
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, (string) json_encode($hits));
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);

        if (random_int(1, 50) === 1) {
            self::purge();
        }
    }

    public static function clear(string $key): void
    {
        @unlink(self::path($key));
    }

    private static function dir(): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/ratelimit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private static function path(string $key): string
    {
        return self::dir() . '/' . sha1($key) . '.json';
    }

    /** @return resource|null */
    private static function open(string $key)
    {
        $fh = @fopen(self::path($key), 'c+');
        if ($fh === false) {
            LogHelper::error('RateLimiter: tidak dapat membuka berkas di storage/ratelimit (cek izin tulis).');
            return null;
        }
        return $fh;
    }

    /** @param resource $fh */
    private static function read($fh, int $window): array
    {
        rewind($fh);
        $raw  = stream_get_contents($fh);
        $data = json_decode($raw === false || $raw === '' ? '[]' : $raw, true);
        $cut  = time() - $window;
        return array_values(array_filter(
            is_array($data) ? $data : [],
            static fn($t): bool => is_int($t) && $t > $cut
        ));
    }

    private static function purge(): void
    {
        foreach (glob(self::dir() . '/*.json') ?: [] as $f) {
            if (@filemtime($f) < time() - 86400) {
                @unlink($f);
            }
        }
    }
}
