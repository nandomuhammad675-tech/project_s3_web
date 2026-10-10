<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Validasi;
use App\Repositories\PengaturanSistemRepository;
use App\Validators\KontenValidator;

/**
 * Pengaturan publik yang dikelola Admin: hanya URL (bukan file). Hubungi Kami TIDAK di sini (statis di kode).
 * Daftar kunci = daftar putih; kunci lain di tabel tidak pernah keluar lewat endpoint publik.
 */
final class PengaturanService
{
    public const KUNCI = ['ppdb_form_url', 'video_profil_url'];

    private PengaturanSistemRepository $repo;

    public function __construct(?PengaturanSistemRepository $repo = null)
    {
        $this->repo = $repo ?? new PengaturanSistemRepository();
    }

    /** GET /api/public/pengaturan */
    public function publik(): array
    {
        $baris = $this->repo->untukKunci(self::KUNCI);
        $hasil = [];
        foreach (self::KUNCI as $k) {
            $hasil[$k] = $baris[$k]['nilai'] ?? null;
        }
        return $hasil;
    }

    /** GET /api/admin/pengaturan: nilai + waktu dan pengguna yang terakhir mengubah. */
    public function admin(): array
    {
        $baris = $this->repo->untukKunci(self::KUNCI);
        $hasil = [];
        foreach (self::KUNCI as $k) {
            $hasil[$k] = [
                'nilai'       => $baris[$k]['nilai'] ?? null,
                'diubah_pada' => $baris[$k]['diubah_pada'] ?? null,
                'diubah_oleh' => $baris[$k]['diubah_oleh'] ?? null,
            ];
        }
        return $hasil;
    }

    /** Parsial: hanya kunci yang dikirim; null/kosong menghapus nilai. */
    public function ubah(int $idUser, array $in): array
    {
        $v = new Validasi($in);
        $d = [];
        foreach (self::KUNCI as $k) {
            if ($v->ada($k)) {
                $d[$k] = KontenValidator::url($v, $k, $k === 'ppdb_form_url' ? 'Link Google Form PPDB' : 'URL video profil');
            }
        }
        if (!$d) {
            $v->error('ppdb_form_url', 'Kirim minimal satu: ppdb_form_url atau video_profil_url.');
        }
        $v->selesai();

        Database::transaksi(function () use ($d, $idUser): void {
            foreach ($d as $k => $nilai) {
                $this->repo->simpan($k, $nilai, $idUser);
            }
        });
        return $this->admin();
    }
}
