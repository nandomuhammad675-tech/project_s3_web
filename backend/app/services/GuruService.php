<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\AkunPenggunaRepository;
use App\Repositories\DataGuruRepository;
use App\Validators\AkunValidator;
use App\Validators\GuruValidator;

/** Data Guru dan akun internalnya dikelola bersama (FR-GURU-01). Semua Guru adalah wali kelas (tanpa guru pengampu). */
final class GuruService
{
    private DataGuruRepository $repo;
    private AkunPenggunaRepository $akunRepo;

    public function __construct(?DataGuruRepository $repo = null, ?AkunPenggunaRepository $akunRepo = null)
    {
        $this->repo     = $repo ?? new DataGuruRepository();
        $this->akunRepo = $akunRepo ?? new AkunPenggunaRepository();
    }

    public function daftar(?string $q, array $p): array
    {
        return Pagination::hasil($this->repo->daftar($q, $p['limit'], $p['offset']), $this->repo->total($q), $p);
    }

    /** Untuk GET /api/public/guru. */
    public function publik(): array
    {
        return $this->repo->publik();
    }

    public function detail(int $id): array
    {
        return ($this->repo->cari($id) ?? throw HttpException::notFound('Guru tidak ditemukan.'))->toArray();
    }

    /** Membuat akun (role guru) dan data Guru dalam satu transaksi. */
    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $username = AkunValidator::username($v);
        $password = AkunValidator::password($v, 'password');
        $d = GuruValidator::bidang($v, true);
        $v->selesai();

        $idGuru = Database::transaksi(function () use ($username, $password, $d): int {
            $idUser = $this->akunRepo->buat((string) $username, password_hash((string) $password, PASSWORD_DEFAULT), 'guru');
            return $this->repo->buat($idUser, $d);
        });

        return $this->detail((int) $idGuru);
    }

    /** Ubah parsial: hanya kolom yang dikirim. Username/password/status akun lewat /api/admin/akun. */
    public function ubah(int $id, array $in): array
    {
        $this->detail($id);
        $v = new Validasi($in);
        $d = GuruValidator::bidang($v, false);
        $v->selesai();

        $this->repo->ubah($id, $d);
        return $this->detail($id);
    }

    /**
     * Hapus Guru: ditolak (409) bila masih menjadi wali kelas pada tahun ajaran mana pun, dan oleh database
     * (1451 => 409) bila sudah punya data akademik; dalam kasus itu nonaktifkan akunnya. Bila berhasil,
     * data_guru dihapus lebih dulu, baru akunnya (urutan TDD §5.3).
     */
    public function hapus(int $id): void
    {
        $g = $this->repo->cari($id) ?? throw HttpException::notFound('Guru tidak ditemukan.');

        if ($this->repo->adaKelasWali($id)) {
            throw HttpException::conflict('Guru masih menjadi wali kelas. Lepas penetapan wali pada kelas terlebih dahulu.');
        }

        Database::transaksi(function () use ($id, $g): void {
            $this->repo->hapus($id);
            $this->akunRepo->hapusAkunGuru($g->idUser);
        });
    }
}
