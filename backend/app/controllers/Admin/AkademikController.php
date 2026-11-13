<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Helpers\Pagination;
use App\Exports\AkademikExporter;
use App\Exports\LaporanExporter;
use App\Helpers\ResponseHelper;
use App\Services\AkademikAdminService;
use App\Validators\AkademikValidator;

/**
 * Admin membaca data akademik Guru: HANYA GET (read-only). Rute tulis sengaja tidak ada.
 * Filter: id_tahun_ajaran (default aktif), id_kelas, id_siswa, semester, + khusus tiap jenis (lihat AkademikValidator).
 */
final class AkademikController
{
    public function absensi(Request $req): void
    {
        $this->daftar($req, 'absensi');
    }

    public function nilai(Request $req): void
    {
        $this->daftar($req, 'nilai');
    }

    public function disiplin(Request $req): void
    {
        $this->daftar($req, 'disiplin');
    }

    private function daftar(Request $req, string $jenis): void
    {
        ResponseHelper::success('OK', (new AkademikAdminService())->daftar(
            $jenis, AkademikValidator::filter($req, $jenis), Pagination::dari($req)
        ));
    }

    public function absensiExcel(Request $req): void
    {
        $this->excel($req, 'absensi');
    }

    public function nilaiExcel(Request $req): void
    {
        $this->excel($req, 'nilai');
    }

    public function disiplinExcel(Request $req): void
    {
        $this->excel($req, 'disiplin');
    }

    /** GET /api/admin/akademik/{jenis}/excel?{filter yang sama dengan daftar}: unduh XLSX; sheet per kelas (nilai: per mapel per kelas). */
    private function excel(Request $req, string $jenis): void
    {
        $f = AkademikValidator::filter($req, $jenis);
        $hasil = (new AkademikAdminService())->untukExcel($jenis, $f);
        $tahun = $hasil['tahun'];

        $ket = 'Tahun Ajaran ' . $tahun->namaTahun
            . (($f['semester'] ?? null) ? ' | Semester ' . ucfirst((string) $f['semester']) : '')
            . (($f['dari'] ?? null) ? ' | Dari ' . $f['dari'] : '')
            . (($f['sampai'] ?? null) ? ' | Sampai ' . $f['sampai'] : '');
        $info = [
            'keterangan' => $ket,
            'baris' => [
                ['Sekolah', (string) \App\Core\Config::env('SCHOOL_NAME', 'SDN Kedungsari 1')],
                ['Tahun Ajaran', $tahun->namaTahun],
                ['Semester', ($f['semester'] ?? null) ? ucfirst((string) $f['semester']) : 'Semua'],
                ['Jumlah baris data', (string) count($hasil['rows'])],
                ['Tanggal ekspor', (new \DateTimeImmutable('now'))->format('Y-m-d H:i')],
            ],
        ];
        $nama = preg_replace('/[^A-Za-z0-9_-]+/', '-', 'data-' . $jenis . '-' . $tahun->namaTahun) . '.xlsx';
        LaporanExporter::kirim(AkademikExporter::buat($jenis, $info, $hasil['rows']), $nama);
    }
}
