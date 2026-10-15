<?php
declare(strict_types=1);

namespace App\Validators;

use App\Core\HttpException;

/** Validasi unggahan gambar (multipart, field "file"). Jenis ditentukan dari ISI berkas, bukan nama/ekstensi dari klien. */
final class UploadValidator
{
    public const MAKS_BYTE = 2 * 1024 * 1024;   // 2 MB

    private const TIPE = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];

    /** @return array{tmp:string, ext:string} */
    public static function periksa(?array $f): array
    {
        if ($f === null || !isset($f['tmp_name'], $f['error']) || is_array($f['error'])) {
            throw HttpException::unprocessable('Kirim berkas gambar pada field "file" (multipart/form-data).', ['file' => 'Berkas wajib dikirim.']);
        }

        if ($f['error'] !== UPLOAD_ERR_OK) {
            $pesan = in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'Ukuran gambar maksimal 2 MB.'
                : 'Unggahan gagal. Coba lagi.';
            throw HttpException::unprocessable($pesan, ['file' => $pesan]);
        }
        if (!is_uploaded_file($f['tmp_name'])) {
            throw HttpException::badRequest('Berkas tidak valid.');
        }
        if ((int) $f['size'] > self::MAKS_BYTE) {
            throw HttpException::unprocessable('Ukuran gambar maksimal 2 MB.', ['file' => 'Ukuran gambar maksimal 2 MB.']);
        }

        $info = @getimagesize($f['tmp_name']);
        $ext  = $info === false ? null : (self::TIPE[$info[2]] ?? null);
        if ($ext === null) {
            throw HttpException::unprocessable('Berkas harus berupa gambar JPG, PNG, atau WEBP.', ['file' => 'Format gambar tidak didukung.']);
        }
        return ['tmp' => (string) $f['tmp_name'], 'ext' => $ext];
    }
}
