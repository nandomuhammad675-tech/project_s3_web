<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use App\Validators\AuthValidator;

final class AuthController
{
    public function login(Request $req): void
    {
        $body     = $req->json();
        $username = trim(AuthValidator::wajibString($body, 'username', 'Username'));
        $password = AuthValidator::wajibString($body, 'password', 'Password');

        ResponseHelper::success('Login berhasil.', (new AuthService())->login($username, $password, $req->ip));
    }

    /** Logout hanya di sisi klien (token dihapus); token tidak dicabut di server (FR-AUTH-05). */
    public function logout(Request $req): void
    {
        ResponseHelper::success('Logout berhasil. Hapus token pada perangkat Anda.');
    }

    public function changePassword(Request $req): void
    {
        $body = $req->json();
        $lama = AuthValidator::wajibString($body, 'password_lama', 'Password lama');
        $baru = AuthValidator::wajibString($body, 'password_baru', 'Password baru');
        $auth = $req->get('auth');

        $data = (new AuthService())->changePassword((int) $auth['id_user'], $lama, $baru, $req->ip);
        ResponseHelper::success('Password berhasil diganti. Gunakan token baru ini.', $data);
    }

    /** SEMENTARA untuk menguji middleware; digantikan GET /api/guru/me di langkah 4. */
    public function me(Request $req): void
    {
        ResponseHelper::success('OK', $req->get('auth'));
    }
}
