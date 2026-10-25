<?php
declare(strict_types=1);

namespace App\Models;

final class Guru
{
    public function __construct(
        public readonly int $idGuru,
        public readonly int $idUser,
        public readonly string $username,
        public readonly string $statusAkun,
        public readonly ?string $nip,
        public readonly string $namaGuru,
        public readonly ?string $jabatan,
        public readonly ?string $fotoProfil,
        public readonly ?string $email,
        public readonly ?string $noTelepon,
        public readonly ?int $idKelasAktif,
        public readonly ?string $kelasAktif
    ) {}

    public static function dariBaris(array $r): self
    {
        return new self(
            (int) $r['id_guru'], (int) $r['id_user'], (string) $r['username'], (string) $r['status_akun'],
            $r['nip'], (string) $r['nama_guru'], $r['jabatan'], $r['foto_profil'], $r['email'], $r['no_telepon'],
            $r['id_kelas_aktif'] === null ? null : (int) $r['id_kelas_aktif'], $r['kelas_aktif']
        );
    }

    public function toArray(): array
    {
        return [
            'id_guru'        => $this->idGuru,
            'id_user'        => $this->idUser,
            'username'       => $this->username,
            'status_akun'    => $this->statusAkun,
            'nip'            => $this->nip,
            'nama_guru'      => $this->namaGuru,
            'jabatan'        => $this->jabatan,
            'foto_profil'    => $this->fotoProfil,
            'email'          => $this->email,
            'no_telepon'     => $this->noTelepon,
            'id_kelas_aktif' => $this->idKelasAktif,
            'kelas_aktif'    => $this->kelasAktif,
        ];
    }
}
