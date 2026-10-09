<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Helpers\Pagination;
use App\Helpers\Validasi;
use App\Models\TahunAjaran;
use App\Repositories\KenaikanKelasRepository;
use App\Repositories\LogPromosiSiswaRepository;
use App\Repositories\TahunAjaranRepository;

/**
 * Kenaikan kelas (FR-KELAS-04, 07 sampai 10; BR-13; TDD bab 7).
 *
 * Satu transaksi: semua pemeriksaan dijalankan lebih dulu (gagal => 422, tidak ada data yang berubah), lalu:
 *   tingkat 1-4 => naik ke satu-satunya kelas tingkat berikutnya (dua kelas tingkat 1 digabung ke satu kelas tingkat 2)
 *   tingkat 5   => naik ke tingkat 6 (otomatis bila satu kelas; dipetakan Admin bila dua kelas)
 *   tingkat 6   => status lulus, tanpa penempatan baru
 *   siswa baru yang dipetakan Admin => ditempatkan (hasil "masuk") dan menjadi aktif
 * Tidak ada opsi "tidak naik kelas". Tahun ajaran tujuan TIDAK diaktifkan di sini (PUT .../aktifkan).
 */
final class KenaikanKelasService
{
    private KenaikanKelasRepository $repo;
    private LogPromosiSiswaRepository $log;
    private TahunAjaranRepository $tahunRepo;

    public function __construct()
    {
        $this->repo      = new KenaikanKelasRepository();
        $this->log       = new LogPromosiSiswaRepository();
        $this->tahunRepo = new TahunAjaranRepository();
    }

    // ------------------------------------------------------------------ proses

    public function jalankan(int $idUser, array $in): array
    {
        $v = new Validasi($in);
        $idTujuan = $v->bilangan('id_tahun_ajaran_tujuan', 'Tahun ajaran tujuan', true, 1, 65535);
        $peta6    = $this->bacaPenempatan($v, $in, 'penempatan_tingkat_6');          // null = tidak dikirim
        $petaBaru = $this->bacaPenempatan($v, $in, 'penempatan_siswa_baru') ?? [];
        $v->selesai();

        $asal = $this->tahunRepo->aktif()
            ?? throw HttpException::unprocessable('Belum ada tahun ajaran aktif sebagai tahun asal.');
        $tujuan = $this->tahunRepo->cari((int) $idTujuan)
            ?? throw HttpException::unprocessable('Tahun ajaran tujuan tidak ditemukan.', ['id_tahun_ajaran_tujuan' => 'Tidak ditemukan.']);
        if ($tujuan->statusAktif) {
            throw HttpException::unprocessable('Tahun ajaran tujuan tidak boleh tahun ajaran yang sedang aktif.', ['id_tahun_ajaran_tujuan' => 'Sedang aktif.']);
        }

        return (array) Database::transaksi(fn() => $this->proses($idUser, $asal, $tujuan, $peta6, $petaBaru));
    }

