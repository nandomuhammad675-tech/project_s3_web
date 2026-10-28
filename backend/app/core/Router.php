<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, regex:string, handler:array|callable, middleware:array}> */
    private array $routes = [];

    public function add(string $method, string $pattern, array|callable $handler, array $middleware = []): void
    {
        // {id} = angka; {nama} lain = huruf/angka/_/-
        $regex = '#^' . preg_replace_callback('/\{(\w+)\}/', static function (array $m): string {
            return $m[1] === 'id' ? '(?P<id>\d+)' : '(?P<' . $m[1] . '>[A-Za-z0-9_-]+)';
        }, $pattern) . '$#';

        $this->routes[] = [
            'method'     => strtoupper($method),
            'regex'      => $regex,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function get(string $p, array|callable $h, array $mw = []): void    { $this->add('GET', $p, $h, $mw); }
    public function post(string $p, array|callable $h, array $mw = []): void   { $this->add('POST', $p, $h, $mw); }
    public function put(string $p, array|callable $h, array $mw = []): void    { $this->add('PUT', $p, $h, $mw); }
    public function delete(string $p, array|callable $h, array $mw = []): void { $this->add('DELETE', $p, $h, $mw); }

    public function dispatch(Request $req): void
    {
        $allowed = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $req->path, $m)) {
                continue;
            }
            if ($route['method'] !== $req->method) {
                $allowed[] = $route['method'];
                continue;
            }

            $req->setParams(array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY));

            foreach ($route['middleware'] as $mw) {
                $class = is_array($mw) ? array_shift($mw) : $mw;
                $args  = is_array($mw) ? $mw : [];
                (new $class(...$args))->handle($req);
            }

            $h = $route['handler'];
            if (is_array($h) && is_string($h[0])) {
                (new $h[0]())->{$h[1]}($req);
            } else {
                $h($req);
            }
            return;
        }

        if ($allowed) {
            header('Allow: ' . implode(', ', array_unique($allowed)));
            throw HttpException::methodNotAllowed();
        }
        throw HttpException::notFound('Endpoint tidak ditemukan.');
    }
}
