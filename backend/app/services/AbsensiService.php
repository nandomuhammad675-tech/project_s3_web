<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Helpers\Validasi;
use App\Repositories\AbsensiSiswaRepository;
use App\Repositories\HariLiburRepository;
use App\Repositories\JurnalAbsenKelasRepository;
use App\Repositories\RiwayatKelasRepository;
use App\Validators\AbsensiValidator;

/**
 * Absensi harian kelas sendiri. id_kelas, id_tahun_ajaran, semester (dari semester_aktif), dan id_guru diisi SERVER
 * dari konteks Guru ($g), bukan dari input (FR-ABS-08).
 */
final class AbsensiService
{
    private AbsensiSiswaRepository $repo;
    private JurnalAbsenKelasRepository $jurnal;
    private HariLiburRepository $libur;
    private RiwayatKelasRepository $riwayat;
    private DasborAbsensiService $dasbor;

    public function __construct()
    {
        $this->repo    = new AbsensiSiswaRepository();
        $this->jurnal  = new JurnalAbsenKelasRepository();
        $this->libur   = new HariLiburRepository();
        $this->riwayat = new RiwayatKelasRepository();
        $this->dasbor  = new DasborAbsensiService();
    }

    /** Daftar siswa aktif + status absensi pada tanggal ini, beserta status kelas (sudah/belum/libur). */
    public function lihat(array $g, string $tanggal): array
    {
        return [
            'tanggal'      => $tanggal,
            'status_kelas' => $this->dasbor->statusKelas((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], $tanggal),
            'items'        => $this->repo->untukTanggal((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], $tanggal),
        ];
    }

    /**
     * Simpan batch dalam satu transaksi. Ditolak seluruhnya (422) bila tanggal hari Minggu atau hari libur kelas,
     * atau ada siswa yang bukan siswa aktif kelas ini. Satu siswa hanya satu catatan per tanggal (409 bila sudah ada;
     * untuk memperbaiki gunakan PUT). Sabtu adalah hari efektif.
     */
    public function simpan(array $g, array $in): array
    {
        $v = new Validasi($in);
        [$tanggal, $items] = AbsensiValidator::batch($v, $in);
        $v->selesai();

        $this->periksaHariEfektif((int) $g['id_kelas'], (string) $tanggal);
        $ids = array_column($items, 'id_siswa');
        $this->periksaSiswa($g, $ids);

        $sudah = $this->repo->sudahAda($ids, (string) $tanggal);
        if ($sudah) {
            throw HttpException::conflict(
                'Absensi tanggal ' . $tanggal . ' sudah tercatat untuk siswa: ' . implode(', ', $sudah) . '. Gunakan perbaikan (PUT) untuk mengubahnya.',
                ['id_siswa' => $sudah]
            );
        }

        Database::transaksi(function () use ($g, $tanggal, $items): void {
            $this->repo->buatBanyak((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], (string) $g['semester'], (int) $g['id_guru'], (string) $tanggal, $items);
            $this->jurnal->tandaiSudah((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], (string) $tanggal);
        });

        return ['tanggal' => $tanggal, 'jumlah' => count($items), 'status_kelas' => 'sudah'];
    }

    /** Perbaiki status kehadiran satu catatan milik kelas sendiri. */
    public function koreksi(array $g, int $id, array $in): array
    {
        $a = $this->repo->cari($id, (int) $g['id_kelas'], (int) $g['id_tahun_ajaran'])
            ?? throw HttpException::notFound('Data absensi tidak ditemukan.');

        $v = new Validasi($in);
        $status = AbsensiValidator::status($v);
        $v->selesai();

        $this->periksaSiswa($g, [$a['id_siswa']]);
        $this->repo->ubahStatus($id, (string) $status);

        $a['status'] = $status;
        return $a;
    }

    private function periksaHariEfektif(int $idKelas, string $tanggal): void
    {
        if ((new \DateTimeImmutable($tanggal))->format('N') === '7') {
            throw HttpException::unprocessable('Hari Minggu bukan hari efektif; absensi tidak dapat dicatat.', ['tanggal' => 'Hari Minggu.']);
        }
        $libur = $this->libur->liburUntukKelas($tanggal, $idKelas);
        if ($libur !== null) {
            $ket = ($libur['keterangan'] ?? '') !== '' ? ' (' . $libur['keterangan'] . ')' : '';
            throw HttpException::unprocessable('Tanggal ini hari libur' . $ket . '; absensi tidak dapat dicatat.', ['tanggal' => 'Hari libur.']);
        }
    }

    /** Hanya siswa AKTIF di kelas Guru ini yang boleh diabsen (SRS-ABS-12). */
    private function periksaSiswa(array $g, array $ids): void
    {
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