    private function proses(int $idUser, TahunAjaran $asal, TahunAjaran $tujuan, ?array $peta6, array $petaBaru): array
    {
        $this->log->kunciTahun($tujuan->idTahunAjaran);
        if ($this->log->adaUntukTahun($tujuan->idTahunAjaran)) {
            throw HttpException::unprocessable('Kenaikan kelas ke tahun ajaran ini sudah pernah dijalankan.');
        }

        // ---- pemeriksaan awal: salah satu gagal => seluruh proses ditolak, belum ada data yang berubah
        $kelas = $this->repo->kelasTahun($tujuan->idTahunAjaran);
        $this->periksaStruktur($kelas);

        $tanpa = $this->repo->siswaAktifTanpaPenempatan($asal->idTahunAjaran);
        if ($tanpa) {
            throw HttpException::unprocessable(
                'Ada siswa aktif tanpa penempatan kelas pada tahun ajaran asal. Tempatkan siswa tersebut terlebih dahulu.',
                ['siswa_tanpa_penempatan' => $tanpa]
            );
        }

        $perTingkat = [];
        foreach ($kelas as $k) {
            $perTingkat[$k['tingkat_kelas']][] = $k;
        }
        $siswaAsal = $this->repo->siswaAktifPada($asal->idTahunAjaran);
        $peta6Final = $this->petakanTingkat5($perTingkat[6], $siswaAsal, $peta6);
        $this->periksaSiswaBaru($petaBaru, $kelas);

        // ---- eksekusi
        $ringkas = ['naik' => 0, 'lulus' => 0, 'masuk' => 0];
        $perKelas = [];
        $idAsal = $asal->idTahunAjaran;
        $idTuj  = $tujuan->idTahunAjaran;

        foreach ($siswaAsal as $s) {
            $t = $s['tingkat_kelas'];
            if ($t === 6) {
                $this->repo->setStatus($s['id_siswa'], 'lulus');
                $this->log->catat($s['id_siswa'], $idAsal, $idTuj, $s['id_kelas'], null, 'lulus', $idUser);
                $ringkas['lulus']++;
                continue;
            }
            $idKelasTujuan = $t === 5 ? $peta6Final[$s['id_siswa']] : $perTingkat[$t + 1][0]['id_kelas'];
            $this->repo->buatRiwayat($s['id_siswa'], $idKelasTujuan, $idTuj);
            $this->log->catat($s['id_siswa'], $idAsal, $idTuj, $s['id_kelas'], $idKelasTujuan, 'naik', $idUser);
            $ringkas['naik']++;
            $perKelas[$idKelasTujuan]['naik'] = ($perKelas[$idKelasTujuan]['naik'] ?? 0) + 1;
        }

        foreach ($petaBaru as $p) {
            $this->repo->buatRiwayat($p['id_siswa'], $p['id_kelas_tujuan'], $idTuj);
            $this->repo->setStatus($p['id_siswa'], 'aktif');
            $this->log->catat($p['id_siswa'], null, $idTuj, null, $p['id_kelas_tujuan'], 'masuk', $idUser);
            $ringkas['masuk']++;
            $perKelas[$p['id_kelas_tujuan']]['masuk'] = ($perKelas[$p['id_kelas_tujuan']]['masuk'] ?? 0) + 1;
        }

        $namaKelas = [];
        foreach ($kelas as $k) {
            $namaKelas[$k['id_kelas']] = $k;
        }
        $rincian = [];
        foreach ($perKelas as $idKelas => $n) {
            $rincian[] = [
                'id_kelas'      => $idKelas,
                'nama_kelas'    => $namaKelas[$idKelas]['nama_kelas'],
                'tingkat_kelas' => $namaKelas[$idKelas]['tingkat_kelas'],
                'naik'          => $n['naik'] ?? 0,
                'masuk'         => $n['masuk'] ?? 0,
            ];
        }
        usort($rincian, static fn(array $a, array $b): int => [$a['tingkat_kelas'], $a['nama_kelas']] <=> [$b['tingkat_kelas'], $b['nama_kelas']]);

        $ringkas['siswa_baru_belum_dipetakan'] = $this->repo->hitungSiswaBaru();

        return [
            'tahun_asal'      => $asal->namaTahun,
            'tahun_tujuan'    => $tujuan->namaTahun,
            'id_tahun_tujuan' => $idTuj,
            'ringkasan'       => $ringkas,
            'per_kelas_tujuan' => $rincian,
            'langkah_berikutnya' => 'Segera aktifkan tahun ajaran tujuan: PUT /api/admin/tahun-ajaran/' . $idTuj
                . '/aktifkan (sebaiknya di luar jam sekolah). Selama jeda, siswa lulus dan siswa baru hasil masuk belum dapat login sebagai Wali.',
        ];
    }

    // ------------------------------------------------------------------ pemeriksaan

