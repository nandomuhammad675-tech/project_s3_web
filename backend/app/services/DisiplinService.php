<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\CatatanKedisiplinanRepository;
use App\Repositories\RiwayatKelasRepository;
use App\Validators\DisiplinValidator;

/** Catatan kedisiplinan kelas sendiri. Dapat ditambah dan diperbaiki, TIDAK dapat dihapus (tidak ada method hapus). */
final class DisiplinService
{
    private CatatanKedisiplinanRepository $repo;
    private RiwayatKelasRepository $riwayat;

    public function __construct()
    {
        $this->repo    = new CatatanKedisiplinanRepository();
        $this->riwayat = new RiwayatKelasRepository();
    }

    public function daftar(array $g, ?int $idSiswa, array $p): array
    {
        $kelas = (int) $g['id_kelas'];
        $tahun = (int) $g['id_tahun_ajaran'];
        return Pagination::hasil(
            $this->repo->daftar($kelas, $tahun, $idSiswa, $p['limit'], $p['offset']),
            $this->repo->total($kelas, $tahun, $idSiswa),
            $p
        );
    }

    public function detail(array $g, int $id): array
    {
        return $this->repo->cari($id, (int) $g['id_kelas'], (int) $g['id_tahun_ajaran'])
            ?? throw HttpException::notFound('Catatan kedisiplinan tidak ditemukan.');
    }

    public function buat(array $g, array $in): array
    {
        $v = new Validasi($in);
        $d = DisiplinValidator::bidang($v, true);
        $v->selesai();

        $this->periksaSiswa($g, (int) $d['id_siswa']);
        $id = $this->repo->buat((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], (int) $g['id_guru'], $d);
        return $this->detail($g, $id);
    }

    /** Ubah parsial: tanggal, jenis_pelanggaran, catatan. Siswa pada catatan tidak dapat diganti. */
    public function ubah(array $g, int $id, array $in): array
    {
        $c = $this->detail($g, $id);

        if (array_key_exists('id_siswa', $in) && (int) $in['id_siswa'] !== $c['id_siswa']) {
            throw HttpException::unprocessable('Siswa pada catatan tidak dapat diganti.', ['id_siswa' => 'Tidak dapat diubah.']);
        }

        $v = new Validasi($in);
        $d = DisiplinValidator::bidang($v, false);
        $v->selesai();

        $this->periksaSiswa($g, $c['id_siswa']);
        $this->repo->ubah($id, $d);
        return $this->detail($g, $id);
    }

    /** Hanya siswa AKTIF di kelas Guru ini (TDD §4: input untuk siswa non-aktif 422). */
    private function periksaSiswa(array $g, int $idSiswa): void
    {
        $boleh = array_column($this->riwayat->siswaAktifKelas((int) $g['id_kelas'], (int) $g['id_tahun_ajaran']), 'id_siswa');
        if (!in_array($idSiswa, $boleh, true)) {
            throw HttpException::unprocessable('Siswa bukan siswa aktif di kelas Anda.', ['id_siswa' => 'Tidak valid: ' . $idSiswa]);
        }
    }
}
