<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\AkunPenggunaRepository;
use App\Validators\AkunValidator;

/** Manajemen akun internal oleh Admin (FR-AUTH-10, FR-AUTH-12). Akun tidak pernah dihapus. */
final class AkunService
{
    private AkunPenggunaRepository $repo;

    public function __construct(?AkunPenggunaRepository $repo = null)
    {
        $this->repo = $repo ?? new AkunPenggunaRepository();
    }

    public function daftar(array $p): array
    {
        return Pagination::hasil($this->repo->daftar($p['limit'], $p['offset']), $this->repo->total(), $p);
    }

    /** Hanya akun Admin; akun Guru dibuat bersama data Guru (FR-GURU-01). */
    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $username = AkunValidator::username($v);
        $password = AkunValidator::password($v, 'password');
        $role     = $v->enum('role', 'Role', ['admin', 'guru'], false) ?? 'admin';
        $v->selesai();

        if ($role !== 'admin') {
            throw HttpException::unprocessable(
                'Akun Guru dibuat bersama data Guru melalui /api/admin/guru. Endpoint ini hanya untuk akun Admin.',
                ['role' => 'Hanya role admin yang dapat dibuat di sini.']
            );
        }

        $id = $this->repo->buat((string) $username, password_hash((string) $password, PASSWORD_DEFAULT), 'admin');
        return (array) $this->repo->ringkas($id);
    }

    public function ubahStatus(int $idPemanggil, int $idTarget, array $in): array
    {
        $v = new Validasi($in);
        $status = $v->enum('status_akun', 'Status akun', ['aktif', 'nonaktif'], true);
        $v->selesai();

        Database::transaksi(function () use ($idPemanggil, $idTarget, $status): void {
            // kunci dulu, baru periksa: dua permintaan bersamaan tidak boleh sama-sama lolos
            $adminAktif = $this->repo->hitungAdminAktifKunci();
            $target = $this->repo->cariById($idTarget) ?? throw HttpException::notFound('Akun tidak ditemukan.');

            if ($status === 'nonaktif') {
                if ($idTarget === $idPemanggil) {
                    throw HttpException::unprocessable('Anda tidak dapat menonaktifkan akun Anda sendiri.');
                }
                if ($target->role === 'admin' && $target->aktif() && $adminAktif <= 1) {
                    throw HttpException::unprocessable('Tidak dapat menonaktifkan Admin aktif terakhir.');
                }
            }
            // Sengaja TIDAK memeriksa apakah Guru masih menjadi wali kelas (keputusan URS Lampiran B butir 5);
            // kelas dengan wali nonaktif ditolak saat kenaikan kelas.
            $this->repo->ubahStatus($idTarget, (string) $status);
        });

        return (array) $this->repo->ringkas($idTarget);
    }

    /** Reset password akun LAIN tanpa password lama. Akun sendiri harus lewat PUT /api/auth/password. */
    public function resetPassword(int $idPemanggil, int $idTarget, array $in): void
    {
        $v = new Validasi($in);
        $password = AkunValidator::password($v, 'password_baru');
        $v->selesai();

        if ($idTarget === $idPemanggil) {
            throw HttpException::unprocessable(
                'Reset oleh Admin tidak dapat dipakai untuk akun sendiri. Gunakan PUT /api/auth/password dengan password lama.'
            );
        }
        if ($this->repo->cariById($idTarget) === null) {
            throw HttpException::notFound('Akun tidak ditemukan.');
        }
        $this->repo->ubahPassword($idTarget, password_hash((string) $password, PASSWORD_DEFAULT));
    }
}
