<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\JwtHelper;
use App\Repositories\DataSiswaRepository;
use App\Services\AuthService;

/**
 * Autentikasi Wali Murid. Pada SETIAP permintaan memeriksa ulang bahwa siswa pada token
 * masih berstatus aktif dan punya penempatan kelas di tahun ajaran aktif (FR-AUTH-14).
 * Scope hanya dari id_siswa di token; id dari klien tidak pernah dipakai.
 */
final class WaliMiddleware
{
    public function handle(Request $req): void
    {
        $token = $req->bearerToken();
        if ($token === null) {
            throw HttpException::unauthorized(AuthService::PESAN_WALI_GAGAL);
        }

        $claims = JwtHelper::verify($token);
        if (($claims['typ'] ?? null) !== 'wali' || !isset($claims['id_siswa'])) {
            throw HttpException::unauthorized(AuthService::PESAN_WALI_GAGAL);
        }

        $siswa = (new DataSiswaRepository())->cariWaliAktifById((int) $claims['id_siswa']);
        if ($siswa === null) {
            throw HttpException::unauthorized(AuthService::PESAN_WALI_GAGAL);
        }

        $req->set('wali', ['id_siswa' => $siswa->idSiswa]);
    }
}
