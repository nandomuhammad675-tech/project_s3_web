<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\MataPelajaranRepository;

final class MapelService
{
    private MataPelajaranRepository $repo;

    public function __construct(?MataPelajaranRepository $repo = null)
    {
        $this->repo = $repo ?? new MataPelajaranRepository();
    }

    public function daftar(array $p): array
    {
        return Pagination::hasil($this->repo->daftar($p['limit'], $p['offset']), $this->repo->total(), $p);
    }

    public function detail(int $id): array
    {
        return $this->repo->cari($id) ?? throw HttpException::notFound('Mata pelajaran tidak ditemukan.');
    }

    public function buat(array $in): array
    {
        [$kode, $nama] = $this->validasi($in);
        return $this->detail($this->repo->buat($kode, $nama));
    }

    public function ubah(int $id, array $in): array
    {
        $this->detail($id);
        [$kode, $nama] = $this->validasi($in);
        $this->repo->ubah($id, $kode, $nama);
        return $this->detail($id);
    }

    /** Ditolak database (409) bila masih dipakai nilai atau jadwal. */
    public function hapus(int $id): void
    {
        $this->detail($id);
        $this->repo->hapus($id);
    }

    /** @return array{0:?string, 1:string} */
    private function validasi(array $in): array
    {
        $v = new Validasi($in);
        $kode = $v->string('kode_mapel', 'Kode mata pelajaran', false, 10);
        $nama = $v->string('nama_mapel', 'Nama mata pelajaran', true, 50);
        $v->selesai();
        return [$kode, (string) $nama];
    }
}
