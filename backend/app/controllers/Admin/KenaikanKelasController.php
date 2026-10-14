<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\KenaikanKelasService;

final class KenaikanKelasController
{
    /** POST /api/admin/kenaikan-kelas */
    public function store(Request $req): void
    {
        $idUser = (int) $req->get('auth')['id_user'];
        ResponseHelper::success('Kenaikan kelas berhasil dijalankan.', (new KenaikanKelasService())->jalankan($idUser, $req->json()));
    }

    /** GET /api/admin/kenaikan-kelas/log?tahun_ajaran_tujuan=ID */
    public function log(Request $req): void
    {
        $id = Pagination::angka($req, 'tahun_ajaran_tujuan');
        if ($id === null) {
            throw HttpException::badRequest('Parameter tahun_ajaran_tujuan wajib diisi.');
        }
        ResponseHelper::success('OK', (new KenaikanKelasService())->lihatLog($id, Pagination::dari($req)));
    }
}
