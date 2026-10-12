<?php
/**
 * Halaman Login SI-KESA (PWA Guru / Admin)
 * Lokasi: pwa-guru/views/auth/login.php
 *
 * Login memakai API:  POST /api/auth/login  { username, password }  ->  { token, user }
 * Token (JWT, 8 jam) disimpan di localStorage oleh script di bawah. Tidak memakai session PHP.
 *
 * Aset yang dipakai:
 *   assets/vidio/VIDEO BG.mp4      video latar
 *   assets/img/login-poster.jpg    gambar diam (tampil sebelum video siap / saat "kurangi gerakan" aktif)
 *
 * CSS dan JavaScript sudah digabung di file ini, jadi cukup satu file.
 */

// ===== PENGATURAN (ubah di sini) =====
$apiBase = '/backend/public/api';          // alamat dasar API backend, tanpa garis miring di akhir

// Alamat folder pwa-guru/ relatif terhadap halaman ini
// (login.php dibuka langsung -> '../../'; jika di-include oleh pwa-guru/index.php -> '')
$root = (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'login.php') ? '../../' : '';

$tujuan = [
    'guru'  => $root . 'index.php',                    // halaman awal PWA Guru
    'admin' => $root . '../pwa-admin/index.php',       // halaman awal PWA Admin
];

$portal = [
    'guru'  => ['label' => 'Portal Guru',  'judul' => 'Masuk Sebagai Guru',  'sub' => 'Kelola Kegiatan Belajar dan Data Kelas Anda'],
    'admin' => ['label' => 'Portal Admin', 'judul' => 'Masuk Sebagai Admin', 'sub' => 'Kelola Data Sekolah, Guru, dan Pengguna'],
];

// Logo sekolah (tertanam langsung di file ini supaya tidak ada gambar yang gagal dimuat)
$logo = 'data:image/webp;base64,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';

