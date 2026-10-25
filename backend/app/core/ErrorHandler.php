<?php
declare(strict_types=1);

namespace App\Core;

use App\Helpers\LogHelper;
use App\Helpers\ResponseHelper;

/**
 * Semua error berakhir di sini. Klien TIDAK PERNAH menerima stack trace, SQL, atau
 * credential (NFR-SEC-06); detail teknis hanya ke storage/logs/app.log.
 * Pemetaan kode error MySQL mengikuti TDD §10.1.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        set_error_handler(static function (int $no, string $str, string $file, int $line): bool {
            if ($no & (E_DEPRECATED | E_USER_DEPRECATED)) {
                return true;
            }
            if (!(error_reporting() & $no)) {
                return false;
            }
            throw new \ErrorException($str, 0, $no, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(\Throwable $e): void
    {
        if ($e instanceof HttpException) {
            ResponseHelper::error($e->getMessage(), $e->status, $e->errors);
            return;
        }

        if ($e instanceof \PDOException) {
            $driverCode = (int) ($e->errorInfo[1] ?? 0);
            switch ($driverCode) {
                case 1062:
                    ResponseHelper::error('Data sudah ada (duplikat).', 409);
                    return;
                case 1451:
                    ResponseHelper::error('Data tidak dapat dihapus karena masih terkait data lain. Gunakan status nonaktif.', 409);
                    return;
                case 1452:
                    ResponseHelper::error('Referensi data tidak ditemukan.', 422);
                    return;
                case 3819:  // MySQL CHECK
                case 4025:  // MariaDB CHECK
                    ResponseHelper::error('Data tidak memenuhi aturan validasi.', 422);
                    return;
            }
        }

        LogHelper::error(get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        ResponseHelper::error('Terjadi kesalahan pada server.', 500);
    }
}
