<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Validasi;
use App\Repositories\ProgramPembiasaanRepository;
use App\Validators\KontenValidator;

/** Enam program pembiasaan: Admin hanya melihat dan mengubah isinya (tidak ada tambah/hapus; hari tetap). */
final class ProgramPembiasaanService
{
    private ProgramPembiasaanRepository $repo;

    public function __construct(?ProgramPembiasaanRepository $repo = null)
    {
        $this->repo = $repo ?? new ProgramPembiasaanRepository();
    }

    public function daftar(): array
    {
        return $this->repo->daftar();
    }

    public function publik(): array
    {
        return $this->repo->publik();
    }

    public function detail(int $id): array
    {
        return $this->repo->cari($id) ?? throw HttpException::notFound('Program pembiasaan tidak ditemukan.');
    }

    public function ubah(int $id, array $in): array
    {
        $this->detail($id);
        $v = new Validasi($in);
        $d = KontenValidator::program($v);
        if (!$d) {
            $v->error('nama_program', 'Kirim minimal satu kolom yang diubah: nama_program, deskripsi, atau ikon.');
        }
        $v->selesai();

        $this->repo->ubah($id, $d);
        return $this->detail($id);
    }
}
