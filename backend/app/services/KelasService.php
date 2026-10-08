<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Models\Kelas;
use App\Repositories\DataGuruRepository;
use App\Repositories\KelasRepository;
use App\Repositories\TahunAjaranRepository;
use App\Validators\KelasValidator;

final class KelasService
{
    private KelasRepository $repo;
    private TahunAjaranRepository $tahunRepo;
    private DataGuruRepository $guruRepo;

    public function __construct(?KelasRepository $repo = null, ?TahunAjaranRepository $tahunRepo = null, ?DataGuruRepository $guruRepo = null)
    {
        $this->repo      = $repo ?? new KelasRepository();
        $this->tahunRepo = $tahunRepo ?? new TahunAjaranRepository();
        $this->guruRepo  = $guruRepo ?? new DataGuruRepository();
    }

    public function daftar(?int $idTahun, array $p): array
    {
        return Pagination::hasil($this->repo->daftar($idTahun, $p['limit'], $p['offset']), $this->repo->total($idTahun), $p);
    }

    public function detail(int $id): array
    {
        return $this->ambil($id)->toArray();
    }

    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $d = KelasValidator::bidang($v, true);
        $v->selesai();

        $idTahun = (int) $d['id_tahun_ajaran'];
        if ($this->tahunRepo->cari($idTahun) === null) {
            throw HttpException::unprocessable('Tahun ajaran tidak ditemukan.', ['id_tahun_ajaran' => 'Tahun ajaran tidak ditemukan.']);
        }
        $this->periksaRombel($idTahun, (int) $d['tingkat_kelas'], null);
        $wali = $d['id_guru_wali'] ?? null;
        if ($wali !== null) {
            $this->periksaWali($idTahun, (int) $wali, null);
        }

        $id = $this->repo->buat($idTahun, (int) $d['tingkat_kelas'], (string) $d['nama_kelas'], $wali === null ? null : (int) $wali);
        return $this->detail($id);
    }

    /** Ubah parsial. Tahun ajaran kelas tidak dapat diubah. Wali dilepas dengan id_guru_wali = null. */
    public function ubah(int $id, array $in): array
    {
        $k = $this->ambil($id);
        $v = new Validasi($in);
        $d = KelasValidator::bidang($v, false);
        $v->selesai();

        if (isset($d['id_tahun_ajaran']) && $d['id_tahun_ajaran'] !== $k->idTahunAjaran) {
            throw HttpException::unprocessable('Tahun ajaran kelas tidak dapat diubah.', ['id_tahun_ajaran' => 'Tidak dapat diubah.']);
        }
        unset($d['id_tahun_ajaran']);

        if (isset($d['tingkat_kelas']) && $d['tingkat_kelas'] !== $k->tingkatKelas) {
            if ($k->jumlahSiswa > 0) {
                throw HttpException::unprocessable('Tingkat tidak dapat diubah karena kelas sudah memiliki siswa.', ['tingkat_kelas' => 'Kelas sudah memiliki siswa.']);
            }
            $this->periksaRombel($k->idTahunAjaran, (int) $d['tingkat_kelas'], $id);
        }
        if (isset($d['id_guru_wali']) && $d['id_guru_wali'] !== $k->idGuruWali) {
            $this->periksaWali($k->idTahunAjaran, (int) $d['id_guru_wali'], $id);
        }

        $this->repo->ubah($id, $d);
        return $this->detail($id);
    }

    /**
     * Ditolak (409) bila kelas masih menjadi target hari libur (database tidak menolak karena CASCADE; tanpa
     * pemeriksaan ini libur khusus kelas berubah menjadi libur semua kelas), dan oleh database (1451 => 409)
     * bila masih terkait riwayat kelas, absensi, nilai, jadwal, piket, atau log.
     */
    public function hapus(int $id): void
    {
        $this->ambil($id);
        if ($this->repo->adaTargetLibur($id)) {
            throw HttpException::conflict('Kelas masih menjadi target hari libur. Hapus atau ubah target hari libur terlebih dahulu.');
        }
        $this->repo->hapus($id);
    }

    private function ambil(int $id): Kelas
    {
        return $this->repo->cari($id) ?? throw HttpException::notFound('Kelas tidak ditemukan.');
    }

    /** Tingkat 2-5: satu kelas per tahun ajaran; tingkat 1 dan 6: maksimal dua (FR-KELAS-08). */
    private function periksaRombel(int $idTahun, int $tingkat, ?int $kecuali): void
    {
        $maks = in_array($tingkat, [1, 6], true) ? 2 : 1;
        $ada  = $this->repo->hitungRombel($idTahun, $tingkat, $kecuali);
        if ($ada >= $maks) {
            $pesan = 'Tingkat ' . $tingkat . ' sudah memiliki ' . $ada . ' kelas pada tahun ajaran ini (maksimal ' . $maks . ').';
            throw HttpException::unprocessable($pesan, ['tingkat_kelas' => $pesan]);
        }
    }

    private function periksaWali(int $idTahun, int $idGuru, ?int $kecuali): void
    {
        if ($this->guruRepo->cari($idGuru) === null) {
            throw HttpException::unprocessable('Guru tidak ditemukan.', ['id_guru_wali' => 'Guru tidak ditemukan.']);
        }
        if ($this->repo->waliSudahDipakai($idTahun, $idGuru, $kecuali)) {
            throw HttpException::conflict('Guru sudah menjadi wali kelas lain pada tahun ajaran ini.');
        }
    }
}