    /**
     * Struktur rombel tahun tujuan (FR-KELAS-08): tingkat 2-5 tepat satu kelas; tingkat 6 satu atau dua; tingkat 1 boleh nol,
     * satu, atau dua. Setiap kelas harus punya wali yang akunnya AKTIF (FR-KELAS-04, BR-13).
     */
    private function periksaStruktur(array $kelas): void
    {
        $jumlah = array_fill(1, 6, 0);
        foreach ($kelas as $k) {
            $jumlah[$k['tingkat_kelas']]++;
        }

        $struktur = [];
        for ($t = 2; $t <= 5; $t++) {
            if ($jumlah[$t] !== 1) {
                $struktur[] = 'Tingkat ' . $t . ' memiliki ' . $jumlah[$t] . ' kelas pada tahun ajaran tujuan (harus tepat satu).';
            }
        }
        if ($jumlah[6] < 1 || $jumlah[6] > 2) {
            $struktur[] = 'Tingkat 6 memiliki ' . $jumlah[6] . ' kelas pada tahun ajaran tujuan (harus satu atau dua).';
        }
        if ($jumlah[1] > 2) {
            $struktur[] = 'Tingkat 1 memiliki ' . $jumlah[1] . ' kelas pada tahun ajaran tujuan (maksimal dua).';
        }
        if ($struktur) {
            throw HttpException::unprocessable('Struktur kelas tahun ajaran tujuan belum memenuhi syarat.', ['struktur_kelas' => $struktur]);
        }

        $wali = [];
        foreach ($kelas as $k) {
            if ($k['id_guru_wali'] === null) {
                $wali[] = 'Kelas ' . $k['nama_kelas'] . ' (tingkat ' . $k['tingkat_kelas'] . ') belum memiliki wali kelas.';
            } elseif ($k['status_akun_wali'] !== 'aktif') {
                $wali[] = 'Wali kelas ' . $k['nama_kelas'] . ' (' . $k['nama_guru_wali'] . ') berakun nonaktif. Tetapkan wali pengganti berakun aktif.';
            }
        }
        if ($wali) {
            throw HttpException::unprocessable('Ada kelas tahun ajaran tujuan tanpa wali kelas yang dapat login.', ['wali_kelas' => $wali]);
        }
    }

    /**
     * Tentukan kelas tingkat 6 untuk tiap siswa tingkat 5 (FR-KELAS-09).
     * Satu kelas tingkat 6: otomatis, dan penempatan_tingkat_6 TIDAK boleh dikirim (422).
     * Dua kelas: setiap siswa aktif tingkat 5 harus dipetakan tepat satu kali.
     * @return array<int, int> id_siswa => id_kelas tujuan
     */
    private function petakanTingkat5(array $kelas6, array $siswaAsal, ?array $peta6): array
    {
        $siswa5 = array_values(array_filter($siswaAsal, static fn(array $s): bool => $s['tingkat_kelas'] === 5));

        if (count($kelas6) === 1) {
            if ($peta6 !== null) {
                throw HttpException::unprocessable(
                    'penempatan_tingkat_6 tidak boleh dikirim karena tahun ajaran tujuan hanya memiliki satu kelas tingkat 6; penempatan dilakukan otomatis.',
                    ['penempatan_tingkat_6' => 'Tidak boleh dikirim.']
                );
            }
            $hasil = [];
            foreach ($siswa5 as $s) {
                $hasil[$s['id_siswa']] = $kelas6[0]['id_kelas'];
            }
            return $hasil;
        }

        $id5      = array_column($siswa5, 'id_siswa');
        $idKelas6 = array_column($kelas6, 'id_kelas');
        $hasil = [];
        $err   = [];
        foreach ($peta6 ?? [] as $p) {
            if (!in_array($p['id_siswa'], $id5, true)) {
                $err[] = 'Siswa ' . $p['id_siswa'] . ' bukan siswa aktif tingkat 5 pada tahun ajaran asal.';
            } elseif (!in_array($p['id_kelas_tujuan'], $idKelas6, true)) {
                $err[] = 'Kelas ' . $p['id_kelas_tujuan'] . ' bukan kelas tingkat 6 pada tahun ajaran tujuan.';
            } else {
                $hasil[$p['id_siswa']] = $p['id_kelas_tujuan'];
            }
        }
        $belum = array_filter($siswa5, static fn(array $s): bool => !isset($hasil[$s['id_siswa']]));
        if ($belum) {
            $err[] = 'Siswa tingkat 5 belum dipetakan: ' . implode(', ', array_map(static fn(array $s): string => $s['nama_siswa'] . ' (' . $s['id_siswa'] . ')', $belum)) . '.';
        }
        if ($err) {
            throw HttpException::unprocessable('Penempatan siswa tingkat 5 ke kelas tingkat 6 tidak valid.', ['penempatan_tingkat_6' => $err]);
        }
        return $hasil;
    }

