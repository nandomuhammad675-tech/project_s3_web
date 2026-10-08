<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\HariLiburRepository;
use App\Repositories\LiburTargetKelasRepository;

final class HariLiburService
{
    private HariLiburRepository $repo;
    private LiburTargetKelasRepository $target;

    public function __construct(?HariLiburRepository $repo = null, ?LiburTargetKelasRepository $target = null)
    {
        $this->repo   = $repo ?? new HariLiburRepository();
        $this->target = $target ?? new LiburTargetKelasRepository();
    }

    public function daftar(?string $dari, ?string $sampai, array $p): array
    {
        $rows  = $this->repo->daftar($dari, $sampai, $p['limit'], $p['offset']);
        $peta  = $this->target->untukBanyak(array_map(static fn(array $r): int => (int) $r['id_libur'], $rows));
        $items = array_map(static function (array $r) use ($peta): array {
            $r['id_libur'] = (int) $r['id_libur'];
            $r['id_kelas'] = $peta[$r['id_libur']] ?? [];
            return $r;
        }, $rows);

        return Pagination::hasil($items, $this->repo->total($dari, $sampai), $p);
    }

    public function detail(int $id): array
    {
        $r = $this->repo->cari($id) ?? throw HttpException::notFound('Hari libur tidak ditemukan.');
        $r['id_libur'] = (int) $r['id_libur'];
        $r['id_kelas'] = $this->target->untuk($id);   // kosong = berlaku untuk semua kelas
        return $r;
    }

    public function buat(array $in): array
    {
        [$tanggal, $ket, $kelas] = $this->validasi($in);

        $id = Database::transaksi(function () use ($tanggal, $ket, $kelas): int {
            $id = $this->repo->buat($tanggal, $ket);
            if ($kelas) {
                $this->target->ganti($id, $kelas);
            }
            return $id;
        });

        return $this->detail((int) $id);
    }

    /** id_kelas dikirim => target diganti; tidak dikirim => target tidak berubah. */
    public function ubah(int $id, array $in): array
    {
        $this->detail($id);
        [$tanggal, $ket, $kelas] = $this->validasi($in);

        Database::transaksi(function () use ($id, $tanggal, $ket, $kelas): void {
            $this->repo->ubah($id, $tanggal, $ket);
            if ($kelas !== null) {
                $this->target->ganti($id, $kelas);
            }
        });

        return $this->detail($id);
    }

    public function hapus(int $id): void
    {
        $this->detail($id);
        $this->repo->hapus($id);
    }

    /** @return array{0:string, 1:?string, 2:?array} */
    private function validasi(array $in): array
    {
        $v = new Validasi($in);
        $tanggal = $v->tanggal('tanggal_libur', 'Tanggal libur', true);
        $ket     = $v->string('keterangan', 'Keterangan', false, 100);
        $kelas   = $v->daftarId('id_kelas', 'Kelas target');
        $v->selesai();
        return [(string) $tanggal, $ket, $kelas];
    }
}
