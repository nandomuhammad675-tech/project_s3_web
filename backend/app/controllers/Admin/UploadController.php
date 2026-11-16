<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\FileHelper;
use App\Helpers\ResponseHelper;
use App\Repositories\MediaRepository;
use App\Validators\UploadValidator;

final class UploadController
{
    /**
     * POST /api/admin/upload (multipart/form-data): "file" (gambar), "jenis" (event|galeri|mading|guru|
     * ekstrakurikuler|program-pembiasaan), "nama" (mis. nama event).
     *  1. Gambar DISIMPAN DI DATABASE (tabel media_file).
     *  2. Salinan berkas ditulis ke website/public/assets/img/<jenis>/<nama-event>-<n>.<ext> (nama mengikuti nama event)
     *     sehingga website menampilkannya langsung.
     * Balasan: {path, di_folder_website}. Path itulah yang disimpan pada kolom gambar.
     */
    public function store(Request $req): void
    {
        $f     = UploadValidator::periksa($_FILES['file'] ?? null);
        $jenis = is_string($_POST['jenis'] ?? null) ? (string) $_POST['jenis'] : 'galeri';
        $nama  = is_string($_POST['nama'] ?? null) ? mb_substr(trim((string) $_POST['nama']), 0, 100) : '';

        $data = file_get_contents($f['tmp']);
        if ($data === false || $data === '') {
            throw HttpException::unprocessable('Berkas gambar tidak dapat dibaca.');
        }
        $mime = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][$f['ext']];
        $jenis = in_array($jenis, FileHelper::JENIS, true) ? $jenis : 'galeri';
        $path  = FileHelper::pathBaru($jenis, $nama, $f['ext']);

        try {
            (new MediaRepository())->simpan($path, $jenis, $mime, $data);
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'max_allowed_packet') || (int) ($e->errorInfo[1] ?? 0) === 1153) {
                throw HttpException::unprocessable('Gambar tidak dapat disimpan ke database. Perbesar max_allowed_packet MySQL (minimal 8M) atau kecilkan gambar.');
            }
            throw $e;
        }

        $diWebsite = FileHelper::tulisKeWebsite($path, $data);
        ResponseHelper::created('Gambar berhasil diunggah.', ['path' => $path, 'di_folder_website' => $diWebsite]);
    }
}
