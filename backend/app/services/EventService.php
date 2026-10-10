<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\EventSekolahRepository;
use App\Repositories\GaleriKegiatanRepository;
use App\Validators\KontenValidator;

/** Kegiatan / event / prestasi / kolaborasi beserta galeri terkait (FR-CONT-04). */
final class EventService
{
    private EventSekolahRepository $repo;
    private GaleriKegiatanRepository $galeri;

    public function __construct()
    {
        $this->repo   = new EventSekolahRepository();
        $this->galeri = new GaleriKegiatanRepository();
    }

    public function daftar(?string $kategori, array $p): array
    {
        return Pagination::hasil($this->repo->daftar($kategori, $p['limit'], $p['offset']), $this->repo->total($kategori), $p);
    }

    /** Publik: hanya event tampil, masing-masing membawa galerinya. */
    public function publik(?string $kategori, array $p): array
    {
        $items = $this->repo->daftarPublik($kategori, $p['limit'], $p['offset']);
        $peta  = $this->galeri->untukEvent(array_column($items, 'id_event'));
        foreach ($items as &$e) {
            $e['galeri'] = $peta[$e['id_event']] ?? [];
        }
        unset($e);
        return Pagination::hasil($items, $this->repo->totalPublik($kategori), $p);
    }

    public function detail(int $id): array
    {
        $e = $this->repo->cari($id) ?? throw HttpException::notFound('Kegiatan tidak ditemukan.');
        $e['galeri'] = $this->galeri->untukEvent([$id])[$id] ?? [];
        return $e;
    }

    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $d = KontenValidator::event($v, true);
        $v->selesai();
        return $this->detail($this->repo->buat($d));
    }

    public function ubah(int $id, array $in): array
    {
        $this->detail($id);
        $v = new Validasi($in);
        $d = KontenValidator::event($v, false);
        $v->selesai();
        $this->repo->ubah($id, $d);
        return $this->detail($id);
    }

    /** Galeri milik event ikut terhapus (CASCADE). */
    public function hapus(int $id): void
    {
        $this->detail($id);
        $this->repo->hapus($id);
    }
}