// ===== Fungsi bantu =====
function esc($t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
function aset(string $root, string $path): string {
    // rawurlencode per bagian supaya spasi pada nama file (VIDEO BG.mp4) menjadi %20
    return $root . 'assets/' . implode('/', array_map('rawurlencode', explode('/', $path)));
}

$role = ($_GET['role'] ?? 'guru') === 'admin' ? 'admin' : 'guru';
$config = [
    'apiBase' => $apiBase,
    'tujuan'  => $tujuan,
    'portal'  => $portal,
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Masuk - SI-KESA</title>
<meta name="theme-color" content="#4c7066">
<meta name="robots" content="noindex">
<link rel="manifest" href="<?= esc($root) ?>manifest.json">
<link rel="icon" href="<?= $logo ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* Halaman login SI-KESA (PWA) */
:root{
    --gold:#e0b058;--gold-dark:#c99a3f;
    --field:rgba(255,255,255,.80);--field-focus:rgba(255,255,255,.96);--danger:#ffe1e1;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:'Poppins',sans-serif;color:#fff;background:#6bb4e8;line-height:1.35;overflow-x:hidden}
button,input{font:inherit}
[hidden]{display:none!important}
:focus-visible{outline:3px solid #fff;outline-offset:2px}

/* Video latar */
.bg{position:fixed;inset:0;z-index:-1;overflow:hidden;background:#6bb4e8 var(--poster) center/cover no-repeat}
.bg video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.bg::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,0) 45%,rgba(20,40,35,.18))}
@media (prefers-reduced-motion:reduce){.bg video{display:none}}

/* Semua ukuran memakai vh agar kartu selalu muat di layar tanpa scroll */
.page{min-height:100%;display:flex;align-items:center;justify-content:flex-end;
    padding:max(14px,env(safe-area-inset-top)) 7vw max(14px,env(safe-area-inset-bottom))}

.card{width:min(100%,500px);padding:clamp(12px,2.6vh,24px) clamp(18px,3vw,30px) clamp(14px,3vh,26px);
    border-radius:clamp(18px,3.4vh,26px);
    background:linear-gradient(150deg,rgba(255,255,255,.16) 0%,rgba(38,82,68,.40) 38%,rgba(34,74,62,.46) 100%);
    border:1.5px solid rgba(255,255,255,.55);
    -webkit-backdrop-filter:blur(16px) saturate(1.35);backdrop-filter:blur(16px) saturate(1.35);
    box-shadow:0 18px 50px rgba(15,40,35,.30),inset 0 1px 0 rgba(255,255,255,.65),inset 0 -1px 0 rgba(255,255,255,.12);
    text-align:center}
.brand{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:clamp(6px,1.6vh,14px)}
.brand img{height:clamp(42px,9vh,72px);width:auto;object-fit:contain;filter:drop-shadow(0 3px 5px rgba(0,0,0,.3))}
.brand span{font-size:clamp(1.5rem,4.6vh,2rem);font-weight:600;letter-spacing:1px;text-shadow:0 2px 6px rgba(0,0,0,.3)}

/* Tab peran */
.tabs{display:grid;grid-template-columns:1fr 1fr;gap:5px;padding:clamp(4px,.9vh,7px);margin-bottom:clamp(6px,1.5vh,12px);
    background:rgba(255,255,255,.22);border:1.5px solid rgba(255,255,255,.5);border-radius:clamp(14px,3vh,20px);
    box-shadow:inset 0 1px 3px rgba(0,0,0,.10)}
.tab{display:flex;align-items:center;justify-content:center;gap:6px;padding:clamp(5px,1.2vh,9px) 10px;border:0;
    border-radius:clamp(10px,2.3vh,15px);background:transparent;color:#fff;font-size:clamp(1.1rem,3.6vh,1.5rem);font-weight:600;
    cursor:pointer;text-shadow:0 2px 4px rgba(0,0,0,.35);transition:background .2s}
.tab svg{width:clamp(18px,3.8vh,26px);height:clamp(18px,3.8vh,26px);flex:none;filter:drop-shadow(0 1px 2px rgba(0,0,0,.35))}
.tab[aria-selected="true"]{background:linear-gradient(180deg,#ecc06a,#dcab4e);box-shadow:0 3px 10px rgba(180,130,30,.45)}
.tab:not([aria-selected="true"]):hover{background:rgba(255,255,255,.28)}

.chip{display:inline-block;padding:1px 18px;margin-bottom:clamp(4px,1vh,8px);border-radius:999px;background:rgba(255,255,255,.22);border:1px solid rgba(255,255,255,.5);
    font-size:clamp(.78rem,2.1vh,.95rem);font-weight:500;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.3)}
h1{font-size:clamp(1.3rem,4vh,1.8rem);font-weight:700;line-height:1.2;text-shadow:0 2px 8px rgba(0,0,0,.40)}
.sub{font-size:clamp(.7rem,1.9vh,.85rem);font-weight:600;margin:1px 0 clamp(8px,2vh,16px);text-shadow:0 1px 4px rgba(0,0,0,.5)}

/* Form */
form{text-align:left}
.field{margin-bottom:clamp(7px,1.6vh,14px)}
.field label{display:block;font-size:clamp(.74rem,2vh,.9rem);font-weight:600;margin:0 0 3px 6px;text-shadow:0 1px 4px rgba(0,0,0,.5)}
.input{position:relative}
.input > svg{position:absolute;left:16px;top:50%;width:clamp(18px,3.6vh,24px);height:clamp(18px,3.6vh,24px);transform:translateY(-50%);color:#4d5d56;pointer-events:none}
.input input{display:block;width:100%;height:clamp(40px,7.2vh,56px);padding:0 52px 0 clamp(44px,7vh,54px);border:0;border-radius:12px;
    background:var(--field);color:#1f2d28;font-size:clamp(.9rem,2.4vh,1.05rem);
    border:1px solid rgba(255,255,255,.7);box-shadow:0 2px 8px rgba(15,40,35,.12);transition:background .2s,box-shadow .2s}
.input input::placeholder{color:#6f7f78}
.input input:focus{outline:2px solid var(--gold);outline-offset:1px;background:var(--field-focus)}
.input input:-webkit-autofill{-webkit-box-shadow:0 0 0 40px #f4f6f5 inset;-webkit-text-fill-color:#1f2d28}
.eye{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:38px;height:38px;border:0;border-radius:50%;
    background:transparent;color:#4d5d56;cursor:pointer;display:grid;place-items:center}
.eye:hover{background:rgba(0,0,0,.07)}
.eye svg{width:22px;height:22px}

.alert{padding:7px 14px;margin-bottom:clamp(6px,1.4vh,12px);border-radius:10px;background:var(--danger);color:#7a1717;
    font-size:clamp(.72rem,1.9vh,.85rem);font-weight:600}

.btn{display:block;width:100%;height:clamp(40px,7.2vh,56px);margin-top:clamp(10px,2.4vh,20px);border:0;border-radius:12px;background:linear-gradient(180deg,#ecc06a,#dcab4e);
    color:#fff;font-size:clamp(.95rem,2.6vh,1.15rem);font-weight:600;cursor:pointer;text-shadow:0 1px 3px rgba(0,0,0,.25);
    box-shadow:0 6px 18px rgba(180,130,30,.40),inset 0 1px 0 rgba(255,255,255,.5);transition:filter .2s,transform .1s}
.btn:hover{filter:brightness(.95)}
.btn:active{transform:translateY(1px)}
.btn[disabled]{opacity:.7;cursor:wait}

@media (max-width:900px){.page{justify-content:center;padding-left:5vw;padding-right:5vw}}
@media (max-width:520px){.card{padding-left:16px;padding-right:16px}}
</style>
</head>
<body>

<!-- Video latar belakang -->
<div class="bg" style="--poster:url('<?= esc(aset($root, 'img/login-poster.jpg')) ?>')" aria-hidden="true">
    <video autoplay muted loop playsinline preload="auto" poster="<?= esc(aset($root, 'img/login-poster.jpg')) ?>">
        <source src="<?= esc(aset($root, 'vidio/VIDEO BG.mp4')) ?>" type="video/mp4">
    </video>
</div>

<main class="page">
    <section class="card" aria-labelledby="judul">
        <div class="brand">
            <img src="<?= $logo ?>" alt="Logo SD Negeri Kedungsari 1">
            <span>SI-KESA</span>
        </div>

        <div class="tabs" role="tablist" aria-label="Pilih peran">
            <?php foreach (['guru' => 'GURU', 'admin' => 'ADMIN'] as $k => $label): ?>
                <button type="button" class="tab" role="tab" data-role="<?= $k ?>"
                        aria-selected="<?= $role === $k ? 'true' : 'false' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M19 6v6M16 9h6"/></svg>
                    <?= $label ?>
                </button>
            <?php endforeach; ?>
        </div>

        <span class="chip" id="chip"><?= esc($portal[$role]['label']) ?></span>
        <h1 id="judul"><?= esc($portal[$role]['judul']) ?></h1>
        <p class="sub" id="sub"><?= esc($portal[$role]['sub']) ?></p>

        <form id="formLogin" novalidate>
            <div class="alert" id="alert" role="alert" hidden></div>

            <div class="field">
                <label for="username">Username</label>
                <div class="input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M19 6v6M16 9h6"/></svg>
                    <input type="text" id="username" name="username" required maxlength="50"
                           autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Masukan Username">
                </div>
            </div>

            <div class="field">
                <label for="password">Kata Sandi</label>
                <div class="input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9.5" rx="2.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                    <input type="password" id="password" name="password" required
                           autocomplete="current-password" placeholder="Masukan Kata Sandi">
                    <button type="button" class="eye" id="eye" aria-label="Tampilkan kata sandi" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn" id="btnMasuk"><?= esc($portal[$role]['judul']) ?></button>
        </form>
    </section>
</main>

<script>window.SIKESA_LOGIN = <?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script>
/**
 * Login SI-KESA (PWA Guru / Admin)
 * Memanggil POST {apiBase}/auth/login  dengan body {username, password}
 * dan membaca token + user dari balasan: {token, user} atau {data:{token, user}}.
 * Token disimpan di localStorage (kunci: sikesa_token, sikesa_user).
 * Logout cukup menghapus kedua kunci itu (token tidak dicabut di server; berlaku 8 jam).
 */
(function () {
    'use strict';

    const CFG = window.SIKESA_LOGIN || {};
    const TOKEN_KEY = 'sikesa_token';
    const USER_KEY  = 'sikesa_user';

    const $ = (id) => document.getElementById(id);
    const tabs = document.querySelectorAll('.tab');
    let role = document.querySelector('.tab[aria-selected="true"]')?.dataset.role || 'guru';

    /* ----- Utilitas ----- */
    function tokenMasihBerlaku(token) {
        try {
            const p = JSON.parse(atob(token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')));
            return !p.exp || p.exp * 1000 > Date.now() + 30000;
        } catch (e) { return false; }
    }
    function tampilError(pesan) {
        const a = $('alert');
        a.textContent = pesan || '';
        a.hidden = !pesan;
    }

    /* ----- Sudah login? langsung ke tujuan ----- */
    try {
        const t = localStorage.getItem(TOKEN_KEY);
        const u = JSON.parse(localStorage.getItem(USER_KEY) || 'null');
        if (t && u && CFG.tujuan && CFG.tujuan[u.role] && tokenMasihBerlaku(t)) {
            window.location.replace(CFG.tujuan[u.role]);
            return;
        }
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
    } catch (e) { /* localStorage tidak tersedia: lanjutkan ke form */ }

    /* ----- Ganti peran Guru / Admin ----- */
    function setRole(r) {
        role = r;
        const p = CFG.portal[r];
        tabs.forEach((t) => t.setAttribute('aria-selected', t.dataset.role === r ? 'true' : 'false'));
        $('chip').textContent = p.label;
        $('judul').textContent = p.judul;
        $('sub').textContent = p.sub;
        $('btnMasuk').textContent = p.judul;
        tampilError('');
    }
    tabs.forEach((t) => t.addEventListener('click', () => setRole(t.dataset.role)));

    /* ----- Tampilkan / sembunyikan kata sandi ----- */
    const pw = $('password'), eye = $('eye');
    eye.addEventListener('click', () => {
        const tampil = pw.type === 'password';
        pw.type = tampil ? 'text' : 'password';
        eye.setAttribute('aria-pressed', tampil);
        eye.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });

    /* ----- Kirim login ----- */
    $('formLogin').addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = $('username').value.trim();
        const password = pw.value;
        if (!username || !password) { tampilError('Isi username dan kata sandi.'); return; }

        const btn = $('btnMasuk');
        btn.disabled = true;
        tampilError('');

        try {
            const ctrl = new AbortController();
            const timer = setTimeout(() => ctrl.abort(), 15000);
            const res = await fetch(CFG.apiBase + '/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ username, password }),
                signal: ctrl.signal,
            });
            clearTimeout(timer);

            const body = await res.json().catch(() => ({}));
            const data = body.data || body;

            if (res.ok && data.token && data.user) {
                // Tab yang dipilih harus sama dengan peran akun
                if (data.user.role !== role) {
                    tampilError('Akun ini bukan akun ' + (role === 'guru' ? 'Guru' : 'Admin') + '. Pilih tab yang sesuai.');
                } else {
                    localStorage.setItem(TOKEN_KEY, data.token);
                    localStorage.setItem(USER_KEY, JSON.stringify(data.user));
                    window.location.replace(CFG.tujuan[data.user.role]);
                    return;
                }
            } else if (res.status === 429) {
                tampilError('Terlalu banyak percobaan. Coba lagi beberapa saat lagi.');
            } else if (res.status === 401 || res.status === 422) {
                tampilError(body.message || 'Username atau kata sandi salah.');
            } else {
                tampilError('Server sedang bermasalah. Coba lagi nanti.');
            }
        } catch (err) {
            tampilError(err.name === 'AbortError'
                ? 'Server tidak merespons. Coba lagi.'
                : 'Tidak bisa terhubung ke server. Periksa koneksi internet Anda.');
        }
        btn.disabled = false;
    });

    /* ----- Beberapa browser seluler menahan autoplay: coba putar sekali lagi ----- */
    const v = document.querySelector('.bg video');
    if (v) { v.muted = true; const p = v.play(); if (p && p.catch) p.catch(() => {}); }
})();
</script>
</body>
</html>