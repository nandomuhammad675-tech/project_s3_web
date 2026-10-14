<?php
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Router;
use App\Middleware\CorsMiddleware;

Dotenv\Dotenv::createImmutable($root)->safeLoad();
date_default_timezone_set((string) Config::get('app.timezone', 'Asia/Jakarta'));
ErrorHandler::register();

$request = Request::capture((string) Config::get('app.base_path', ''));
(new CorsMiddleware())->handle($request);

$router = new Router();
foreach (['api_auth', 'api_public', 'api_admin', 'api_guru', 'api_wali'] as $file) {
    (require $root . '/routes/' . $file . '.php')($router);
}

$router->dispatch($request);
