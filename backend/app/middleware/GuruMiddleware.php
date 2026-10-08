<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;
use App\Repositories\DataGuruRepository;

/**
 * Scope Guru (TDD §3.1, §4). Dijalankan SETELAH AuthMiddleware('guru').
 * Kelas Guru SELALU diambil dari database (kelas.id_guru_wali pada tahun ajaran aktif),
 * tidak pernah dari parameter, body, atau klaim token (AC-15).
 *
 *   [GuruMiddleware::class]               => wajib punya kelas, bila tidak 403
 *   [GuruMiddleware::class, 'opsional']   => tetap lanjut dengan kelas = null (untuk GET /api/guru/me)
 */
final class GuruMiddleware
{
    public function __construct(private string $mode = 'wajib') {}

    public function handle(Request $req): void
    {
        $auth = $req->get('auth');
        if (!is_array($auth)) {
            throw HttpException::unauthorized();   // salah pasang: harus setelah AuthMiddleware
        }

        $k = (new DataGuruRepository())->konteksAktif((int) $auth['id_user']);
        if ($k === null) {
            throw HttpException::forbidden('Akun ini belum memiliki data guru.');
        }

        if ($this->mode !== 'opsional' && $k['id_kelas'] === null) {
            throw HttpException::forbidden(
                $k['id_tahun_ajaran'] === null
                    ? 'Belum ada tahun ajaran aktif.'
                    : 'Anda belum ditetapkan sebagai wali kelas pada tahun ajaran aktif.'
            );
        }

        $req->set('guru', $k);
    }
}
