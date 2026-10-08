<?php
declare(strict_types=1);

namespace App\Models;

/** Penempatan siswa pada kelas di satu tahun ajaran. nomor_absen BUKAN credential login. */
final class RiwayatKelas
{
    public function __construct(
        public readonly int $idRiwayat,
        public readonly int $idSiswa,
        public readonly string $namaSiswa,
        public readonly string $nisn,
        public readonly int $idKelas,
        public readonly string $namaKelas,
        public readonly int $tingkatKelas,
        public readonly int $idTahunAjaran,
        public readonly string $namaTahun,
        public readonly ?int $nomorAbsen
    ) {}

    public static function dariBaris(array $r): self
    {
        return new self(
            (int) $r['id_riwayat'], (int) $r['id_siswa'], (string) $r['nama_siswa'], (string) $r['nisn_resmi'],
            (int) $r['id_kelas'], (string) $r['nama_kelas'], (int) $r['tingkat_kelas'],
            (int) $r['id_tahun_ajaran'], (string) $r['nama_tahun'],
            $r['nomor_absen'] === null ? null : (int) $r['nomor_absen']
        );
    }

    public function toArray(): array
    {
        return [
            'id_riwayat'      => $this->idRiwayat,
            'id_siswa'        => $this->idSiswa,
            'nama_siswa'      => $this->namaSiswa,
            'nisn_resmi'      => $this->nisn,
            'id_kelas'        => $this->idKelas,
            'nama_kelas'      => $this->namaKelas,
            'tingkat_kelas'   => $this->tingkatKelas,
            'id_tahun_ajaran' => $this->idTahunAjaran,
            'nama_tahun'      => $this->namaTahun,
            'nomor_absen'     => $this->nomorAbsen,
        ];
    }
}
