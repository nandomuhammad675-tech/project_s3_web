<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\GaleriKegiatanRepository;
use App\Validators\KontenValidator;

/** Foto kegiatan: terkait event atau mandiri (id_event NULL). */
final class GaleriService
{
    private GaleriKegiatanRepository $repo;

    public function __construct(?GaleriKegiatanRepository $repo = null)
    {
        $this->repo = $repo ?? new GaleriKegiatanRepository();
    }

    public function daftar(?int $idEvent, bool $mandiri, array $p): array
    {
        return Pagination::hasil($this->repo->daftar($idEvent, $mandiri, $p['limit'], $p['offset']), $this->repo->total($idEvent, $mandiri), $p);
    }

    public function publikMandiri(array $p): array
    {
        return Pagination::hasil($this->repo->mandiriPublik($p['limit'], $p['offset']), $this->repo->totalMandiri(), $p);
    }

    public function detail(int $id): array
    {
        return $this->repo->cari($id) ?? throw HttpException::notFound('Foto galeri tidak ditemukan.');
    }

    /** id_event tidak ada => 422 (FK 1452). */
    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $d = KontenValidator::galeri($v, true);
        $v->selesai();
        return $this->detail($this->repo->buat($d['id_event'], (string) $d['file_gambar'], $d['keterangan']));
    }

    public function ubah(int $id, array $in): array
    {
        $this->detail($id);
        $v = new Validasi($in);
        $d = KontenValidator::galeri($v, false);
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
