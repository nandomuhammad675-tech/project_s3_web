<?php
declare(strict_types=1);

namespace App\Controllers\Wali;

use App\Core\Request;
use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use App\Validators\AuthValidator;

final class AuthController
{
    public function login(Request $req): void
    {
        $nisn = trim(AuthValidator::wajibString($req->json(), 'nisn', 'NISN'));
        ResponseHelper::success('Login berhasil.', (new AuthService())->loginWali($nisn, $req->ip));
    }
}
