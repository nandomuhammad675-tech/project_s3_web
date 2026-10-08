<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/** Satu koneksi PDO per request. Semua query WAJIB prepared statement (NFR-SEC-04). */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $c = Config::get('database');
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $c['host'], $c['port'], $c['name'], $c['charset']
            );
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                // timestamp di skema memakai UTC (si_kesa_v9.sql: time_zone +00:00)
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
            ]);
        }
        return self::$pdo;
    }
}
