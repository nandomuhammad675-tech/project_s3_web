<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Repositories\DataSiswaRepository;
use App\Validators\SiswaValidator;

final class SiswaService
{
    private DataSiswaRepository $repo;

    public function __construct(?DataSiswaRepository $repo = null)
    {
        $this->repo = $repo ?? new DataSiswaRepository();
    }

    public function daftar(?string $q, ?string $status, array $p): array
    {
        return Pagination::hasil($this->repo->daftarAdmin($q, $status, $p['limit'], $p['offset']), $this->repo->totalAdmin($q, $status), $p);
    }

    public function detail(int $id): array
    {
        return $this->repo->detailAdmin($id) ?? throw HttpException::notFound('Siswa tidak ditemukan.');
    }

    /** Siswa baru (hasil PPDB atau pindahan) SELALU berstatus "baru" sampai ditempatkan ke kelas (FR-SISWA-03). */
    public function buat(array $in): array
    {
        $v = new Validasi($in);
        $d = SiswaValidator::bidang($v, true);
        if ($v->ada('status_siswa') && ($in['status_siswa'] ?? null) !== 'baru') {
            $v->error('status_siswa', 'Siswa baru selalu berstatus baru; status berubah otomatis saat ditempatkan.');
        }
        $v->selesai();

        $id = $this->repo->buat((string) $d['nama_siswa'], (string) $d['nisn_resmi'], $d['nama_wali'], $d['no_hp_wali']);
        return $this->detail($id);
    }

    /**
     * Impor massal siswa (dari berkas Excel yang dibaca klien). Tiap baris divalidasi seperti buat(); baris
     * yang valid disimpan (status baru), yang gagal dilaporkan per baris. Maksimal 500 baris per permintaan.
     * @param array<int, mixed> $rows
     * @return array{total:int, berhasil:int, gagal:int, hasil:array<int, array<string, mixed>>}
     */
    public function impor(array $rows): array
    {
        if ($rows === []) {
            throw HttpException::unprocessable('Tidak ada baris data untuk diimpor.');
        }
        if (count($rows) > 500) {
            throw HttpException::unprocessable('Maksimal 500 baris per impor. Pecah berkas menjadi beberapa bagian.');
        }

        $hasil = [];
        $ok = 0;
        $dalamBerkas = [];
        foreach (array_values($rows) as $i => $baris) {
            $no = $i + 1;
            if (!is_array($baris)) {
                $hasil[] = ['baris' => $no, 'status' => 'gagal', 'pesan' => 'Format baris tidak valid.'];
                continue;
            }
            $nisn = isset($baris['nisn_resmi']) ? trim((string) $baris['nisn_resmi']) : '';
            if ($nisn !== '' && isset($dalamBerkas[$nisn])) {
                $hasil[] = ['baris' => $no, 'status' => 'gagal', 'pesan' => 'NISN ganda dalam berkas (sama dengan baris ' . $dalamBerkas[$nisn] . ').'];
                continue;
            }
            try {
                $in = [
                    'nama_siswa' => $baris['nama_siswa'] ?? null,
                    'nisn_resmi' => $nisn,
                    'nama_wali'  => ($baris['nama_wali'] ?? '') === '' ? null : $baris['nama_wali'],
                    'no_hp_wali' => ($baris['no_hp_wali'] ?? '') === '' ? null : $baris['no_hp_wali'],
                ];
                $this->buat($in);
                $dalamBerkas[$nisn] = $no;
                $ok++;
                $hasil[] = ['baris' => $no, 'status' => 'berhasil', 'pesan' => 'Tersimpan.'];
            } catch (HttpException $e) {
                $detail = $e->errors ? ' ' . implode(' ', array_map('strval', $e->errors)) : '';
                $pesan = $e->status === 409 ? 'NISN sudah terdaftar.' : trim($e->getMessage() . $detail);
                $hasil[] = ['baris' => $no, 'status' => 'gagal', 'pesan' => $pesan];
            }
        }
        return ['total' => count($rows), 'berhasil' => $ok, 'gagal' => count($rows) - $ok, 'hasil' => $hasil];
    }

    /**
     * Ubah parsial. Satu-satunya perubahan status manual yang diizinkan: baru atau aktif menjadi "pindah"
     * (TDD §5.5). Status lain berubah otomatis oleh penempatan dan kenaikan kelas.
     */
    public function ubah(int $id, array $in): array
    {
        $siswa = $this->repo->cariById($id) ?? throw HttpException::notFound('Siswa tidak ditemukan.');

        $v = new Validasi($in);
        $d = SiswaValidator::bidang($v, false);
        $status = $v->enum('status_siswa', 'Status siswa', ['baru', 'aktif', 'lulus', 'pindah'], false);
        $v->selesai();

        if ($status !== null && $status !== $siswa->status) {
            if ($status !== 'pindah') {
                throw HttpException::unprocessable(
                    'Status hanya dapat diubah menjadi pindah. Status lain berubah otomatis oleh penempatan dan kenaikan kelas.',
                    ['status_siswa' => 'Hanya perubahan menjadi pindah yang diizinkan.']
                );
            }
            if (!in_array($siswa->status, ['baru', 'aktif'], true)) {
                throw HttpException::unprocessable(
                    'Hanya siswa berstatus baru atau aktif yang dapat ditandai pindah.',
                    ['status_siswa' => 'Status saat ini: ' . $siswa->status . '.']
                );
            }
            $d['status_siswa'] = 'pindah';
        }

        $this->repo->ubah($id, $d);
        return $this->detail($id);
    }

    /** Ditolak database (409) bila siswa sudah punya riwayat kelas atau data akademik; gunakan status. */
    public function hapus(int $id): void
    {
        $this->repo->cariById($id) ?? throw HttpException::notFound('Siswa tidak ditemukan.');
        $this->repo->hapus($id);
    }
}
