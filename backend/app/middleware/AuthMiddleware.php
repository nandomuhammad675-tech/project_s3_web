<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\JwtHelper;
use App\Repositories\AkunPenggunaRepository;

/**
 * Autentikasi Admin/Guru. Dijalankan di SETIAP permintaan (TDD §3.1):
 *  - token valid dan bertipe "internal"
 *  - akun masih aktif (akun nonaktif langsung ditolak)
 *  - token terbit tidak lebih awal dari kata_sandi_diubah_pada (resolusi detik; tanda "<" disengaja)
 *  - role cocok bila rute membatasi role: [AuthMiddleware::class, 'admin']
 * Role dan scope selalu dari database, bukan dari klaim token.
 */
final class AuthMiddleware
{
    /** @var string[] */
    private array $roles;

    public function __construct(string ...$roles)
    {
        $this->roles = $roles;
    }

    public function handle(Request $req): void
    {
        $token = $req->bearerToken();
        if ($token === null) {
            throw HttpException::unauthorized('Token tidak ditemukan.');
        }

        $claims = JwtHelper::verify($token);
        if (($claims['typ'] ?? null) !== 'internal' || !isset($claims['sub'], $claims['iat'])) {
            throw HttpException::unauthorized('Token tidak valid atau sudah kedaluwarsa.');
        }

        $akun = (new AkunPenggunaRepository())->cariById((int) $claims['sub']);
        if ($akun === null || !$akun->aktif()) {
            throw HttpException::unauthorized('Token tidak valid atau sudah kedaluwarsa.');
        }
        if ($akun->kataSandiDiubahTs !== null && (int) $claims['iat'] < $akun->kataSandiDiubahTs) {
            throw HttpException::unauthorized('Token tidak valid atau sudah kedaluwarsa.');
        }

        if ($this->roles && !in_array($akun->role, $this->roles, true)) {
            throw HttpException::forbidden();
        }

        $req->set('auth', ['id_user' => $akun->idUser, 'username' => $akun->username, 'role' => $akun->role]);
    }
}
