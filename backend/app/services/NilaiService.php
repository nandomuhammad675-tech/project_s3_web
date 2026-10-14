<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\DataNilaiRepository;
use App\Repositories\MataPelajaranRepository;
use App\Repositories\RiwayatKelasRepository;
use App\Validators\NilaiValidator;

/**
 * Nilai UH (boleh berulang, bernomor), ASTS, dan ASAS (masing-masing satu per siswa-mapel-semester).
 * Hanya UH yang dapat dihapus; ASTS/ASAS hanya diperbaiki (FR-NILAI-07). Admin hanya membaca (tidak ada rute Admin).
 */
final class NilaiService
{
    private DataNilaiRepository $repo;
    private MataPelajaranRepository $mapel;
    private RiwayatKelasRepository $riwayat;

    public function __construct()
    {
        $this->repo    = new DataNilaiRepository();
        $this->mapel   = new MataPelajaranRepository();
        $this->riwayat = new RiwayatKelasRepository();
    }

    public function daftar(array $g, array $filter, array $p): array
    {
        $kelas = (int) $g['id_kelas'];
        $tahun = (int) $g['id_tahun_ajaran'];
        return Pagination::hasil(
            $this->repo->daftar($kelas, $tahun, $filter, $p['limit'], $p['offset']),
            $this->repo->total($kelas, $tahun, $filter),
            $p
        );
    }

    public function detail(array $g, int $id): array
    {
        return $this->repo->cari($id, (int) $g['id_kelas'], (int) $g['id_tahun_ajaran'])
            ?? throw HttpException::notFound('Nilai tidak ditemukan.');
    }

    public function buat(array $g, array $in): array
    {
        $v = new Validasi($in);
        $d = NilaiValidator::bidang($v, $in);
        $v->selesai();

        $this->periksaSiswa($g, (int) $d['id_siswa']);
        if ($this->mapel->cari((int) $d['id_mapel']) === null) {
            throw HttpException::unprocessable('Mata pelajaran tidak ditemukan.', ['id_mapel' => 'Mata pelajaran tidak ditemukan.']);
        }

        $tahun = (int) $g['id_tahun_ajaran'];
        if ($this->repo->ada((int) $d['id_siswa'], (int) $d['id_mapel'], $tahun, (string) $d['semester'], (string) $d['jenis'], $d['nomor_uh'])) {
            $label = $d['jenis'] === 'UH' ? 'UH-' . $d['nomor_uh'] : (string) $d['jenis'];
            throw HttpException::conflict(
                'Nilai ' . $label . ' untuk siswa, mata pelajaran, dan semester ini sudah ada. Gunakan perbaikan (PUT) untuk mengubahnya.'
            );
        }

        $id = $this->repo->buat((int) $g['id_kelas'], $tahun, (int) $g['id_guru'], $d);
        return $this->detail($g, $id);
    }

    /** Perbaiki nilai (hanya angkanya). */
    public function ubah(array $g, int $id, array $in): array
    {
        $n = $this->detail($g, $id);

        $v = new Validasi($in);
        $nilai = NilaiValidator::nilai($v);
        $v->selesai();

        $this->periksaSiswa($g, (int) $n['id_siswa']);
        $this->repo->ubahNilai($id, (int) $nilai);
        return $this->detail($g, $id);
    }

    public function hapus(array $g, int $id): void
    {
        $n = $this->detail($g, $id);
        if ($n['jenis'] !== 'UH') {
            throw HttpException::unprocessable(
                $n['jenis'] . ' tidak dapat dihapus, hanya diperbaiki. Hanya nilai UH yang salah input yang dapat dihapus.',
                ['jenis' => 'Hanya UH yang dapat dihapus.']
            );
        }
        $this->repo->hapus($id);
    }

    /** Hanya siswa AKTIF di kelas Guru ini (SRS-NILAI-09). */
    private function periksaSiswa(array $g, int $idSiswa): void
    {
        $boleh = array_column($this->riwayat->siswaAktifKelas((int) $g['id_kelas'], (int) $g['id_tahun_ajaran']), 'id_siswa');
        if (!in_array($idSiswa, $boleh, true)) {
            throw HttpException::unprocessable('Siswa bukan siswa aktif di kelas Anda.', ['id_siswa' => 'Tidak valid: ' . $idSiswa]);
        }
    }
}
