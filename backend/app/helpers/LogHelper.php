<?php
declare(strict_types=1);

namespace App\Helpers;

/** Log ke storage/logs/app.log. Jangan pernah mencatat password, token, atau NISN lengkap. */
final class LogHelper
{
    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    private static function write(string $level, string $message): void
    {
        $file = dirname(__DIR__, 2) . '/storage/logs/app.log';
        $line = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), $level, str_replace(["\r", "\n"], ' ', $message));
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
