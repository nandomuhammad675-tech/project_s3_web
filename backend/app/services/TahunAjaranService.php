<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Helpers\Validasi;
use App\Models\TahunAjaran;
use App\Repositories\TahunAjaranRepository;
use App\Validators\TahunAjaranValidator;

final class TahunAjaranService
{
    private TahunAjaranRepository $repo;

    public function __construct(?TahunAjaranRepository $repo = null)
    {
        $this->repo = $repo ?? new TahunAjaranRepository();
    }

    public function daftar(): array
    {
        return array_map(static fn(TahunAjaran $t): array => $t->toArray(), $this->repo->daftar());
    }

    public function detail(int $id): array
    {
        return $this->ambil($id)->toArray();
    }

    /** Tahun ajaran baru: belum aktif, semester ganjil. Aktivasi lewat aktifkan(). */
    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $nama = TahunAjaranValidator::nama($v, true);
        $v->selesai();

        $id = $this->repo->buat((string) $nama);
        return $this->detail($id);
    }

    /**
     * Ubah nama dan/atau semester berjalan. Semester: hanya pada tahun ajaran AKTIF, hanya ganjil ke genap,
     * dan wajib konfirmasi_semester = true (FR-TA-01, BR-31). Nilai sama dengan yang sekarang diabaikan.
     */
    public function ubah(int $id, array $in): array
    {
        $t = $this->ambil($id);

        $v = new Validasi($in);
        $nama     = $v->ada('nama_tahun') ? TahunAjaranValidator::nama($v, true) : null;
        $semester = $v->ada('semester_aktif') ? $v->enum('semester_aktif', 'Semester', ['ganjil', 'genap'], true) : null;
        $v->selesai();

        $ubahSemester = $semester !== null && $semester !== $t->semesterAktif;
        if ($ubahSemester) {
            if (!$t->statusAktif) {
                throw HttpException::unprocessable('Semester berjalan hanya dapat diganti pada tahun ajaran yang aktif.');
            }
            if ($t->semesterAktif !== 'ganjil' || $semester !== 'genap') {
                throw HttpException::unprocessable('Semester berjalan hanya dapat maju dari ganjil ke genap.');
            }
            if (($in['konfirmasi_semester'] ?? false) !== true) {
                throw HttpException::unprocessable(
                    'Perubahan semester TIDAK DAPAT DIKEMBALIKAN. Kirim konfirmasi_semester = true untuk melanjutkan.',
                    ['konfirmasi_semester' => 'Konfirmasi wajib bernilai true.']
                );
            }
        }

        Database::transaksi(function () use ($id, $t, $nama, $semester, $ubahSemester): void {
            if ($nama !== null && $nama !== $t->namaTahun) {
                $this->repo->ubahNama($id, $nama);
            }
            if ($ubahSemester) {
                $this->repo->ubahSemester($id, (string) $semester);
            }
        });

        return $this->detail($id);
    }

    public function hapus(int $id): void
    {
        $t = $this->ambil($id);
        if ($t->statusAktif) {
            throw HttpException::conflict('Tahun ajaran aktif tidak dapat dihapus.');
        }
        // Bila masih ada kelas/riwayat/data lain, database menolak (1451) dan ErrorHandler membalas 409.
        $this->repo->hapus($id);
    }

    /**
     * Aktivasi (FR-TA-04, BR-23): tahun ajaran PERTAMA (belum ada yang aktif) boleh langsung diaktifkan;
     * selanjutnya hanya setelah kenaikan kelas ke tahun tersebut dijalankan. Dua langkah dalam satu transaksi.
     */
    public function aktifkan(int $id): array
    {
        $t = $this->ambil($id);
        if ($t->statusAktif) {
            throw HttpException::unprocessable('Tahun ajaran ini sudah aktif.');
        }
        if ($this->repo->aktif() !== null && !$this->repo->adaLogPromosiKeTahun($id)) {
            throw HttpException::unprocessable('Kenaikan kelas ke tahun ajaran ini belum dijalankan. Jalankan kenaikan kelas terlebih dahulu.');
        }

        Database::transaksi(function () use ($id): void {
            $this->repo->nonaktifkanSemua();   // satu UPDATE yang menukar keduanya dapat bentrok dengan UNIQUE aktif_flag
            $this->repo->aktifkan($id);
        });

        return $this->detail($id);
    }

    private function ambil(int $id): TahunAjaran
    {
        return $this->repo->cari($id) ?? throw HttpException::notFound('Tahun ajaran tidak ditemukan.');
    }
}
