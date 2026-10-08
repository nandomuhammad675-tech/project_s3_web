<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class PiketValidator
{
    /**
     * @param bool $baru true = hari wajib; false = ubah parsial (hanya kunci yang dikirim)
     * @return array<string, mixed> kunci: hari, keterangan, id_siswa (daftar id atau tidak ada)
     */
    public static function bidang(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru || $v->ada('hari')) {
            $d['hari'] = $v->enum('hari', 'Hari', JadwalValidator::HARI, true);
        }
        if ($baru || $v->ada('keterangan')) {
            $d['keterangan'] = $v->string('keterangan', 'Keterangan', false, 100);
        }
        $siswa = $v->daftarId('id_siswa', 'Siswa piket');
        if ($siswa !== null) {
            $d['id_siswa'] = $siswa;
        }
        return $d;
    }
}
