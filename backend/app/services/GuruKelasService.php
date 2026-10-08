<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\RiwayatKelasRepository;

/** Informasi kelas milik Guru yang sedang login. $g = konteks dari GuruMiddleware. */
final class GuruKelasService
{
    public function profil(array $g, string $username): array
    {
        return [
            'guru' => ['id_guru' => $g['id_guru'], 'nama_guru' => $g['nama_guru'], 'username' => $username],
            'tahun_ajaran' => $g['id_tahun_ajaran'] === null ? null : [
                'id_tahun_ajaran' => $g['id_tahun_ajaran'],
                'nama_tahun'      => $g['nama_tahun'],
                'semester'        => $g['semester'],
            ],
            // null = belum ditetapkan sebagai wali kelas (aplikasi menampilkan pesannya)
            'kelas' => $g['id_kelas'] === null ? null : [
                'id_kelas'      => $g['id_kelas'],
                'nama_kelas'    => $g['nama_kelas'],
                'tingkat_kelas' => $g['tingkat_kelas'],
            ],
        ];
    }

    /** Siswa AKTIF pada kelas Guru di tahun ajaran aktif. */
    public function siswa(array $g): array
    {
        $items = (new RiwayatKelasRepository())->siswaAktifKelas((int) $g['id_kelas'], (int) $g['id_tahun_ajaran']);
        return ['kelas' => $g['nama_kelas'], 'total' => count($items), 'items' => $items];
    }
}
