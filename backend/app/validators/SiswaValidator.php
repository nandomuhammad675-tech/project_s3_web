<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class SiswaValidator
{
    /** @return array<string, mixed> kolom data_siswa (tanpa status; status diatur terpisah) */
    public static function bidang(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru || $v->ada('nama_siswa')) {
            $d['nama_siswa'] = $v->string('nama_siswa', 'Nama siswa', true, 100);
        }
        if ($baru || $v->ada('nisn_resmi')) {
            $nisn = $v->string('nisn_resmi', 'NISN', true, 10, 10);
            if ($nisn !== null && !ctype_digit($nisn)) {
                $v->error('nisn_resmi', 'NISN harus berupa 10 digit angka.');
                $nisn = null;
            }
            $d['nisn_resmi'] = $nisn;
        }
        if ($baru || $v->ada('nama_wali')) {
            $d['nama_wali'] = $v->string('nama_wali', 'Nama wali', false, 100);
        }
        if ($baru || $v->ada('no_hp_wali')) {
            $d['no_hp_wali'] = $v->telepon('no_hp_wali', 'Nomor HP wali', false, 15);
        }
        return $d;
    }
}
