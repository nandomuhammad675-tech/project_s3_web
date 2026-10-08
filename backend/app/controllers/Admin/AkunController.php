<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\ResponseHelper;
use App\Services\AkunService;

final class AkunController
{
    public function index(Request $req): void
    {
        ResponseHelper::success('OK', (new AkunService())->daftar(Pagination::dari($req)));
    }

    public function store(Request $req): void
    {
        ResponseHelper::created('Akun Admin berhasil dibuat.', (new AkunService())->buat($req->json()));
    }

    public function ubahStatus(Request $req): void
    {
        $pemanggil = (int) $req->get('auth')['id_user'];
        ResponseHelper::success('Status akun diperbarui.', (new AkunService())->ubahStatus($pemanggil, $req->id(), $req->json()));
    }

    public function resetPassword(Request $req): void
    {
        $pemanggil = (int) $req->get('auth')['id_user'];
        (new AkunService())->resetPassword($pemanggil, $req->id(), $req->json());
        ResponseHelper::success('Password akun berhasil diganti. Token lama akun tersebut tidak berlaku lagi.');
    }
}
