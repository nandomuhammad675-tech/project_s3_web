<?php
declare(strict_types=1);

namespace App\Helpers;

/** Format respons seragam: {success, message, data} atau {success:false, message[, errors]} (SRS §3.1). */
final class ResponseHelper
{
    public static function json(int $status, array $payload): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    public static function success(string $message = 'Berhasil.', mixed $data = null, int $status = 200): void
    {
        self::json($status, ['success' => true, 'message' => $message, 'data' => $data]);
    }

    public static function created(string $message = 'Data berhasil dibuat.', mixed $data = null): void
    {
        self::success($message, $data, 201);
    }

    public static function error(string $message, int $status, array $errors = []): void
    {
        $payload = ['success' => false, 'message' => $message];
        if ($errors) {
            $payload['errors'] = $errors;
        }
        self::json($status, $payload);
    }
}
