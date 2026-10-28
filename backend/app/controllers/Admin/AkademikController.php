<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\AkademikAdminService;
use App\Validators\AkademikValidator;

/**
 * Admin membaca data akademik Guru: HANYA GET (read-only). Rute tulis sengaja tidak ada.
 * Filter: id_tahun_ajaran (default aktif), id_kelas, id_siswa, semester, + khusus tiap jenis (lihat AkademikValidator).
 */
final class AkademikController
{
    public function absensi(Request $req): void
    {
        $this->daftar($req, 'absensi');
    }

    public function nilai(Request $req): void
    {
        $this->daftar($req, 'nilai');
    }

    public function disiplin(Request $req): void
    {
        $this->daftar($req, 'disiplin');
    }

    private function daftar(Request $req, string $jenis): void
    {
        ResponseHelper::success('OK', (new AkademikAdminService())->daftar(
            $jenis, AkademikValidator::filter($req, $jenis), Pagination::dari($req)
        ));
    }
}
