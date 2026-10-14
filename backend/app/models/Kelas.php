<?php
declare(strict_types=1);

namespace App\Models;

final class Kelas
{
    public function __construct(
        public readonly int $idKelas,
        public readonly int $idTahunAjaran,
        public readonly string $namaTahun,
        public readonly int $tingkatKelas,
        public readonly string $namaKelas,
        public readonly ?int $idGuruWali,
        public readonly ?string $namaGuruWali,
        public readonly int $jumlahSiswa
    ) {}

    public static function dariBaris(array $r): self
    {
        return new self(
            (int) $r['id_kelas'], (int) $r['id_tahun_ajaran'], (string) $r['nama_tahun'], (int) $r['tingkat_kelas'],
            (string) $r['nama_kelas'], $r['id_guru_wali'] === null ? null : (int) $r['id_guru_wali'],
            $r['nama_guru_wali'], (int) $r['jumlah_siswa']
        );
    }

    public function toArray(): array
    {
        return [
            'id_kelas'        => $this->idKelas,
            'id_tahun_ajaran' => $this->idTahunAjaran,
            'nama_tahun'      => $this->namaTahun,
            'tingkat_kelas'   => $this->tingkatKelas,
            'nama_kelas'      => $this->namaKelas,
            'id_guru_wali'    => $this->idGuruWali,
            'nama_guru_wali'  => $this->namaGuruWali,
            'jumlah_siswa'    => $this->jumlahSiswa,
        ];
    }
}
