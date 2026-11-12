<?php
declare(strict_types=1);

/**
 * SI-KESA PWA Guru | konfigurasi bersama (dipakai front controller dan semua view).
 *
 * Susunan folder yang diasumsikan (XAMPP): htdocs/si-kesa/{backend,pwa-admin,pwa-guru,...}
 * Alamat API, PWA Admin, dan PWA Guru DITURUNKAN OTOMATIS dari lokasi folder ini.
 * Ubah lewat salah satu cara (urutan prioritas):
 *   1. berkas config.local.php di folder ini (return array; contoh di bawah)
 *   2. environment: SIKESA_API_BASE, SIKESA_ADMIN_URL
 *
 * Contoh config.local.php:
 *   <?php return ['api_base' => 'https://api.sekolah.sch.id', 'admin_url' => 'https://admin.sekolah.sch.id/'];
 *
 * api_base = alamat folder backend/public TANPA "/api" dan tanpa garis miring akhir.
 */
function sk_cfg(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $script = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $root   = preg_replace('#/views(/.*)?$#', '', $script);        // folder pwa-guru (juga bila view dibuka langsung)
    $induk  = rtrim(str_replace('\\', '/', dirname($root)), '/');   // mis. /si-kesa
    if ($induk === '.' || $induk === '/') {
        $induk = '';
    }

    $cfg = [
        'school'    => 'SDN Kedungsari 1',
        'base'      => $root,
        'api_base'  => rtrim((string) (getenv('SIKESA_API_BASE') ?: $induk . '/backend/public'), '/'),
        'admin_url' => rtrim((string) (getenv('SIKESA_ADMIN_URL') ?: $induk . '/pwa-admin'), '/') . '/',
        'token_key' => 'sikesa_guru_token',
        'user_key'  => 'sikesa_guru_user',
        'admin_token_key' => 'sikesa_admin_token',   // harus sama dengan PWA Admin (apiClient.js)
        'admin_user_key'  => 'sikesa_admin_user',
    ];

    $lokal = __DIR__ . '/config.local.php';
    if (is_file($lokal)) {
        $o = require $lokal;
        if (is_array($o)) {
            $cfg = array_merge($cfg, $o);
            $cfg['api_base'] = rtrim((string) $cfg['api_base'], '/');
            $cfg['admin_url'] = rtrim((string) $cfg['admin_url'], '/') . '/';
        }
    }
    return $cfg;
}

/** Halaman PWA Guru: kunci rute => berkas view. */
function sk_routes(): array
{
    return [
        'dashboard' => 'dashboard/index.php',
        'absensi'   => 'absensi/list.php',
        'nilai'     => 'nilai/input.php',
        'jadwal'    => 'jadwal/index.php',
        'piket'     => 'piket/index.php',
        'disiplin'  => 'disiplin/index.php',
        'laporan'   => 'laporan/index.php',
        'profil'    => 'profil/profil.php',
        'password'  => 'profil/ubah-password.php',
        'login'     => 'auth/login.php',
    ];
}

/** URL halaman, mis. sk_url('absensi') => /si-kesa/pwa-guru/index.php?hal=absensi */
function sk_url(string $hal = 'dashboard'): string
{
    return sk_cfg()['base'] . '/index.php?hal=' . rawurlencode($hal);
}

function sk_e($t): string
{
    return htmlspecialchars((string) $t, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