    /** Siswa baru yang dipetakan: harus berstatus baru dan kelas tujuannya milik tahun ajaran tujuan (tingkat berapa pun). */
    private function periksaSiswaBaru(array $petaBaru, array $kelasTujuan): void
    {
        if (!$petaBaru) {
            return;
        }
        $siswa    = $this->repo->siswaByIds(array_column($petaBaru, 'id_siswa'));
        $idKelas  = array_column($kelasTujuan, 'id_kelas');
        $err = [];
        foreach ($petaBaru as $p) {
            $s = $siswa[$p['id_siswa']] ?? null;
            if ($s === null) {
                $err[] = 'Siswa ' . $p['id_siswa'] . ' tidak ditemukan.';
            } elseif ($s['status_siswa'] !== 'baru') {
                $err[] = 'Siswa ' . $s['nama_siswa'] . ' berstatus ' . $s['status_siswa'] . '; hanya siswa berstatus baru yang dapat dipetakan.';
            }
            if (!in_array($p['id_kelas_tujuan'], $idKelas, true)) {
                $err[] = 'Kelas ' . $p['id_kelas_tujuan'] . ' bukan kelas pada tahun ajaran tujuan.';
            }
        }
        if ($err) {
            throw HttpException::unprocessable('Penempatan siswa baru tidak valid.', ['penempatan_siswa_baru' => $err]);
        }
    }

    // ------------------------------------------------------------------ input

    /**
     * Daftar {id_siswa, id_kelas_tujuan}. Kunci tidak ada atau null => null (tidak dikirim).
     * @return array<int, array{id_siswa:int, id_kelas_tujuan:int}>|null
     */
    private function bacaPenempatan(Validasi $v, array $in, string $kunci): ?array
    {
        if (!array_key_exists($kunci, $in) || $in[$kunci] === null) {
            return null;
        }
        if (!is_array($in[$kunci])) {
            $v->error($kunci, 'Harus berupa daftar {id_siswa, id_kelas_tujuan}.');
            return [];
        }

        $hasil = [];
        $terlihat = [];
        foreach (array_values($in[$kunci]) as $i => $it) {
            $k = $kunci . '[' . $i . ']';
            if (!is_array($it)) {
                $v->error($k, 'Setiap item harus berupa objek {id_siswa, id_kelas_tujuan}.');
                continue;
            }
            $s  = self::angka($it['id_siswa'] ?? null);
            $kt = self::angka($it['id_kelas_tujuan'] ?? null);
            if ($s === null) {
                $v->error($k . '.id_siswa', 'id_siswa harus berupa angka positif.');
            }
            if ($kt === null) {
                $v->error($k . '.id_kelas_tujuan', 'id_kelas_tujuan harus berupa angka positif.');
            }
            if ($s === null || $kt === null) {
                continue;
            }
            if (isset($terlihat[$s])) {
                $v->error($k . '.id_siswa', 'Siswa ' . $s . ' muncul lebih dari sekali.');
                continue;
            }
            $terlihat[$s] = true;
            $hasil[] = ['id_siswa' => $s, 'id_kelas_tujuan' => $kt];
        }
        return $hasil;
    }

    private static function angka(mixed $x): ?int
    {
        if (is_string($x) && ctype_digit($x)) {
            $x = (int) $x;
        }
        return is_int($x) && $x > 0 ? $x : null;
    }

    // ------------------------------------------------------------------ log

    /** GET /api/admin/kenaikan-kelas/log: ringkasan dan daftar hasil proses ke satu tahun ajaran tujuan. */
    public function lihatLog(int $idTahunTujuan, array $p): array
    {
        $tahun = $this->tahunRepo->cari($idTahunTujuan) ?? throw HttpException::notFound('Tahun ajaran tidak ditemukan.');

        return [
            'tahun_ajaran_tujuan' => $tahun->toArray(),
            'ringkasan'           => $this->log->ringkasan($idTahunTujuan),
            'per_kelas_tujuan'    => $this->log->perKelasTujuan($idTahunTujuan),
        ] + Pagination::hasil($this->log->daftar($idTahunTujuan, $p['limit'], $p['offset']), $this->log->total($idTahunTujuan), $p);
    }
}
