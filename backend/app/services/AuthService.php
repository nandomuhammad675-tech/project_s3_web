<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\HttpException;
use App\Helpers\JwtHelper;
use App\Helpers\RateLimiter;
use App\Models\AkunPengguna;
use App\Repositories\AkunPenggunaRepository;
use App\Repositories\DataSiswaRepository;
use App\Validators\AuthValidator;

final class AuthService
{
    public const PESAN_LOGIN_GAGAL = 'Username atau password salah.';
    /** Pesan SAMA untuk semua penyebab gagal Wali (FR-AUTH-08): tidak membocorkan apakah NISN terdaftar. */
    public const PESAN_WALI_GAGAL  = 'NISN tidak valid atau tidak dapat digunakan.';

    private static ?string $dummyHash = null;

    private AkunPenggunaRepository $akunRepo;
    private DataSiswaRepository $siswaRepo;

    public function __construct(?AkunPenggunaRepository $akunRepo = null, ?DataSiswaRepository $siswaRepo = null)
    {
        $this->akunRepo  = $akunRepo ?? new AkunPenggunaRepository();
        $this->siswaRepo = $siswaRepo ?? new DataSiswaRepository();
    }

    // ------------------------------------------------------------ Admin / Guru

    public function login(string $username, string $password, string $ip): array
    {
        $cfg = Config::get('auth.rate_limit_login');
        $kUser = strlen($username) <= 50 ? 'login:user:' . mb_strtolower($username) : null;
        $kIp   = 'login:ip:' . $ip;

        if (($kUser !== null && RateLimiter::count($kUser, $cfg['window']) >= $cfg['max'])
            || RateLimiter::count($kIp, $cfg['window']) >= $cfg['max_ip']) {
            throw HttpException::tooManyRequests();
        }

        $akun = $this->akunRepo->cariByUsername($username);
        // verifikasi selalu dijalankan (hash dummy bila akun tidak ada) agar waktu respons tidak membocorkan username
        $cocok = password_verify($password, $akun?->kataSandiHash ?? self::dummyHash());

        if ($akun === null || !$cocok || !$akun->aktif()) {
            if ($kUser !== null) {
                RateLimiter::hit($kUser, $cfg['window']);
            }
            RateLimiter::hit($kIp, $cfg['window']);
            throw HttpException::unauthorized(self::PESAN_LOGIN_GAGAL);
        }

        if ($kUser !== null) {
            RateLimiter::clear($kUser);
        }
        return $this->tokenInternal($akun);
    }

    /** Ganti password sendiri (FR-AUTH-11): wajib password lama; token lama otomatis tidak berlaku. */
    public function changePassword(int $idUser, string $lama, string $baru, string $ip): array
    {
        $cfg  = Config::get('auth.rate_limit_login');
        $kKey = 'pw:user:' . $idUser;
        if (RateLimiter::count($kKey, $cfg['window']) >= $cfg['max']) {
            throw HttpException::tooManyRequests();
        }

        $akun = $this->akunRepo->cariById($idUser);
        if ($akun === null || !$akun->aktif()) {
            throw HttpException::unauthorized();
        }

        AuthValidator::passwordBaru($lama, $baru);

        if (!password_verify($lama, $akun->kataSandiHash)) {
            RateLimiter::hit($kKey, $cfg['window']);
            throw HttpException::unprocessable('Password lama salah.', ['password_lama' => 'Password lama salah.']);
        }

        $this->akunRepo->ubahPassword($idUser, password_hash($baru, PASSWORD_DEFAULT));
        RateLimiter::clear($kKey);

        $akunBaru = $this->akunRepo->cariById($idUser);   // token baru diterbitkan SETELAH perubahan
        return $this->tokenInternal($akunBaru ?? $akun);
    }

    private function tokenInternal(AkunPengguna $akun): array
    {
        $ttl = (int) Config::get('auth.ttl_internal', 28800);
        $token = JwtHelper::issue([
            'typ'  => 'internal',
            'sub'  => (string) $akun->idUser,
            'role' => $akun->role,
        ], $ttl);

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'user'       => ['id_user' => $akun->idUser, 'username' => $akun->username, 'role' => $akun->role],
        ];
    }

    private static function dummyHash(): string
    {
        return self::$dummyHash ??= password_hash('dummy-untuk-menyamakan-waktu', PASSWORD_DEFAULT);
    }

    // ------------------------------------------------------------ Wali Murid

    public function loginWali(string $nisn, string $ip): array
    {
        $cfg = Config::get('auth.rate_limit_wali');
        $formatOk = (bool) preg_match('/^\d{10}$/', $nisn);
        $kNisn = $formatOk ? 'wali:nisn:' . $nisn : null;   // hanya NISN berformat benar yang dicatat per-NISN
        $kIp   = 'wali:ip:' . $ip;

        if (($kNisn !== null && RateLimiter::count($kNisn, $cfg['window']) >= $cfg['max'])
            || RateLimiter::count($kIp, $cfg['window']) >= $cfg['max_ip']) {
            throw HttpException::tooManyRequests();
        }

        $siswa = $formatOk ? $this->siswaRepo->cariWaliAktifByNisn($nisn) : null;
        if ($siswa === null) {
            if ($kNisn !== null) {
                RateLimiter::hit($kNisn, $cfg['window']);
            }
            RateLimiter::hit($kIp, $cfg['window']);
            throw HttpException::unauthorized(self::PESAN_WALI_GAGAL);
        }

        if ($kNisn !== null) {
            RateLimiter::clear($kNisn);
        }

        $ttl = (int) Config::get('auth.ttl_wali', 3600);
        $token = JwtHelper::issue(['typ' => 'wali', 'id_siswa' => $siswa->idSiswa], $ttl);

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'siswa'      => ['nama_siswa' => $siswa->namaSiswa],
        ];
    }
}
