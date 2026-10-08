<?php
declare(strict_types=1);

namespace App\Models;

final class TahunAjaran
{
    public function __construct(
        public readonly int $idTahunAjaran,
        public readonly string $namaTahun,
        public readonly string $semesterAktif,   // ganjil | genap
        public readonly bool $statusAktif
    ) {}

    public function toArray(): array
    {
        return [
            'id_tahun_ajaran' => $this->idTahunAjaran,
            'nama_tahun'      => $this->namaTahun,
            'semester_aktif'  => $this->semesterAktif,
            'status_aktif'    => $this->statusAktif,
        ];
    }
}
