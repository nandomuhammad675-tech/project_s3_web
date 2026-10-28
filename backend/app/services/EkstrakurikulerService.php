<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\EkstrakurikulerRepository;
use App\Validators\KontenValidator;

final class EkstrakurikulerService
{
    private EkstrakurikulerRepository $repo;

    public function __construct(?EkstrakurikulerRepository $repo = null)
    {
        $this->repo = $repo ?? new EkstrakurikulerRepository();
    }

    public function daftar(array $p): array
    {
        return Pagination::hasil($this->repo->daftar($p['limit'], $p['offset']), $this->repo->total(), $p);
    }

    public function publik(): array
    {
        return $this->repo->semua();
    }

    public function detail(int $id): array
    {
        return $this->repo->cari($id) ?? throw HttpException::notFound('Ekstrakurikuler tidak ditemukan.');
    }

    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $d = KontenValidator::ekskul($v, true);
        $v->selesai();
        return $this->detail($this->repo->buat($d));
    }

    public function ubah(int $id, array $in): array
    {
        $this->detail($id);
        $v = new Validasi($in);
        $d = KontenValidator::ekskul($v, false);
        $v->selesai();
        $this->repo->ubah($id, $d);
        return $this->detail($id);
    }

    /** Relasi Guru-ekskul ikut terhapus (CASCADE). */
    public function hapus(int $id): void
    {
        $this->detail($id);
        $this->repo->hapus($id);
    }
}
