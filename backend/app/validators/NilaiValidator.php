<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class NilaiValidator
{
    public const JENIS = ['UH', 'ASTS', 'ASAS'];

    /**
     * Body: {"id_siswa":1,"id_mapel":3,"semester":"ganjil","jenis":"UH","nomor_uh":2,"nilai":87}
     * kelas, tahun ajaran, dan Guru TIDAK dibaca dari input (diisi server).
     * @return array{id_siswa:?int, id_mapel:?int, semester:?string, jenis:?string, nomor_uh:?int, nilai:?int}
     */
    public static function bidang(Validasi $v, array $in): array
    {
        $d = [
            'id_siswa' => $v->bilangan('id_siswa', 'Siswa', true, 1, 4294967295),
            'id_mapel' => $v->bilangan('id_mapel', 'Mata pelajaran', true, 1, 255),
            'semester' => $v->enum('semester', 'Semester', ['ganjil', 'genap'], true),
            'jenis'    => $v->enum('jenis', 'Jenis penilaian', self::JENIS, true),
            'nomor_uh' => null,
            'nilai'    => self::nilai($v),
        ];

        if ($d['jenis'] === 'UH') {
            $d['nomor_uh'] = $v->bilangan('nomor_uh', 'Nomor UH', true, 1, 255);
        } elseif ($d['jenis'] !== null && ($in['nomor_uh'] ?? null) !== null && $in['nomor_uh'] !== '') {
            $v->error('nomor_uh', 'Nomor UH hanya untuk jenis UH; kosongkan untuk ' . $d['jenis'] . '.');
        }
        return $d;
    }

    /** Nilai bulat 0-100 (rentang sekolah; kolom database tinyint, jadi desimal tidak diterima). */
    public static function nilai(Validasi $v): ?int
    {
        return $v->bilangan('nilai', 'Nilai', true, 0, 100);
    }
}
