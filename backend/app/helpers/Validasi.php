<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Core\HttpException;

/**
 * Pengumpul kesalahan validasi input JSON. Semua kesalahan dikumpulkan lalu dilempar sekaligus
 * sebagai 422 dengan daftar per-field (selesai()).
 */
final class Validasi
{
    private array $err = [];

    public function __construct(private array $in) {}

    public function ada(string $k): bool
    {
        return array_key_exists($k, $this->in);
    }

    public function error(string $k, string $pesan): void
    {
        $this->err[$k] = $pesan;
    }

    /** Teks (dipangkas spasi). Kosong/tidak ada => null (dan error bila wajib). */
    public function string(string $k, string $label, bool $wajib, int $max, int $min = 1): ?string
    {
        $v = $this->in[$k] ?? null;
        if ($v === null || (is_string($v) && trim($v) === '')) {
            if ($wajib) {
                $this->err[$k] = $label . ' wajib diisi.';
            }
            return null;
        }
        if (!is_string($v)) {
            $this->err[$k] = $label . ' harus berupa teks.';
            return null;
        }
        $v = trim($v);
        $len = mb_strlen($v);
        if ($len > $max) {
            $this->err[$k] = $label . ' maksimal ' . $max . ' karakter.';
            return null;
        }
        if ($len < $min) {
            $this->err[$k] = $label . ' minimal ' . $min . ' karakter.';
            return null;
        }
        return $v;
    }

    /** Teks mentah TANPA dipangkas (untuk password). */
    public function rahasia(string $k, string $label, bool $wajib = true): ?string
    {
        $v = $this->in[$k] ?? null;
        if (!is_string($v) || $v === '') {
            if ($wajib) {
                $this->err[$k] = $label . ' wajib diisi.';
            }
            return null;
        }
        return $v;
    }

    public function tanggal(string $k, string $label, bool $wajib): ?string
    {
        $v = $this->string($k, $label, $wajib, 10, 10);
        if ($v !== null && !self::adalahTanggal($v)) {
            $this->err[$k] = $label . ' harus berformat YYYY-MM-DD dan berupa tanggal yang valid.';
            return null;
        }
        return $v;
    }

    public function email(string $k, string $label, bool $wajib, int $max = 100): ?string
    {
        $v = $this->string($k, $label, $wajib, $max);
        if ($v !== null && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->err[$k] = $label . ' tidak valid.';
            return null;
        }
        return $v;
    }

    public function telepon(string $k, string $label, bool $wajib, int $max): ?string
    {
        $v = $this->string($k, $label, $wajib, $max, 5);
        if ($v !== null && !preg_match('/^[0-9+\-\s()]+$/', $v)) {
            $this->err[$k] = $label . ' hanya boleh berisi angka, spasi, +, -, dan tanda kurung.';
            return null;
        }
        return $v;
    }

    /** Bilangan bulat dalam rentang [min, max]. Menerima angka atau string angka. */
    public function bilangan(string $k, string $label, bool $wajib, int $min, int $max): ?int
    {
        $v = $this->in[$k] ?? null;
        if ($v === null || $v === '') {
            if ($wajib) {
                $this->err[$k] = $label . ' wajib diisi.';
            }
            return null;
        }
        if (is_string($v) && ctype_digit($v)) {
            $v = (int) $v;
        }
        if (!is_int($v) || $v < $min || $v > $max) {
            $this->err[$k] = $label . ' harus berupa bilangan bulat antara ' . $min . ' dan ' . $max . '.';
            return null;
        }
        return $v;
    }

    public function enum(string $k, string $label, array $boleh, bool $wajib): ?string
    {
        $v = $this->in[$k] ?? null;
        if ($v === null || $v === '') {
            if ($wajib) {
                $this->err[$k] = $label . ' wajib diisi.';
            }
            return null;
        }
        if (!is_string($v) || !in_array($v, $boleh, true)) {
            $this->err[$k] = $label . ' harus salah satu dari: ' . implode(', ', $boleh) . '.';
            return null;
        }
        return $v;
    }

    /** Daftar id positif. Kunci tidak ada => null; ada tetapi kosong => []. */
    public function daftarId(string $k, string $label): ?array
    {
        if (!$this->ada($k)) {
            return null;
        }
        $v = $this->in[$k];
        if (!is_array($v)) {
            $this->err[$k] = $label . ' harus berupa daftar id.';
            return [];
        }
        $hasil = [];
        foreach ($v as $x) {
            if (is_int($x) && $x > 0) {
                $hasil[] = $x;
            } elseif (is_string($x) && ctype_digit($x) && (int) $x > 0) {
                $hasil[] = (int) $x;
            } else {
                $this->err[$k] = $label . ' harus berisi angka id yang valid.';
                return [];
            }
        }
        return array_values(array_unique($hasil));
    }

    /** Boolean (true/false/1/0). Tidak dikirim atau null => $default. */
    public function bool(string $k, string $label, ?bool $default = null): ?bool
    {
        $v = $this->in[$k] ?? null;
        if ($v === null) {
            return $default;
        }
        if (is_bool($v)) {
            return $v;
        }
        if ($v === 1 || $v === 0 || $v === '1' || $v === '0') {
            return (bool) (int) $v;
        }
        $this->err[$k] = $label . ' harus berupa true atau false.';
        return $default;
    }

    public function selesai(): void
    {
        if ($this->err) {
            throw HttpException::unprocessable('Validasi gagal.', $this->err);
        }
    }

    public static function adalahTanggal(string $s): bool
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m)) {
            return false;
        }
        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }
}
