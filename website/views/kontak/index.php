<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Hubungi Kami - dibuka lewat '?page=kontak'
 * Kontak ditulis tetap di views/partials/site-info.php (BUKAN dari database/API) dan tidak ada formulir kirim pesan.
 * Baris yang nilainya kosong disembunyikan, KECUALI media sosial: Instagram dan Facebook selalu tampil.
 */
$k = site_info();
$telHref = $k['telepon'] !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $k['telepon']) : '';
$wa = preg_replace('/\D/', '', $k['whatsapp']);
if ($wa !== '' && $wa[0] === '0') { $wa = '62' . substr($wa, 1); }
$waHref = $wa !== '' ? 'https://wa.me/' . $wa : '';
$sosmed = [
    ['Instagram', $k['instagram'] ?: '#', 'sosmed__ig', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.3" cy="6.7" r="1" fill="currentColor" stroke="none"/></svg>'],
    ['Facebook',  $k['facebook'] ?: '#',  'sosmed__fb', '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg>'],
];

layout_start([
    'title'       => 'Hubungi Kami - SD Negeri Kedungsari 1',
    'current'     => 'kontak',
    'description' => 'Hubungi SD Negeri Kedungsari 1 - alamat, telepon, email, media sosial, jam layanan, dan lokasi sekolah',
    'css'         => 'kontak',
]);
?>
<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> c0537a9e00559a0ef785f741664ec62ff3a44d17
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<meta name="description" content="Hubungi SD Negeri Kedungsari 1 - alamat, telepon, email, WhatsApp, dan lokasi sekolah">

<link rel="icon" href="<?= base_url('assets/bingkai.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
/* ===== Token ===== */
:root{
    --green:#5b7553;
    --green-dark:#46603f;
    --green-soft:#a9b79a;
    --sage:#eeecdc;
    --cream:#f6ecdc;
    --paper:#fbfaf4;
    --card-blue:#eef2f8;
    --icon-bg:#dbe4f5;
    --orange:#f08a1c;
    --ink:#2f3a2c;
    --muted:#6b7566;
    --radius:14px;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Poppins',sans-serif;color:var(--ink);background:var(--paper);line-height:1.6}
img{max-width:100%;display:block}
a{color:inherit;text-decoration:none}
ul{list-style:none}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important;scroll-behavior:auto!important}}
:focus-visible{outline:3px solid var(--orange);outline-offset:2px}

