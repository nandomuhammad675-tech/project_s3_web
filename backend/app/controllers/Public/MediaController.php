<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\FileHelper;
use App\Repositories\MediaRepository;

/** GET /api/media?path=assets/img/event/nama-event-1.jpg : gambar dari database (publik; cadangan bila berkas website tidak ada). */
final class MediaController
{
    public function show(Request $req): void
    {
        $path = is_string($_GET['path'] ?? null) ? (string) $_GET['path'] : '';
        if (!preg_match(FileHelper::POLA_PATH, $path)) {
            throw HttpException::notFound('Gambar tidak ditemukan.');
        }
        $m = (new MediaRepository())->baca($path);
        if ($m === null) {
            throw HttpException::notFound('Gambar tidak ditemukan.');
        }
        $etag = '"' . md5($path . $m['ukuran']) . '"';
        header('Content-Type: ' . $m['mime']);
        header('Content-Length: ' . $m['ukuran']);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=86400');
        header('ETag: ' . $etag);
        if ((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);
            return;
        }
        echo $m['data'];
    }
}
