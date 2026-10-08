<?php
declare(strict_types=1);

namespace App\Models;

final class Siswa
{
    public function __construct(
        public readonly int $idSiswa,
        public readonly string $namaSiswa,
        public readonly string $nisn,
        public readonly string $status   // baru | aktif | lulus | pindah
    ) {}

    public function aktif(): bool
    {
        return $this->status === 'aktif';
    }
}
