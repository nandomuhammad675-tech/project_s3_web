<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    private ?array $json = null;
    private array $params = [];
    private array $attributes = [];

    private function __construct(
        public readonly string $method,
        public readonly string $path,
        private array $query,
        private string $rawBody,
        private array $headers,
        public readonly string $ip
    ) {}

    public static function capture(string $basePath = ''): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Di Apache/XAMPP backend berada di subfolder (mis. /si-kesa/backend/public).
        // Bila APP_BASE_PATH kosong, turunkan otomatis dari lokasi index.php.
        if ($basePath === '') {
            $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
            if (str_ends_with($script, '/index.php')) {
                $basePath = rtrim(dirname($script), '/\\');
            }
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = rawurldecode($uri);
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $path = '/' . trim($uri, '/');

        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $headers[strtolower(str_replace('_', '-', substr($k, 5)))] = (string) $v;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = (string) $_SERVER['CONTENT_TYPE'];
        }
        if (!isset($headers['authorization']) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers['authorization'] = (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        // Sengaja hanya REMOTE_ADDR: X-Forwarded-For mudah dipalsukan dan merusak rate limit.
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        return new self($method, $path, $_GET, (string) file_get_contents('php://input'), $headers, $ip);
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function bearerToken(): ?string
    {
        $h = $this->header('authorization');
        return ($h !== null && preg_match('/^Bearer\s+(\S+)$/i', $h, $m)) ? $m[1] : null;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /** Body JSON sebagai array. Body kosong = []. Body bukan JSON object/array = 400. */
    public function json(): array
    {
        if ($this->json === null) {
            if (trim($this->rawBody) === '') {
                $this->json = [];
            } else {
                $data = json_decode($this->rawBody, true);
                if (!is_array($data)) {
                    throw HttpException::badRequest('Body harus berupa JSON yang valid.');
                }
                $this->json = $data;
            }
        }
        return $this->json;
    }

    public function setParams(array $p): void { $this->params = $p; }

    public function param(string $name): ?string { return $this->params[$name] ?? null; }

    /** Parameter rute {id} sebagai integer (rute {id} sudah dibatasi \d+ oleh Router). */
    public function id(string $name = 'id'): int { return (int) ($this->params[$name] ?? 0); }

    public function set(string $key, mixed $value): void { $this->attributes[$key] = $value; }

    public function get(string $key, mixed $default = null): mixed { return $this->attributes[$key] ?? $default; }
}
