<?php
declare(strict_types=1);

namespace App\Core;

/** Error yang aman ditampilkan ke klien (pesan + status HTTP). */
class HttpException extends \RuntimeException
{
    public function __construct(
        public readonly int $status,
        string $message,
        public readonly array $errors = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $status, $previous);
    }

    public static function badRequest(string $m = 'Request tidak valid.', array $e = []): self { return new self(400, $m, $e); }
    public static function unauthorized(string $m = 'Unauthorized.'): self { return new self(401, $m); }
    public static function forbidden(string $m = 'Anda tidak berwenang mengakses sumber daya ini.'): self { return new self(403, $m); }
    public static function notFound(string $m = 'Data tidak ditemukan.'): self { return new self(404, $m); }
    public static function methodNotAllowed(string $m = 'Metode tidak tersedia.'): self { return new self(405, $m); }
    public static function conflict(string $m, array $e = []): self { return new self(409, $m, $e); }
    public static function unprocessable(string $m, array $e = []): self { return new self(422, $m, $e); }
    public static function tooManyRequests(string $m = 'Terlalu banyak percobaan. Coba lagi nanti.'): self { return new self(429, $m); }
}
