<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class DisiplinValidator
{
    /**
     * Catatan minimal: jenis pelanggaran, tanggal, catatan (FR-DIS-02). Tanpa tingkat pelanggaran / tindak lanjut.
     * @param bool $baru true = semua wajib dan id_siswa ikut; false = ubah parsial (id_siswa tidak boleh diubah)
     */
    public static function bidang(Validasi $v, bool $baru): array
    {
        $d = [];
        if ($baru) {
            $d['id_siswa'] = $v->bilangan('id_siswa', 'Siswa', true, 1, 4294967295);
        }
        if ($baru || $v->ada('tanggal')) {
            $d['tanggal'] = $v->tanggal('tanggal', 'Tanggal', true);
        }
        if ($baru || $v->ada('jenis_pelanggaran')) {
            $d['jenis_pelanggaran'] = $v->string('jenis_pelanggaran', 'Jenis pelanggaran', true, 100);
        }
        if ($baru || $v->ada('catatan')) {
            $d['catatan'] = $v->string('catatan', 'Catatan', true, 2000);
        }
        return $d;
    }
}
