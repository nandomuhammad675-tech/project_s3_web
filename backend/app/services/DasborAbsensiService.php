<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\HariLiburRepository;
use App\Repositories\JurnalAbsenKelasRepository;
use App\Repositories\KelasRepository;
use App\Repositories\TahunAjaranRepository;

/** Status absensi kelas per tanggal: libur, sudah, atau belum (FR-ABS-10/11). Hanya baca. */
final class DasborAbsensiService
{
    private HariLiburRepository $libur;
    private JurnalAbsenKelasRepository $jurnal;

    public function __construct()
    {
        $this->libur  = new HariLiburRepository();
        $this->jurnal = new JurnalAbsenKelasRepository();
    }

    /** Urutan: Minggu/libur => "libur"; ada penanda jurnal => "sudah"; selain itu "belum". */
    public function statusKelas(int $idKelas, int $idTahun, string $tanggal): string
    {
        if ((new \DateTimeImmutable($tanggal))->format('N') === '7') {
            return 'libur';
        }
        if ($this->libur->liburUntukKelas($tanggal, $idKelas) !== null) {
            return 'libur';
        }
        return $this->jurnal->sudahDiabsen($idKelas, $idTahun, $tanggal) ? 'sudah' : 'belum';
    }

    public function untukGuru(array $g, string $tanggal): array
    {
        return [
            'tanggal'    => $tanggal,
            'id_kelas'   => $g['id_kelas'],
            'nama_kelas' => $g['nama_kelas'],
            'status'     => $this->statusKelas((int) $g['id_kelas'], (int) $g['id_tahun_ajaran'], $tanggal),
        ];
    }

    /** Seluruh kelas pada tahun ajaran aktif. Kelas tanpa baris jurnal dianggap "belum". */
    public function untukAdmin(string $tanggal): array
    {
        $tahun = (new TahunAjaranRepository())->aktif();
        if ($tahun === null) {
            return ['tanggal' => $tanggal, 'tahun_ajaran' => null, 'ringkasan' => ['sudah' => 0, 'belum' => 0, 'libur' => 0], 'items' => []];
        }

        $ringkasan = ['sudah' => 0, 'belum' => 0, 'libur' => 0];
        $items = [];
        foreach ((new KelasRepository())->daftar($tahun->idTahunAjaran, 200, 0) as $k) {
            $status = $this->statusKelas((int) $k['id_kelas'], $tahun->idTahunAjaran, $tanggal);
            $ringkasan[$status]++;
            $items[] = [
                'id_kelas'       => $k['id_kelas'],
                'nama_kelas'     => $k['nama_kelas'],
                'tingkat_kelas'  => $k['tingkat_kelas'],
                'nama_guru_wali' => $k['nama_guru_wali'],
                'status'         => $status,
            ];
        }
        return ['tanggal' => $tanggal, 'tahun_ajaran' => $tahun->namaTahun, 'ringkasan' => $ringkasan, 'items' => $items];
    }
}
