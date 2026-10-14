<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\DataSiswaRepository;
use App\Validators\SiswaValidator;

final class SiswaService
{
    private DataSiswaRepository $repo;

    public function __construct(?DataSiswaRepository $repo = null)
    {
        $this->repo = $repo ?? new DataSiswaRepository();
    }

    public function daftar(?string $q, ?string $status, array $p): array
    {
        return Pagination::hasil($this->repo->daftarAdmin($q, $status, $p['limit'], $p['offset']), $this->repo->totalAdmin($q, $status), $p);
    }

    public function detail(int $id): array
    {
        return $this->repo->detailAdmin($id) ?? throw HttpException::notFound('Siswa tidak ditemukan.');
    }

    /** Siswa baru (hasil PPDB atau pindahan) SELALU berstatus "baru" sampai ditempatkan ke kelas (FR-SISWA-03). */
    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $d = SiswaValidator::bidang($v, true);
        if ($v->ada('status_siswa') && ($in['status_siswa'] ?? null) !== 'baru') {
            $v->error('status_siswa', 'Siswa baru selalu berstatus baru; status berubah otomatis saat ditempatkan.');
        }
        $v->selesai();

        $id = $this->repo->buat((string) $d['nama_siswa'], (string) $d['nisn_resmi'], $d['nama_wali'], $d['no_hp_wali']);
        return $this->detail($id);
    }

    /**
     * Ubah parsial. Satu-satunya perubahan status manual yang diizinkan: baru atau aktif menjadi "pindah"
     * (TDD §5.5). Status lain berubah otomatis oleh penempatan dan kenaikan kelas.
     */
    public function ubah(int $id, array $in): array
    {
        $siswa = $this->repo->cariById($id) ?? throw HttpException::notFound('Siswa tidak ditemukan.');

        $v = new Validasi($in);
        $d = SiswaValidator::bidang($v, false);
        $status = $v->enum('status_siswa', 'Status siswa', ['baru', 'aktif', 'lulus', 'pindah'], false);
        $v->selesai();

        if ($status !== null && $status !== $siswa->status) {
            if ($status !== 'pindah') {
                throw HttpException::unprocessable(
                    'Status hanya dapat diubah menjadi pindah. Status lain berubah otomatis oleh penempatan dan kenaikan kelas.',
                    ['status_siswa' => 'Hanya perubahan menjadi pindah yang diizinkan.']
                );
            }
            if (!in_array($siswa->status, ['baru', 'aktif'], true)) {
                throw HttpException::unprocessable(
                    'Hanya siswa berstatus baru atau aktif yang dapat ditandai pindah.',
                    ['status_siswa' => 'Status saat ini: ' . $siswa->status . '.']
                );
            }
            $d['status_siswa'] = 'pindah';
        }

        $this->repo->ubah($id, $d);
        return $this->detail($id);
    }

    /** Ditolak database (409) bila siswa sudah punya riwayat kelas atau data akademik; gunakan status. */
    public function hapus(int $id): void
    {
        $this->repo->cariById($id) ?? throw HttpException::notFound('Siswa tidak ditemukan.');
        $this->repo->hapus($id);
    }
}
