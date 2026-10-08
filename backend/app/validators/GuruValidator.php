<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class GuruValidator
{
    /**
     * @param bool $baru true = semua kolom disertakan (kosong => null); false = hanya kolom yang dikirim (ubah parsial)
     * @return array<string, mixed> kolom data_guru
     */
    public static function bidang(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru || $v->ada('nama_guru')) {
            $d['nama_guru'] = $v->string('nama_guru', 'Nama guru', true, 100);
        }
        if ($baru || $v->ada('nip')) {
            $nip = $v->string('nip', 'NIP', false, 20, 5);
            if ($nip !== null && !ctype_digit($nip)) {
                $v->error('nip', 'NIP hanya boleh berisi angka.');
                $nip = null;
            }
            $d['nip'] = $nip;
        }
        if ($baru || $v->ada('jabatan')) {
            $d['jabatan'] = $v->string('jabatan', 'Jabatan', false, 50);
        }
        if ($baru || $v->ada('foto_profil')) {
            $d['foto_profil'] = $v->string('foto_profil', 'Foto profil', false, 255);
        }
        if ($baru || $v->ada('email')) {
            $d['email'] = $v->email('email', 'Email', false, 100);
        }
        if ($baru || $v->ada('no_telepon')) {
            $d['no_telepon'] = $v->telepon('no_telepon', 'Nomor telepon', false, 20);
        }
        return $d;
    }
}
