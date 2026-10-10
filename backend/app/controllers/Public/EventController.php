<?php
declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\EventService;
use App\Validators\KontenValidator;

final class EventController
{
    /** GET /api/public/event?kategori=&page=&per_page= : event berstatus tampil + galeri terkait. */
    public function index(Request $req): void
    {
        $kategori = $req->query('kategori');
        if ($kategori !== null && $kategori !== '' && !in_array($kategori, KontenValidator::KATEGORI_EVENT, true)) {
            throw HttpException::badRequest('Parameter kategori harus salah satu dari: ' . implode(', ', KontenValidator::KATEGORI_EVENT) . '.');
        }
        ResponseHelper::success('OK', (new EventService())->publik(
            ($kategori === null || $kategori === '') ? null : (string) $kategori,
            Pagination::dari($req, 20, 100)
        ));
    }
}
