<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\EventService;
use App\Validators\KontenValidator;

final class EventController
{
    /** GET /api/admin/event?kategori= : semua event, termasuk yang disembunyikan. */
    public function index(Request $req): void
    {
        $kategori = $req->query('kategori');
        if ($kategori !== null && $kategori !== '' && !in_array($kategori, KontenValidator::KATEGORI_EVENT, true)) {
            throw HttpException::badRequest('Parameter kategori harus salah satu dari: ' . implode(', ', KontenValidator::KATEGORI_EVENT) . '.');
        }
        ResponseHelper::success('OK', (new EventService())->daftar(($kategori === null || $kategori === '') ? null : (string) $kategori, Pagination::dari($req)));
    }

    public function show(Request $req): void
    {
        ResponseHelper::success('OK', (new EventService())->detail($req->id()));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Kegiatan berhasil dibuat.', (new EventService())->buat($req->json()));
    }

    public function update(Request $req): void
    {
        ResponseHelper::success('Kegiatan diperbarui.', (new EventService())->ubah($req->id(), $req->json()));
    }

    public function destroy(Request $req): void
    {
        (new EventService())->hapus($req->id());
        ResponseHelper::success('Kegiatan dan galeri terkaitnya berhasil dihapus.');
    }
}
