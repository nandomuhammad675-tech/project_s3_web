<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Core\Config;
use App\Repositories\MediaRepository;

final class FileHelper
{
    /** Jenis gambar yang diizinkan = nama folder di website/public/assets/img/. */
    public const JENIS = ['event', 'galeri', 'mading', 'guru', 'ekstrakurikuler', 'program-pembiasaan'];

    /** Pola path gambar yang sah pada kolom gambar. */
    public const POLA_PATH = '#^assets/img/(event|galeri|mading|guru|ekstrakurikuler|program-pembiasaan)/[a-z0-9-]{1,100}\.(jpg|png|webp)$#';

    public static function slug(string $s, int $maks = 60): string
    {
        $s = strtolower((string) (function_exists('iconv') ? @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) : $s));
        $s = trim((string) preg_replace('/[^a-z0-9]+/', '-', $s), '-');
        $s = rtrim(substr($s, 0, $maks), '-');
        return $s !== '' ? $s : 'umum';
    }

    /**
     * Folder public/ milik website (tujuan salinan berkas gambar). Default: ../website/public relatif terhadap
     * folder backend (susunan XAMPP htdocs/si-kesa/{backend,website}). Ubah lewat WEBSITE_PUBLIC_DIR di .env.
     */
    public static function folderWebsite(): string
    {
        $env = trim((string) Config::env('WEBSITE_PUBLIC_DIR', ''));
        return rtrim($env !== '' ? $env : dirname(__DIR__, 3) . '/website/public', '/\\');
    }

    /**
     * Tentukan path baru: assets/img/<jenis>/<nama-event>-<n>.<ext>; n = nomor berikutnya yang belum dipakai
     * (dicek di folder website dan di database). Nama berkas dari klien tidak dipakai.
     */
    public static function pathBaru(string $jenis, string $nama, string $ext): string
    {
        $jenis = in_array($jenis, self::JENIS, true) ? $jenis : 'galeri';
        $slug  = self::slug($nama !== '' ? $nama : $jenis);
        $dir   = self::folderWebsite() . '/assets/img/' . $jenis;
        $media = new MediaRepository();
        for ($n = 1; $n < 10000; $n++) {
            $path = 'assets/img/' . $jenis . '/' . $slug . '-' . $n . '.' . $ext;
            if (!is_file($dir . '/' . $slug . '-' . $n . '.' . $ext) && !$media->ada($path)) {
                return $path;
            }
        }
        throw new \RuntimeException('Nomor berkas habis.');
    }

    /** Tulis salinan berkas ke folder website. @return bool false bila folder tidak dapat ditulis (data tetap ada di database) */
    public static function tulisKeWebsite(string $path, string $data): bool
    {
        if (!preg_match(self::POLA_PATH, $path)) {
            return false;
        }
        $file = self::folderWebsite() . '/' . $path;
        $dir  = dirname($file);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            LogHelper::error('Folder gambar website tidak dapat dibuat: ' . $dir);
            return false;
        }
        if (@file_put_contents($file, $data) === false) {
            LogHelper::error('Berkas gambar website tidak dapat ditulis: ' . $file);
            return false;
        }
        return true;
    }

    /** Hapus gambar: baris database dan salinan berkas di folder website (hanya path yang sah). */
    public static function hapus(?string $path): void
    {
        if ($path === null || !preg_match(self::POLA_PATH, $path)) {
            return;
        }
        (new MediaRepository())->hapus($path);
        $f = self::folderWebsite() . '/' . $path;
        if (is_file($f)) {
            @unlink($f);
        }
    }
}
