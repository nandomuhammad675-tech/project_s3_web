<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\DataSiswaRepository;
use App\Repositories\KelasRepository;
use App\Repositories\RiwayatKelasRepository;
use App\Repositories\TahunAjaranRepository;

/**
 * Penempatan siswa ke kelas. tahun ajaran SELALU diturunkan dari kelas (tidak diterima dari klien).
 * Perpindahan siswa antar kelas dan koreksi hasil kenaikan kelas belum tersedia (BR-37).
 */
final class RiwayatKelasService
{
    private RiwayatKelasRepository $repo;
    private KelasRepository $kelasRepo;
    private DataSiswaRepository $siswaRepo;
    private TahunAjaranRepository $tahunRepo;

    public function __construct()
    {
        $this->repo      = new RiwayatKelasRepository();
        $this->kelasRepo = new KelasRepository();
        $this->siswaRepo = new DataSiswaRepository();
        $this->tahunRepo = new TahunAjaranRepository();
    }

    public function daftar(array $filter, array $p): array
    {
        return Pagination::hasil($this->repo->daftar($filter, $p['limit'], $p['offset']), $this->repo->total($filter), $p);
    }

    public function detail(int $id): array
    {
        return ($this->repo->cari($id) ?? throw HttpException::notFound('Penempatan tidak ditemukan.'))->toArray();
    }

    /**
     * Penempatan langsung (FR-KELAS-11): hanya ke kelas pada tahun ajaran AKTIF.
     * Siswa baru menjadi aktif; siswa aktif tanpa penempatan tahun ini tetap aktif; lulus/pindah ditolak.
     * Penempatan ke tahun ajaran yang belum aktif hanya melalui kenaikan kelas.
     */
    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $idSiswa = $v->bilangan('id_siswa', 'Siswa', true, 1, 4294967295);
        $idKelas = $v->bilangan('id_kelas', 'Kelas', true, 1, 65535);
        $nomor   = $v->bilangan('nomor_absen', 'Nomor absen', false, 1, 255);
        $idTahunKirim = $v->bilangan('id_tahun_ajaran', 'Tahun ajaran', false, 1, 65535);
        $v->selesai();

        $kelas = $this->kelasRepo->cari((int) $idKelas)
            ?? throw HttpException::unprocessable('Kelas tidak ditemukan.', ['id_kelas' => 'Kelas tidak ditemukan.']);

        if ($idTahunKirim !== null && $idTahunKirim !== $kelas->idTahunAjaran) {
            throw HttpException::unprocessable('Tahun ajaran mengikuti kelas dan tidak dapat berbeda.', ['id_tahun_ajaran' => 'Tidak sesuai dengan tahun ajaran kelas.']);
        }

        $aktif = $this->tahunRepo->aktif();
        if ($aktif === null || $aktif->idTahunAjaran !== $kelas->idTahunAjaran) {
            throw HttpException::unprocessable(
                'Penempatan langsung hanya ke kelas pada tahun ajaran aktif. Untuk tahun ajaran berikutnya gunakan proses kenaikan kelas.',
                ['id_kelas' => 'Kelas bukan milik tahun ajaran aktif.']
            );
        }

        $siswa = $this->siswaRepo->cariById((int) $idSiswa)
            ?? throw HttpException::unprocessable('Siswa tidak ditemukan.', ['id_siswa' => 'Siswa tidak ditemukan.']);

        if (in_array($siswa->status, ['lulus', 'pindah'], true)) {
            throw HttpException::unprocessable('Siswa berstatus ' . $siswa->status . ' tidak dapat ditempatkan.', ['id_siswa' => 'Status siswa: ' . $siswa->status . '.']);
        }

        $id = Database::transaksi(function () use ($siswa, $kelas, $nomor): int {
            if ($this->repo->sudahDitempatkan($siswa->idSiswa, $kelas->idTahunAjaran)) {
                throw HttpException::conflict('Siswa sudah ditempatkan pada tahun ajaran ini.');
            }
            // nomor absen kembar dalam satu kelas ditolak database (1062 => 409) dan seluruh transaksi dibatalkan
            $id = $this->repo->buat($siswa->idSiswa, $kelas->idKelas, $kelas->idTahunAjaran, $nomor);
            if ($siswa->status === 'baru') {
                $this->siswaRepo->ubah($siswa->idSiswa, ['status_siswa' => 'aktif']);
            }
            return $id;
        });

        return $this->detail((int) $id);
    }

    /** Hanya nomor_absen yang dapat diubah (null = mengosongkan). Pindah kelas belum tersedia (BR-37). */
    public function ubah(int $id, array $in): array
    {
        $this->detail($id);

        foreach (['id_siswa', 'id_kelas', 'id_tahun_ajaran'] as $terlarang) {
            if (array_key_exists($terlarang, $in)) {
                throw HttpException::unprocessable('Hanya nomor_absen yang dapat diubah. Perpindahan kelas belum tersedia melalui sistem.', [$terlarang => 'Tidak dapat diubah.']);
            }
        }

        $v = new Validasi($in);
        if (!$v->ada('nomor_absen')) {
            throw HttpException::badRequest('nomor_absen wajib dikirim (angka, atau null untuk mengosongkan).');
        }
        $nomor = $v->bilangan('nomor_absen', 'Nomor absen', false, 1, 255);
        $v->selesai();

        $this->repo->ubahNomorAbsen($id, $nomor);
        return $this->detail($id);
    }

    /**
     * Hanya untuk mengoreksi penempatan yang salah (FR-KELAS-12). Ditolak (409) bila sudah ada absensi, nilai,
     * kedisiplinan, piket, atau log kenaikan kelas. Bila berhasil dan siswa berstatus aktif tanpa penempatan lain,
     * statusnya kembali menjadi baru (status lulus/pindah tidak diubah).
     */
    public function hapus(int $id): void
    {
        $r = $this->repo->cari($id) ?? throw HttpException::notFound('Penempatan tidak ditemukan.');

        $alasan = $this->repo->alasanTerkait($r);
        if ($alasan) {
            throw HttpException::conflict('Penempatan tidak dapat dihapus karena sudah terkait: ' . implode(', ', $alasan) . '.', ['terkait' => $alasan]);
        }

        Database::transaksi(function () use ($id, $r): void {
            $this->repo->hapus($id);
            $siswa = $this->siswaRepo->cariById($r->idSiswa);
            if ($siswa !== null && $siswa->status === 'aktif' && $this->repo->jumlahRiwayatSiswa($r->idSiswa) === 0) {
                $this->siswaRepo->ubah($r->idSiswa, ['status_siswa' => 'baru']);
            }
        });
    }
}
