<?php
declare(strict_types=1);

namespace App\Models;

/** Baris akun_pengguna (Admin/Guru). Wali Murid tidak punya akun. */
final class AkunPengguna
{
    public function __construct(
        public readonly int $idUser,
        public readonly string $username,
        public readonly string $kataSandiHash,
        public readonly string $role,            // admin | guru
        public readonly string $statusAkun,      // aktif | nonaktif
        public readonly ?int $kataSandiDiubahTs  // epoch detik; null = belum pernah diubah
    ) {}

    public function aktif(): bool
    {
        return $this->statusAkun === 'aktif';
    }
}
