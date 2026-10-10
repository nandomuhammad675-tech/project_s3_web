<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Repositories\AkademikAdminRepository;
use App\Repositories\TahunAjaranRepository;

/** Akses baca Admin ke absensi, nilai, kedisiplinan seluruh kelas. Tidak ada jalur tulis. */
final class AkademikAdminService
{
    /** Tahun ajaran default = yang aktif; id_tahun_ajaran yang tidak ada => 404. */
    public function daftar(string $jenis, array $f, array $p): array
    {
        $tahun = $f['id_tahun_ajaran'] === null
            ? (new TahunAjaranRepository())->aktif()
            : ((new TahunAjaranRepository())->cari($f['id_tahun_ajaran'])
                ?? throw HttpException::notFound('Tahun ajaran tidak ditemukan.'));

        if ($tahun === null) {   // belum ada tahun ajaran aktif
            return Pagination::hasil([], 0, $p) + ['tahun_ajaran' => null];
        }

        $f['id_tahun_ajaran'] = $tahun->idTahunAjaran;
        $repo = new AkademikAdminRepository();
        return Pagination::hasil($repo->daftar($jenis, $f, $p['limit'], $p['offset']), $repo->total($jenis, $f), $p)
            + ['tahun_ajaran' => $tahun->namaTahun];
    }
}
