<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Helpers\Validasi;
use App\Repositories\JadwalPiketRepository;
use App\Repositories\PiketSiswaRepository;
use App\Repositories\RiwayatKelasRepository;
use App\Validators\PiketValidator;

/**
 * CRUD piket kebersihan kelas oleh Guru (FR-PWA-GURU-02): satu piket per kelas per hari, berisi siswa bertugas.
 * Piket berulang per hari (bukan per tanggal). Siswa harus siswa AKTIF di kelas Guru sendiri.
 */
final class PiketService
{
    private JadwalPiketRepository $repo;
    private PiketSiswaRepository $siswaPiket;
    private RiwayatKelasRepository $riwayat;

    public function __construct()
    {
        $this->repo       = new JadwalPiketRepository();
        $this->siswaPiket = new PiketSiswaRepository();
        $this->riwayat    = new RiwayatKelasRepository();
    }

    public function daftar(array $g): array
    {
        $rows  = $this->repo->daftar((int) $g['id_kelas'], (int) $g['id_tahun_ajaran']);
        $siswa = $this->siswaPiket->untuk(array_map(static fn(array $r): int => $r['id_piket'], $rows));
        $items = array_map(static function (array $r) use ($siswa): array {
            $r['siswa'] = $siswa[$r['id_piket']] ?? [];
            return $r;
        }, $rows);
        return ['items' => $items, 'total' => count($items)];
    }

    public function detail(array $g, int $id): array
    {
        $r = $this->repo->cari($id, (int) $g['id_kelas'], (int) $g['id_tahun_ajaran'])
            ?? throw HttpException::notFound('Jadwal piket tidak ditemukan.');
        $r['siswa'] = $this->siswaPiket->untuk([$id])[$id] ?? [];
        return $r;
    }

    public function buat(array $g, array $in): array
    {
        $v = new Validasi($in);
        $d = PiketValidator::bidang($v, true);
        $v->selesai();

        $ids = $d['id_siswa'] ?? [];
        $this->periksaSiswa($g, $ids);
        $this->periksaHari($g, (string) $d['hari'], null);

        $id = Database::transaksi(function () use ($g, $d, $ids): int {
            $id = $this->repo->buat((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], (string) $d['hari'], $d['keterangan'], (int) $g['id_guru']);
            $this->siswaPiket->ganti($id, $ids);
            return $id;
        });

        return $this->detail($g, (int) $id);
    }

    /** Ubah parsial: hari, keterangan, dan/atau daftar siswa (id_siswa dikirim = menggantikan seluruh daftar). */
    public function ubah(array $g, int $id, array $in): array
    {
        $this->detail($g, $id);

        $v = new Validasi($in);
        $d = PiketValidator::bidang($v, false);
        $v->selesai();

        if (isset($d['hari'])) {
            $this->periksaHari($g, (string) $d['hari'], $id);
        }
        $ids = $d['id_siswa'] ?? null;
        if ($ids !== null) {
            $this->periksaSiswa($g, $ids);
        }

        Database::transaksi(function () use ($g, $id, $d, $ids): void {
            $kolom = array_intersect_key($d, ['hari' => 1, 'keterangan' => 1]);
            $kolom['id_guru_penetap'] = (int) $g['id_guru'];
            $this->repo->ubah($id, $kolom);
            if ($ids !== null) {
                $this->siswaPiket->ganti($id, $ids);
            }
        });

        return $this->detail($g, $id);
    }

    /** Jadwal piket bukan data akademik: Guru pemilik kelas boleh menghapusnya; siswa bertugas ikut terhapus. */
    public function hapus(array $g, int $id): void
    {
        $this->detail($g, $id);
        $this->repo->hapus($id);
    }

    private function periksaHari(array $g, string $hari, ?int $kecuali): void
    {
        if ($this->repo->adaHari((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], $hari, $kecuali)) {
            throw HttpException::conflict('Sudah ada jadwal piket untuk hari ' . $hari . '. Ubah jadwal yang ada.');
        }
    }

    /** Semua id harus siswa AKTIF pada kelas Guru ini (siswa kelas lain, baru, lulus, atau pindah ditolak). */
    private function periksaSiswa(array $g, array $ids): void
    {
        if (!$ids) {
            return;
        }
        $boleh = array_column($this->riwayat->siswaAktifKelas((int) $g['id_kelas'], (int) $g['id_tahun_ajaran']), 'id_siswa');
        $tolak = array_values(array_diff($ids, $boleh));
        if ($tolak) {
            throw HttpException::unprocessable(
                'Sebagian siswa bukan siswa aktif di kelas Anda.',
                ['id_siswa' => 'Tidak valid: ' . implode(', ', $tolak)]
            );
        }
    }
}
