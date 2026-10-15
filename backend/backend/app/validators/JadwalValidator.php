<?php
declare(strict_types=1);

namespace App\Validators;

use App\Helpers\Validasi;

final class JadwalValidator
{
    public const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    /**
     * kelas dan tahun ajaran TIDAK dibaca dari input: selalu dari konteks Guru (server).
     * @return array{semester:?string, id_mapel:?int, hari:?string, jam_mulai:?string, jam_selesai:?string, keterangan?:?string}
     */
    public static function bidang(Validasi $v, bool $baru): array
    {
        $d = [
            'semester'    => $v->enum('semester', 'Semester', ['ganjil', 'genap'], true),
            'id_mapel'    => $v->bilangan('id_mapel', 'Mata pelajaran', true, 1, 255),
            'hari'        => $v->enum('hari', 'Hari', self::HARI, true),
            'jam_mulai'   => self::jam($v, 'jam_mulai', 'Jam mulai'),
            'jam_selesai' => self::jam($v, 'jam_selesai', 'Jam selesai'),
        ];
        if ($baru || $v->ada('keterangan')) {
            $d['keterangan'] = $v->string('keterangan', 'Keterangan', false, 100);
        }
        if ($d['jam_mulai'] !== null && $d['jam_selesai'] !== null && $d['jam_selesai'] <= $d['jam_mulai']) {
            $v->error('jam_selesai', 'Jam selesai harus lebih besar dari jam mulai.');
        }
        return $d;
    }

    /** Terima "7:05" atau "07:05" atau "07:05:00"; kembalikan "07:05:00". */
    private static function jam(Validasi $v, string $k, string $label): ?string
    {
        $s = $v->string($k, $label, true, 8, 4);
        if ($s === null) {
            return null;
        }
        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?$/', $s, $m)) {
            $v->error($k, $label . ' harus berformat HH:MM (24 jam).');
            return null;
        }
        return sprintf('%02d:%s:%s', (int) $m[1], $m[2], $m[3] ?? '00');
    }
}
