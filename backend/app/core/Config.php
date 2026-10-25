<?php
declare(strict_types=1);

namespace App\Core;

/** Pembaca .env dan file config/*.php (notasi titik: "database.host"). */
final class Config
{
    /** @var array<string, array> */
    private static array $cache = [];

    public static function env(string $key, mixed $default = null): mixed
    {
        $v = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return ($v === false || $v === null || $v === '') ? $default : $v;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $file  = array_shift($parts);

        if (!isset(self::$cache[$file])) {
            $path = dirname(__DIR__, 2) . '/config/' . $file . '.php';
            self::$cache[$file] = is_file($path) ? (require $path) : [];
        }

        $value = self::$cache[$file];
        foreach ($parts as $p) {
            if (!is_array($value) || !array_key_exists($p, $value)) {
                return $default;
            }
            $value = $value[$p];
        }
        return $value;
    }
}
