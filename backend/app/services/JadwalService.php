<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\JadwalPelajaranRepository;
use App\Repositories\MataPelajaranRepository;
use App\Validators\JadwalValidator;

/**
 * CRUD jadwal pelajaran oleh Guru untuk kelasnya (FR-PWA-GURU-01). Kelas dan tahun ajaran selalu dari konteks Guru
 * ($g dari GuruMiddleware), tidak pernah dari input. Admin tidak punya akses (BR-15).
 */
final class JadwalService
{
    private JadwalPelajaranRepository $repo;
    private MataPelajaranRepository $mapelRepo;

    public function __construct()
    {
        $this->repo      = new JadwalPelajaranRepository();
        $this->mapelRepo = new MataPelajaranRepository();
    }

    public function daftar(array $g, ?string $semester, ?string $hari, array $p): array
    {
        $kelas = (int) $g['id_kelas'];
        $tahun = (int) $g['id_tahun_ajaran'];
        return Pagination::hasil(
            $this->repo->daftar($kelas, $tahun, $semester, $hari, $p['limit'], $p['offset']),
            $this->repo->total($kelas, $tahun, $semester, $hari),
            $p
        );
    }

    public function detail(array $g, int $id): array
    {
        return $this->repo->cari($id, (int) $g['id_kelas'], (int) $g['id_tahun_ajaran'])
            ?? throw HttpException::notFound('Jadwal tidak ditemukan.');
    }

    public function buat(array $g, array $in): array
    {
        $v = new Validasi($in);
        $d = JadwalValidator::bidang($v, true);
        $v->selesai();

        $this->periksaMapel((int) $d['id_mapel']);
        $this->periksaBentrok($g, $d, null);

        $id = $this->repo->buat((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], $d);
        return $this->detail($g, $id);
    }

    /** Lima kolom utama wajib dikirim ulang; keterangan hanya berubah bila dikirim. */
    public function ubah(array $g, int $id, array $in): array
    {
        $this->detail($g, $id);

        $v = new Validasi($in);
        $d = JadwalValidator::bidang($v, false);
        $v->selesai();

        $this->periksaMapel((int) $d['id_mapel']);
        $this->periksaBentrok($g, $d, $id);

        $this->repo->ubah($id, $d);
        return $this->detail($g, $id);
    }

    public function hapus(array $g, int $id): void
    {
        $this->detail($g, $id);
        $this->repo->hapus($id);
    }

    private function periksaMapel(int $idMapel): void
    {
        if ($this->mapelRepo->cari($idMapel) === null) {
            throw HttpException::unprocessable('Mata pelajaran tidak ditemukan.', ['id_mapel' => 'Mata pelajaran tidak ditemukan.']);
        }
    }

    private function periksaBentrok(array $g, array $d, ?int $kecuali): void
    {
        $b = $this->repo->bentrok(
            (int) $g['id_kelas'], (int) $g['id_tahun_ajaran'],
            (string) $d['semester'], (string) $d['hari'], (string) $d['jam_mulai'], (string) $d['jam_selesai'], $kecuali
        );
        if ($b !== null) {
            throw HttpException::conflict(sprintf(
                'Jadwal bertabrakan dengan %s (%s-%s) pada hari %s.',
                $b['nama_mapel'], substr((string) $b['jam_mulai'], 0, 5), substr((string) $b['jam_selesai'], 0, 5), $b['hari']
            ));
        }
    }
}
