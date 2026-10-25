<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class KelasValidator
{
    /** @return array<string, mixed> kunci yang dikirim saja (ubah) atau semua (baru) */
    public static function bidang(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru || $v->ada('id_tahun_ajaran')) {
            $d['id_tahun_ajaran'] = $v->bilangan('id_tahun_ajaran', 'Tahun ajaran', true, 1, 65535);
        }
        if ($baru || $v->ada('tingkat_kelas')) {
            $d['tingkat_kelas'] = $v->bilangan('tingkat_kelas', 'Tingkat kelas', true, 1, 6);
        }
        if ($baru || $v->ada('nama_kelas')) {
            $d['nama_kelas'] = $v->string('nama_kelas', 'Nama kelas', true, 10);
        }
        if ($baru || $v->ada('id_guru_wali')) {
            // null / kosong = tidak ada wali (melepas penetapan wali)
            $d['id_guru_wali'] = $v->bilangan('id_guru_wali', 'Guru wali', false, 1, 4294967295);
        }
        return $d;
    }
}
