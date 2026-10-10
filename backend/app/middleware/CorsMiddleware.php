<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Config;
use App\Core\Request;

/** Dipanggil dari public/index.php sebelum routing agar preflight OPTIONS terjawab. */
final class CorsMiddleware
{
    public function handle(Request $req): void
    {
        $cfg    = Config::get('cors');
        $origin = $req->header('origin');

        if ($origin !== null) {
            $allowed = in_array('*', $cfg['allowed_origins'], true) || in_array($origin, $cfg['allowed_origins'], true);
            if ($allowed) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Vary: Origin');
                header('Access-Control-Allow-Methods: ' . $cfg['allowed_methods']);
                header('Access-Control-Allow-Headers: ' . $cfg['allowed_headers']);
                header('Access-Control-Max-Age: ' . $cfg['max_age']);
                header('Access-Control-Expose-Headers: Content-Disposition');   // agar nama berkas unduhan terbaca klien
            }
        }

        if ($req->method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
