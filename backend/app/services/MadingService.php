<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\GaleriMadingRepository;
use App\Validators\KontenValidator;

/** Mading / karya siswa (FR-CONT-05). */
final class MadingService
{
    private GaleriMadingRepository $repo;

    public function __construct(?GaleriMadingRepository $repo = null)
    {
        $this->repo = $repo ?? new GaleriMadingRepository();
    }

    public function daftar(array $p): array
    {
        return Pagination::hasil($this->repo->daftar($p['limit'], $p['offset']), $this->repo->total(), $p);
    }

    public function publik(array $p): array
    {
        return Pagination::hasil($this->repo->daftarPublik($p['limit'], $p['offset']), $this->repo->totalPublik(), $p);
    }

    public function detail(int $id): array
    {
        return $this->repo->cari($id) ?? throw HttpException::notFound('Karya mading tidak ditemukan.');
    }

    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $d = KontenValidator::mading($v, true);
        $v->selesai();
        return $this->detail($this->repo->buat($d));
    }

    public function ubah(int $id, array $in): array
    {
        $this->detail($id);
        $v = new Validasi($in);
        $d = KontenValidator::mading($v, false);
        $v->selesai();
        $this->repo->ubah($id, $d);
        return $this->detail($id);
    }

    public function hapus(int $id): void
    {
        $this->detail($id);
        $this->repo->hapus($id);
    }
}
