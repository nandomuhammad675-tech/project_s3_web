<?php
/**
 * SI-KESA PWA Admin | halaman login (desain yang sama dengan PWA Guru).
 * Dirender mandiri oleh index.php (tanpa kerangka sidebar). Variabel $config dari index.php.
 *
 * @var array $config Dibuat di pwa-admin/index.php
 */
function esc($t): string { return e((string) $t); }
function aset(string $path): string {
    return url('assets/' . implode('/', array_map('rawurlencode', explode('/', $path))));
}

$role   = ($_GET['role'] ?? 'admin') === 'guru' ? 'guru' : 'admin';
$portal = [
    'guru'  => ['judul' => 'Masuk Sebagai Guru',  'sub' => 'Kelola kegiatan belajar dan data kelas Anda'],
    'admin' => ['judul' => 'Masuk Sebagai Admin', 'sub' => 'Kelola data sekolah, guru, dan pengguna'],
];
$config = [
    'apiBase'     => $config['apiBase'],
    'defaultRole' => 'admin',
    'demo'        => false,
    'tujuan'      => ['admin' => url('dashboard'), 'guru' => $config['guruHome']],
    'portal'      => $portal,
    'keys'        => [
        'guru'  => ['token' => 'sikesa_guru_token',  'user' => 'sikesa_guru_user'],
        'admin' => ['token' => 'sikesa_admin_token', 'user' => 'sikesa_admin_user'],
    ],
];
$ROLE_TITLE = 'Admin';
$LOGO     = esc(aset('img/logo_sekolah.png'));
$VIDEO    = esc(aset('vidio/VIDEO BG.mp4'));
$MANIFEST = esc(url('manifest.json'));
$PWAJS    = esc(url('assets/js/pwa-install.js'));
$SW       = esc(url('sw.js'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Masuk | SI-KESA <?= $ROLE_TITLE ?></title>
<meta name="theme-color" content="#455a2d">
<meta name="robots" content="noindex">
<link rel="manifest" href="<?= $MANIFEST ?>">
<link rel="icon" href="<?= $LOGO ?>">
<link rel="apple-touch-icon" href="<?= $LOGO ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
/* Halaman login SI-KESA: dipakai bersama oleh PWA Guru dan PWA Admin */
:root{
    --green:#455a2d;--green-dark:#35471f;--gold:#eba048;--gold-dark:#d98c2e;
    --glass:rgba(41,56,26,.80);--line:rgba(255,255,255,.22);--field:#f4f3ee;--danger:#ffe3e0;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:'Poppins',sans-serif;color:#fff;background:linear-gradient(135deg,#2f4220,#587a3c);line-height:1.4;overflow-x:hidden}
button,input{font:inherit}
[hidden]{display:none!important}
:focus-visible{outline:3px solid var(--gold);outline-offset:2px}

.bg{position:fixed;inset:0;z-index:-1;overflow:hidden}
.bg video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.bg::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(20,32,12,.10) 40%,rgba(20,32,12,.45))}
@media (prefers-reduced-motion:reduce){.bg video{display:none}}

.page{min-height:100%;display:flex;align-items:center;justify-content:flex-end;
    padding:max(18px,env(safe-area-inset-top)) 7vw max(18px,env(safe-area-inset-bottom))}

.card{width:min(100%,440px);padding:30px 30px 24px;border-radius:26px;background:var(--glass);
    border:1px solid var(--line);-webkit-backdrop-filter:blur(14px);backdrop-filter:blur(14px);
    box-shadow:0 24px 60px rgba(0,0,0,.35),inset 0 1px 0 rgba(255,255,255,.12)}
.brand{display:flex;align-items:center;gap:14px;margin-bottom:20px}
.brand img{height:64px;width:auto;object-fit:contain;filter:drop-shadow(0 3px 6px rgba(0,0,0,.35))}
.brand b{display:block;font-size:1.55rem;font-weight:700;letter-spacing:.5px;line-height:1.1}
.brand small{display:block;font-size:.76rem;font-weight:500;opacity:.85;margin-top:2px}

.tabs{display:grid;grid-template-columns:1fr 1fr;gap:4px;padding:4px;margin-bottom:20px;background:rgba(255,255,255,.14);border-radius:14px}
.tab{display:flex;align-items:center;justify-content:center;gap:7px;padding:9px 10px;border:0;border-radius:10px;background:transparent;
    color:#fff;font-size:.9rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s}
.tab svg{width:18px;height:18px;flex:none}
.tab[aria-selected="true"]{background:var(--gold);color:#2b2108;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.tab:not([aria-selected="true"]):hover{background:rgba(255,255,255,.18)}

h1{font-size:1.5rem;font-weight:700;line-height:1.2}
.sub{font-size:.82rem;font-weight:400;opacity:.88;margin:4px 0 20px}

form{text-align:left}
.field{margin-bottom:14px}
.field label{display:block;font-size:.8rem;font-weight:600;margin:0 0 6px 2px}
.input{position:relative}
.input > svg{position:absolute;left:14px;top:50%;width:20px;height:20px;transform:translateY(-50%);color:#6b7560;pointer-events:none}
.input input{display:block;width:100%;height:50px;padding:0 48px 0 44px;border:2px solid transparent;border-radius:12px;
    background:var(--field);color:#222;font-size:.95rem;transition:border-color .15s,background .15s}
.input input::placeholder{color:#8a8f84}
.input input:focus{outline:none;border-color:var(--gold);background:#fff}
.eye{position:absolute;right:6px;top:50%;transform:translateY(-50%);width:38px;height:38px;border:0;border-radius:50%;
    background:transparent;color:#5d6656;cursor:pointer;display:grid;place-items:center}
   input[type="password"]::-ms-reveal,input[type="password"]::-ms-clear{display:none}

.alert{padding:10px 14px;margin-bottom:14px;border-radius:10px;background:var(--danger);color:#7a1717;font-size:.82rem;font-weight:600}

.btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:52px;margin-top:20px;border:0;border-radius:12px;
    background:var(--gold);color:#2b2108;font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s,transform .05s}
.btn:hover{background:var(--gold-dark)}
.btn:active{transform:scale(.99)}
.btn[disabled]{opacity:.7;cursor:wait}
.spin{width:18px;height:18px;border:3px solid rgba(43,33,8,.25);border-top-color:#2b2108;border-radius:50%;animation:s .8s linear infinite}
@keyframes s{to{transform:rotate(360deg)}}

.install{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:46px;margin-top:10px;border:1.5px solid rgba(255,255,255,.55);
    border-radius:12px;background:transparent;color:#fff;font-size:.9rem;font-weight:600;cursor:pointer}
.install:hover{background:rgba(255,255,255,.14)}
.install svg{width:18px;height:18px}
.help{margin-top:16px;text-align:center;font-size:.76rem;opacity:.85}

@media (max-width:900px){.page{justify-content:center;padding-left:5vw;padding-right:5vw}}
@media (max-width:520px){.card{padding:24px 20px 20px}.brand img{height:54px}}
</style>
</head>
<body>

<div class="bg" aria-hidden="true">
    <video autoplay muted loop playsinline preload="auto">
        <source src="<?= $VIDEO ?>" type="video/mp4">
    </video>
</div>

<main class="page">
    <section class="card" aria-labelledby="judul">
        <div class="brand">
            <img src="<?= $LOGO ?>" alt="Logo SD Negeri Kedungsari 1">
            <div><b>SI-KESA</b><small>SD Negeri Kedungsari 1</small></div>
        </div>

        <div class="tabs" role="tablist" aria-label="Pilih peran">
            <?php foreach (['guru' => 'GURU', 'admin' => 'ADMIN'] as $k => $label): ?>
                <button type="button" class="tab" role="tab" data-role="<?= $k ?>" aria-selected="<?= $role === $k ? 'true' : 'false' ?>">
                    <?php if ($k === 'guru'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 2.5 9 2.5 12 0v-5"/></svg>
                    <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>
                    <?php endif; ?>
                    <?= $label ?>
                </button>
            <?php endforeach; ?>
        </div>

        <h1 id="judul"><?= esc($portal[$role]['judul']) ?></h1>
        <p class="sub" id="sub"><?= esc($portal[$role]['sub']) ?></p>

        <form id="formLogin" method="post" action="#" novalidate>
            <div class="alert" id="alert" role="alert" hidden></div>

            <div class="field">
                <label for="username">Username</label>
                <div class="input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0 1 7-7h2a7 7 0 0 1 7 7v1"/></svg>
                    <input type="text" id="username" name="username" required maxlength="50" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Masukkan username">
                </div>
            </div>

            <div class="field">
                <label for="password">Kata Sandi</label>
                <div class="input">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi">
                    <button type="button" class="eye" id="eye" aria-label="Tampilkan kata sandi" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn" id="btnMasuk"><span id="btnTeks">Masuk</span></button>
        </form>

        <button type="button" class="install" data-pwa-install hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 21h14"/></svg>
            Pasang Aplikasi
        </button>
        <p class="help">Lupa password? Hubungi Admin sekolah.</p>
    </section>
</main>

<script>window.SIKESA_LOGIN = <?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script>
/**
 * Login SI-KESA (dipakai PWA Guru dan PWA Admin). POST {apiBase}/auth/login {username, password}.
 * Token disimpan di localStorage dengan kunci sesuai peran. Logout hanya menghapus token di klien (berlaku 8 jam).
 * Kredensial TIDAK disimpan oleh aplikasi: penyimpanan dan pengisian otomatis diserahkan ke pengelola
 * kata sandi browser (atribut autocomplete + Credential Management API), seperti PWA pada umumnya.
 */
(function () {
    'use strict';
    const CFG = window.SIKESA_LOGIN || {};
    const KEYS = CFG.keys;
    const $ = (id) => document.getElementById(id);
    const tabs = document.querySelectorAll('.tab');
    let role = document.querySelector('.tab[aria-selected="true"]')?.dataset.role || CFG.defaultRole || 'guru';

    function tokenMasihBerlaku(token) {
        try {
            const p = JSON.parse(atob(token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')));
            return !p.exp || p.exp * 1000 > Date.now() + 30000;
        } catch (e) { return false; }
    }
    function tampilError(pesan) { const a = $('alert'); a.textContent = pesan || ''; a.hidden = !pesan; }
    function sibuk(on) {
        $('btnMasuk').disabled = on;
        $('btnTeks').textContent = on ? 'Memproses...' : 'Masuk';
        const sp = $('btnMasuk').querySelector('.spin');
        if (on && !sp) { const s = document.createElement('span'); s.className = 'spin'; $('btnMasuk').prepend(s); }
        if (!on && sp) sp.remove();
    }

    /* Pesan dari sesi sebelumnya (mis. sesi berakhir) */
    let adaPesan = false;
    try {
        const pesan = sessionStorage.getItem('sikesa_login_msg');
        if (pesan) { tampilError(pesan); sessionStorage.removeItem('sikesa_login_msg'); adaPesan = true; }
    } catch (e) { /* abaikan */ }

    /* Sudah login (peran aplikasi ini) dan token masih berlaku? langsung ke tujuan */
    try {
        const r = CFG.defaultRole;
        const t = localStorage.getItem(KEYS[r].token);
        const u = JSON.parse(localStorage.getItem(KEYS[r].user) || 'null');
        if (t && u && u.role === r && tokenMasihBerlaku(t) && !adaPesan && !CFG.demo) {
            window.location.replace(CFG.tujuan[r]);
            return;
        }
        localStorage.removeItem(KEYS[r].token);
        localStorage.removeItem(KEYS[r].user);
    } catch (e) { /* localStorage tidak tersedia: lanjutkan ke form */ }

    function setRole(r) {
        role = r;
        const p = CFG.portal[r];
        tabs.forEach((t) => t.setAttribute('aria-selected', t.dataset.role === r ? 'true' : 'false'));
        $('judul').textContent = p.judul;
        $('sub').textContent = p.sub;
        tampilError('');
    }
    tabs.forEach((t) => t.addEventListener('click', () => setRole(t.dataset.role)));

    const pw = $('password'), eye = $('eye');
    eye.addEventListener('click', () => {
        const tampil = pw.type === 'password';
        pw.type = tampil ? 'text' : 'password';
        eye.setAttribute('aria-pressed', tampil);
        eye.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });

    /* Isi otomatis dari pengelola kata sandi browser (bila tersedia dan pengguna mengizinkan) */
    if (window.PasswordCredential && navigator.credentials && navigator.credentials.get) {
        navigator.credentials.get({ password: true, mediation: 'optional' }).then((c) => {
            if (c && c.type === 'password' && !$('username').value) { $('username').value = c.id; pw.value = c.password || ''; }
        }).catch(() => {});
    }
    function simpanKredensial(username, password) {
        try {
            if (window.PasswordCredential && navigator.credentials && navigator.credentials.store) {
                navigator.credentials.store(new PasswordCredential({ id: username, password: password, name: username }));
            }
        } catch (e) { /* abaikan */ }
    }

    $('formLogin').addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = $('username').value.trim();
        const password = pw.value;
        if (!username || !password) { tampilError('Isi username dan kata sandi.'); return; }
        sibuk(true); tampilError('');
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
                if (data.user.role !== role) {
                    tampilError('Akun ini bukan akun ' + (role === 'guru' ? 'Guru' : 'Admin') + '. Pilih tab yang sesuai.');
                } else {
                    const k = KEYS[data.user.role];
                    localStorage.setItem(k.token, data.token);
                    localStorage.setItem(k.user, JSON.stringify({ username: data.user.username, nama: '', role: data.user.role }));
                    simpanKredensial(username, password);
                    window.location.replace(CFG.tujuan[data.user.role]);
                    return;
                }
            } else if (res.status === 429) {
                tampilError('Terlalu banyak percobaan. Coba lagi beberapa saat lagi.');
            } else if (res.status === 401 || res.status === 422) {
                tampilError(body.message || 'Username atau kata sandi salah.');
                pw.value = '';
            } else {
                tampilError('Server sedang bermasalah. Coba lagi nanti.');
            }
        } catch (err) {
            tampilError(err.name === 'AbortError' ? 'Server tidak merespons. Coba lagi.' : 'Tidak bisa terhubung ke server. Periksa koneksi internet Anda.');
        }
        sibuk(false);
    });

    const v = document.querySelector('.bg video');
    if (v) { v.muted = true; const p = v.play(); if (p && p.catch) p.catch(() => {}); }
})();
</script>
<script src="<?= $PWAJS ?>"></script>
<script>
    if ('serviceWorker' in navigator) { navigator.serviceWorker.register('<?= $SW ?>').catch(function () {}); }
</script>
</body>
</html>
