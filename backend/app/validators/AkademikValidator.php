<?php
declare(strict_types=1);

namespace App\Validators;

use App\Core\HttpException;
use App\Core\Request;
use App\Helpers\Pagination;
use App\Helpers\Validasi;

/** Validasi parameter query untuk baca data akademik Admin dan laporan. Salah format => 400. */
final class AkademikValidator
{
    public const SEMESTER = ['ganjil', 'genap'];

    /** Filter baca akademik Admin. @param string $jenis absensi|nilai|disiplin */
    public static function filter(Request $req, string $jenis): array
    {
        $f = [
            'id_tahun_ajaran' => Pagination::angka($req, 'id_tahun_ajaran'),
            'id_kelas'        => Pagination::angka($req, 'id_kelas'),
            'id_siswa'        => Pagination::angka($req, 'id_siswa'),
        ];
        if ($jenis !== 'disiplin') {
            $f['semester'] = self::pilihan($req, 'semester', self::SEMESTER);
        }
        if ($jenis === 'absensi') {
            $f['status'] = self::pilihan($req, 'status', ['hadir', 'sakit', 'izin', 'alpa']);
        }
        if ($jenis === 'nilai') {
            $f['id_mapel'] = Pagination::angka($req, 'id_mapel');
            $f['jenis']    = self::pilihan($req, 'jenis', ['UH', 'ASTS', 'ASAS']);
        }
        if ($jenis !== 'nilai') {
            $f['dari']   = self::tanggal($req, 'dari');
            $f['sampai'] = self::tanggal($req, 'sampai');
            if ($f['dari'] !== null && $f['sampai'] !== null && $f['dari'] > $f['sampai']) {
                throw HttpException::badRequest('Parameter dari tidak boleh setelah sampai.');
            }
        }
        return $f;
    }

    /**
     * Parameter laporan: semester dan tahun_ajaran (id) wajib; kelas opsional (hanya berlaku untuk Admin);
     * jenis (khusus Excel): absensi | nilai | semua (default).
     * @return array{semester:string, id_tahun_ajaran:int, id_kelas:?int, jenis:string}
     */
    public static function laporan(Request $req): array
    {
        $semester = self::pilihan($req, 'semester', self::SEMESTER);
        $tahun    = Pagination::angka($req, 'tahun_ajaran');
        if ($semester === null || $tahun === null) {
            throw HttpException::badRequest('Parameter semester (ganjil/genap) dan tahun_ajaran (id tahun ajaran) wajib diisi.');
        }
        return [
            'semester'        => $semester,
            'id_tahun_ajaran' => $tahun,
            'id_kelas'        => Pagination::angka($req, 'kelas'),
            'jenis'           => self::pilihan($req, 'jenis', ['absensi', 'nilai', 'semua']) ?? 'semua',
        ];
    }

    private static function pilihan(Request $req, string $nama, array $boleh): ?string
    {
        $v = $req->query($nama);
        if ($v === null || $v === '') {
            return null;
        }
        if (!is_string($v) || !in_array($v, $boleh, true)) {
            throw HttpException::badRequest('Parameter ' . $nama . ' harus salah satu dari: ' . implode(', ', $boleh) . '.');
        }
        return $v;
    }

    private static function tanggal(Request $req, string $nama): ?string
    {
        $v = $req->query($nama);
        if ($v === null || $v === '') {
            return null;
        }
        if (!is_string($v) || !Validasi::adalahTanggal($v)) {
            throw HttpException::badRequest('Parameter ' . $nama . ' harus berformat YYYY-MM-DD.');
        }
        return $v;
    }
}
