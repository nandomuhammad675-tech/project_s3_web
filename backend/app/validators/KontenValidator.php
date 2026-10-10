<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

/** Validasi konten website. Semua metode: $baru true = kolom wajib/semua disertakan; false = hanya yang dikirim (ubah parsial). */
final class KontenValidator
{
    public const KATEGORI_EVENT = ['kegiatan', 'event', 'prestasi', 'kolaborasi'];

    /**
     * Gambar: path hasil POST /api/admin/upload ("uploads/<32 hex>.jpg|png|webp") atau URL https.
     * Database hanya menyimpan path/URL (TDD §9).
     */
    public static function gambar(Validasi $v, string $k, string $label, bool $wajib): ?string
    {
        $s = $v->string($k, $label, $wajib, 255);
        if ($s === null) {
            return null;
        }
        if (!preg_match('#^(uploads/[a-f0-9]{32}\.(jpg|png|webp)|https://[^\s<>"\']+)$#', $s)) {
            $v->error($k, $label . ' harus berupa path hasil unggah (uploads/...) atau URL https.');
            return null;
        }
        return $s;
    }

    /** URL http/https (menolak javascript:, data:, dll). */
    public static function url(Validasi $v, string $k, string $label): ?string
    {
        $s = $v->string($k, $label, false, 500);
        if ($s === null) {
            return null;
        }
        if (!preg_match('#^https?://#i', $s) || filter_var($s, FILTER_VALIDATE_URL) === false) {
            $v->error($k, $label . ' harus berupa URL http atau https yang valid.');
            return null;
        }
        return $s;
    }

    public static function ekskul(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru || $v->ada('nama_ekskul')) {
            $d['nama_ekskul'] = $v->string('nama_ekskul', 'Nama ekstrakurikuler', true, 50);
        }
        if ($baru || $v->ada('deskripsi')) {
            $d['deskripsi'] = $v->string('deskripsi', 'Deskripsi', false, 5000);
        }
        if ($baru || $v->ada('ikon')) {
            $d['ikon'] = self::gambar($v, 'ikon', 'Ikon', false);
        }
        return $d;
    }

    public static function event(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru || $v->ada('nama_event')) {
            $d['nama_event'] = $v->string('nama_event', 'Nama kegiatan', true, 100);
        }
        if ($baru || $v->ada('kategori')) {
            $d['kategori'] = $v->enum('kategori', 'Kategori', self::KATEGORI_EVENT, true);
        }
        if ($baru || $v->ada('tanggal')) {
            $d['tanggal'] = $v->tanggal('tanggal', 'Tanggal', false);
        }
        if ($baru || $v->ada('deskripsi')) {
            $d['deskripsi'] = $v->string('deskripsi', 'Deskripsi', false, 5000);
        }
        if ($baru || $v->ada('banner')) {
            $d['banner'] = self::gambar($v, 'banner', 'Banner', false);
        }
        if ($baru) {
            $d['status_tampil'] = $v->bool('status_tampil', 'Status tampil', true);
        } elseif ($v->ada('status_tampil')) {
            $d['status_tampil'] = $v->bool('status_tampil', 'Status tampil', null);
        }
        return $d;
    }

    /** Galeri: id_event boleh null (galeri mandiri). */
    public static function galeri(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru || $v->ada('id_event')) {
            $d['id_event'] = $v->bilangan('id_event', 'Event', false, 1, 4294967295);
        }
        if ($baru || $v->ada('file_gambar')) {
            $d['file_gambar'] = self::gambar($v, 'file_gambar', 'Gambar', true);
        }
        if ($baru || $v->ada('keterangan')) {
            $d['keterangan'] = $v->string('keterangan', 'Keterangan', false, 255);
        }
        return $d;
    }

    public static function mading(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru || $v->ada('judul_karya')) {
            $d['judul_karya'] = $v->string('judul_karya', 'Judul karya', true, 100);
        }
        if ($baru || $v->ada('kategori')) {
            $d['kategori'] = $v->string('kategori', 'Kategori', false, 50);
        }
        if ($baru || $v->ada('deskripsi')) {
            $d['deskripsi'] = $v->string('deskripsi', 'Deskripsi', false, 5000);
        }
        if ($baru || $v->ada('nama_penulis')) {
            $d['nama_penulis'] = $v->string('nama_penulis', 'Nama penulis', false, 100);
        }
        if ($baru || $v->ada('file_gambar')) {
            $d['file_gambar'] = self::gambar($v, 'file_gambar', 'Gambar', true);
        }
        if ($baru) {
            $d['status_tampil'] = $v->bool('status_tampil', 'Status tampil', true);
        } elseif ($v->ada('status_tampil')) {
            $d['status_tampil'] = $v->bool('status_tampil', 'Status tampil', null);
        }
        return $d;
    }

    /** Program pembiasaan: hari tetap, hanya nama/deskripsi/ikon yang dapat diubah. */
    public static function program(Validasi $v): array
    {
        $d = [];
        if ($v->ada('nama_program')) {
            $d['nama_program'] = $v->string('nama_program', 'Nama program', true, 50);
        }
        if ($v->ada('deskripsi')) {
            $d['deskripsi'] = $v->string('deskripsi', 'Deskripsi', false, 255);
        }
        if ($v->ada('ikon')) {
            $d['ikon'] = self::gambar($v, 'ikon', 'Ikon', false);
        }
        return $d;
    }
}
