<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\GuruEkskulRepository;

/** Relasi Guru - ekstrakurikuler (FR-CONT-03). */
final class GuruEkskulService
{
    private GuruEkskulRepository $repo;

    public function __construct(?GuruEkskulRepository $repo = null)
    {
        $this->repo = $repo ?? new GuruEkskulRepository();
    }

    public function daftar(?int $idGuru, ?int $idEkskul, array $p): array
    {
        return Pagination::hasil($this->repo->daftar($idGuru, $idEkskul, $p['limit'], $p['offset']), $this->repo->total($idGuru, $idEkskul), $p);
    }

    public function detail(int $id): array
    {
        return $this->repo->cari($id) ?? throw HttpException::notFound('Relasi Guru-ekstrakurikuler tidak ditemukan.');
    }

    /** Duplikat => 409 (UNIQUE); guru atau ekskul tidak ada => 422 (FK). */
    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $idGuru   = $v->bilangan('id_guru', 'Guru', true, 1, 4294967295);
        $idEkskul = $v->bilangan('id_ekskul', 'Ekstrakurikuler', true, 1, 255);
        $v->selesai();

        return $this->detail($this->repo->buat((int) $idGuru, (int) $idEkskul));
    }

    public function hapus(int $id): void
    {
        $this->detail($id);
        $this->repo->hapus($id);
    }
}
