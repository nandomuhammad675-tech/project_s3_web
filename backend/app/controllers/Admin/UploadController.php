<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\FileHelper;
use App\Helpers\ResponseHelper;
use App\Validators\UploadValidator;

final class UploadController
{
    /**
     * POST /api/admin/upload (multipart/form-data, field "file"): simpan gambar, kembalikan path relatif
     * untuk dipakai di kolom gambar (banner, ikon, file_gambar, foto_profil). Database hanya menyimpan path.
     */
    public function store(Request $req): void
    {
        $f = UploadValidator::periksa($_FILES['file'] ?? null);
        ResponseHelper::created('Gambar berhasil diunggah.', ['path' => FileHelper::simpanGambar($f['tmp'], $f['ext'])]);
    }
}