/* ===== Navbar ===== */
.navbar{position:sticky;top:0;z-index:50;display:flex;align-items:center;justify-content:space-between;
    padding:10px 6vw;background:linear-gradient(90deg,#4f6b49,#5f7d57);color:#fff}
.navbar__brand{display:flex;align-items:center;gap:10px;font-weight:600;font-size:.95rem}
.navbar__brand img{width:42px;height:42px;object-fit:contain}
.navbar__menu{display:flex;align-items:center;gap:26px;font-size:.9rem;font-weight:500}
.navbar__menu a{padding:6px 2px;border-bottom:2px solid transparent;transition:border-color .2s}
.navbar__menu a:hover,.navbar__menu a.is-active{border-color:#fff}
.btn-ppdb{display:inline-block;background:var(--orange);color:#fff!important;padding:7px 18px!important;
    border-radius:999px;border:0!important;font-weight:600;transition:background .2s}
.btn-ppdb:hover{background:#d9780f}
.navbar__toggle{display:none;background:none;border:0;cursor:pointer;flex-direction:column;gap:5px}
.navbar__toggle span{width:24px;height:2px;background:#fff;display:block}

/* ===== Section umum ===== */
.section{position:relative;padding:72px 6vw;text-align:center;overflow:hidden}
.section--leaf::before,.section--leaf::after{content:"";position:absolute;width:190px;height:260px;opacity:.28;pointer-events:none;
    background:no-repeat center/contain url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 140'><path d='M50 4C95 40 98 100 50 136 2 100 5 40 50 4Z' fill='%236f8a62'/><path d='M50 14V132' stroke='%23fbfaf4' stroke-width='2'/></svg>")}
.section--leaf::before{left:-60px;top:40px;transform:rotate(-25deg)}
.section--leaf::after{right:-60px;top:20px;transform:rotate(30deg) scaleX(-1)}
.title{position:relative;display:inline-block;font-size:clamp(1.7rem,3.5vw,2.4rem);font-weight:700;color:var(--green);margin-bottom:34px;padding-bottom:12px}
.title::after{content:"";position:absolute;left:50%;bottom:0;width:60%;height:3px;background:var(--green);transform:translateX(-50%);border-radius:3px}

/* ===== Halaman Kontak ===== */
.kontak-section{padding-top:clamp(70px,10vw,120px);padding-bottom:90px}

.panel{position:relative;z-index:1;background:var(--card-blue);border:3px solid var(--green);border-radius:20px;
    overflow:hidden;text-align:left;box-shadow:0 8px 0 rgba(91,117,83,.35)}
.panel__head{display:flex;align-items:center;gap:16px;padding:12px 26px;border-bottom:3px solid var(--green)}
.panel__head svg{width:34px;height:34px;flex:none;color:#101a3a}
.panel__head h2{font-size:clamp(1.3rem,2.8vw,2rem);font-weight:700;color:#101a3a;line-height:1.2}

.panel--info{max-width:1200px;margin:0 auto}
.info-list{padding:6px clamp(16px,3vw,34px) 14px}
.info-item{display:flex;align-items:center;gap:22px;padding:22px 0;border-bottom:1.5px solid #2f3a2c}
.info-item:last-child{border-bottom:0}
.info-item__icon{flex:none;width:58px;height:58px;border-radius:50%;background:var(--icon-bg);display:grid;place-items:center;color:#101a3a}
.info-item__icon svg{width:28px;height:28px}
.info-item__text{font-size:clamp(.95rem,1.8vw,1.1rem);font-weight:500;color:#101a3a;word-break:break-word}
a.info-item__text:hover{text-decoration:underline}

.kontak-row{position:relative;z-index:1;display:grid;grid-template-columns:1.9fr 1fr;gap:28px;
    max-width:1260px;margin:70px auto 0}
.panel--map{min-height:420px}
.panel--map iframe{display:block;width:100%;height:100%;min-height:420px;border:0}
.panel--sosmed{display:flex;flex-direction:column}
.sosmed-list{padding:8px clamp(14px,2vw,26px) 20px}
.sosmed-item{display:flex;align-items:center;gap:18px;padding:12px 0;border-bottom:1.5px solid #2f3a2c;
    font-size:1rem;font-weight:500;color:#101a3a}
.sosmed-item svg{width:34px;height:34px;flex:none}
.sosmed-item:hover span{text-decoration:underline}

/* ===== Footer ===== */
.footer{background:linear-gradient(90deg,#4f6b49,#5f7d57);color:#fff;font-size:.8rem}
.footer__inner{display:grid;grid-template-columns:1.3fr 1fr .8fr 1.4fr;gap:30px;padding:40px 6vw 28px}
.footer__logo{display:flex;align-items:center;gap:10px;font-size:1rem;margin-bottom:8px}
.footer__logo img{width:46px;height:46px;object-fit:contain}
.footer h4{font-size:.9rem;margin-bottom:10px}
.footer li{margin-bottom:6px;opacity:.9}
.footer a:hover{text-decoration:underline}
.footer__map{width:100%;height:90px;border:0;border-radius:8px;margin-top:10px}
.footer__copy{text-align:center;padding:12px;border-top:1px solid rgba(255,255,255,.25);font-size:.72rem}

/* ===== Responsif ===== */
@media (max-width:860px){
    .navbar__toggle{display:flex}
    .navbar__menu{position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:0;
        background:#4f6b49;padding:10px 6vw 18px;display:none}
    .navbar__menu.is-open{display:flex}
    .navbar__menu li{width:100%;padding:8px 0}
    .kontak-row{grid-template-columns:1fr;margin-top:50px}
    .panel--map,.panel--map iframe{min-height:320px}
    .footer__inner{grid-template-columns:1fr 1fr}
}
@media (max-width:520px){
    .section{padding:56px 5vw}
    .kontak-section{padding-top:56px}
    .info-item{gap:14px;padding:16px 0}
    .info-item__icon{width:48px;height:48px}
    .info-item__icon svg{width:24px;height:24px}
    .footer__inner{grid-template-columns:1fr}
}
    </style>
</head>
<body>
<?php
$menuLain = [
    'berita' => 'Berita',
    'ekstra' => 'Ekstra',
    'galeri' => 'Galeri',
    'kontak' => 'Hubungi kami',
];
?>
    <nav class="navbar" id="navbar">
        <a href="<?= page_url('home') ?>" class="navbar__brand">
            <img src="<?= base_url('assets/bingkai.png') ?>" alt="Logo SD Negeri Kedungsari 1">
            <span>SD NEGERI KEDUNGSARI 1</span>
        </a>

        <button class="navbar__toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <ul class="navbar__menu" id="navMenu">
            <li>
                <a href="<?= page_url('home') ?>" class="<?= $current === 'home' ? 'is-active' : '' ?>">Home</a>
            </li>

            <li>
                <a href="<?= page_url('profil') ?>" class="<?= $current === 'profil' ? 'is-active' : '' ?>">Profil</a>
            </li>

            <?php foreach ($menuLain as $key => $label): ?>
                <li>
                    <a href="<?= page_url($key) ?>" class="<?= $current === $key ? 'is-active' : '' ?>"><?= $label ?></a>
                </li>
            <?php endforeach; ?>

            <li><a href="<?= page_url('ppdb') ?>" class="btn-ppdb">Daftar PPDB</a></li>
        </ul>
    </nav>

<<<<<<< HEAD
>>>>>>> 8d7d43c3183adc7a19a337326a880273da51868a
=======
=======
>>>>>>> 82200f0e38e90c8bbf80824aab51d892a727d18d
>>>>>>> c0537a9e00559a0ef785f741664ec62ff3a44d17
<main>
    <?php ui_hero('Hubungi Kami', 'Kami siap membantu informasi sekolah dan pendaftaran'); ?>
    <section class="section section--leaf" id="kontak">
        <h2 class="title">Informasi Kontak</h2>
        <div class="kontak">
            <ul class="info-list">
                <?php if ($k['alamat'] !== ''): ?>
                <li class="info-item"><span class="info-item__label">Alamat</span><span><?= esc($k['alamat']) ?></span></li>
                <?php endif; if ($k['telepon'] !== ''): ?>
                <li class="info-item"><span class="info-item__label">Telepon</span><a href="<?= esc($telHref) ?>"><?= esc($k['telepon']) ?></a></li>
                <?php endif; if ($k['whatsapp'] !== ''): ?>
                <li class="info-item"><span class="info-item__label">WhatsApp</span><a href="<?= esc($waHref) ?>" target="_blank" rel="noopener noreferrer"><?= esc($k['whatsapp']) ?></a></li>
                <?php endif; if ($k['email'] !== ''): ?>
                <li class="info-item"><span class="info-item__label">Email</span><a href="mailto:<?= esc($k['email']) ?>"><?= esc($k['email']) ?></a></li>
                <?php endif; if (!empty($k['jam'])): ?>
                <li class="info-item"><span class="info-item__label">Jam Layanan</span><span><?= esc($k['jam']) ?></span></li>
                <?php endif; ?>
                <li class="info-item">
                    <span class="info-item__label">Media Sosial</span>
                    <span class="sosmed">
                        <?php foreach ($sosmed as [$nama, $url, $kelas, $ikon]): ?>
                            <a class="<?= $kelas ?>" href="<?= esc($url) ?>" target="_blank" rel="noopener noreferrer"><?= $ikon ?><span><?= esc($nama) ?></span></a>
                        <?php endforeach; ?>
                    </span>
                </li>
            </ul>

            <div class="aksi">
                <a class="pill" href="<?= esc($k['peta_link']) ?>" target="_blank" rel="noopener noreferrer">Buka di Google Maps</a>
            </div>

            <div class="peta">
                <iframe loading="lazy" title="Lokasi SD Negeri Kedungsari 1" src="<?= esc($k['peta']) ?>" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>
</main>
<<<<<<< HEAD
<<<<<<< HEAD
<?php layout_end(); ?>
=======
=======
>>>>>>> c0537a9e00559a0ef785f741664ec62ff3a44d17

<footer class="footer">
    <div class="footer__inner">
        <div class="footer__brand">
            <div class="footer__logo">
                <img src="<?= base_url('assets/bingkai.png') ?>" alt="Logo">
                <strong>SD NEGERI KEDUNGSARI 1</strong>
            </div>
            <p>Situs resmi SD Negeri Kedungsari 1 untuk informasi akademik dan kegiatan sekolah.</p>
        </div>

        <div class="footer__col">
            <h4>Informasi Sekolah</h4>
            <ul>
                <li><a href="<?= page_url('profil') ?>#visi-misi">Visi &amp; Misi</a></li>
                <li><a href="<?= page_url('ppdb') ?>">Pendaftaran Siswa Baru</a></li>
                <li><a href="<?= page_url('berita') ?>">Artikel &amp; Berita Terkini</a></li>
                <li><a href="<?= page_url('ekstra') ?>">Ekstrakurikuler</a></li>
                <li><a href="<?= page_url('profil') ?>#guru">Guru &amp; Karyawan</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h4>Sosial media</h4>
            <ul>
                <li><a href="<?= esc($kontak['instagram']) ?>" target="_blank" rel="noopener">Instagram</a></li>
                <li><a href="<?= esc($kontak['facebook']) ?>" target="_blank" rel="noopener">Facebook</a></li>
            </ul>
        </div>

        <div class="footer__col footer__contact">
            <h4>Kontak &amp; Lokasi</h4>
            <ul>
                <li>Jl. Raya Kolinggang, Kec. Tohasen, Kabupaten Kediri, Jawa Timur 64152</li>
                <li>0000-0000-0000</li>
                <li>sdnegerikedungsari1@gmail.com</li>
            </ul>
            <iframe class="footer__map" loading="lazy" title="Lokasi sekolah" src="<?= esc($petaUrl) ?>"></iframe>
        </div>
    </div>
    <div class="footer__copy">&copy; <?= date('Y') ?> SD Negeri Kedungsari 1. Semua hak dilindungi.</div>
</footer>

<script>
/* ===== Menu mobile ===== */
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('navToggle');
    const menu   = document.getElementById('navMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open);
    });
});
</script>
</body>
</html>
<<<<<<< HEAD
>>>>>>> 8d7d43c3183adc7a19a337326a880273da51868a
=======
=======
<?php layout_end(); ?>
>>>>>>> 82200f0e38e90c8bbf80824aab51d892a727d18d
>>>>>>> c0537a9e00559a0ef785f741664ec62ff3a44d17
