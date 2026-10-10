<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Core\HttpException;

final class FileHelper
{
    /**
     * Simpan gambar yang sudah divalidasi ke public/uploads dengan nama acak (nama dari klien tidak dipakai).
     * @return string path relatif "uploads/<32 hex>.<ext>" yang disimpan di kolom gambar
     */
    public static function simpanGambar(string $tmp, string $ext): string
    {
        $dir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            LogHelper::error('Folder unggahan tidak dapat dibuat: ' . $dir);
            throw new \RuntimeException('Folder unggahan tidak tersedia.');
        }

        $nama = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($tmp, $dir . '/' . $nama)) {
            LogHelper::error('move_uploaded_file gagal ke ' . $dir);
            throw new \RuntimeException('Berkas tidak dapat disimpan.');
        }
        return 'uploads/' . $nama;
    }
}
