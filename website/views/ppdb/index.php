<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman PPDB - SD Negeri Kedungsari 1
 * SATU FILE. Letakkan di: website/views/ppdb/index.php
 * Dibuka lewat '?page=ppdb'
 * Formulir pendaftaran = Google Form dari pengaturan_sistem.ppdb_form_url (bagian #daftar di halaman ini)
 */

// Alamat lama '?page=ppdb&step=formulir' dialihkan ke bagian formulir pada halaman ini.
if (($_GET['step'] ?? '') === 'formulir') {
    header('Location: ' . page_url('ppdb') . '#daftar', true, 301);
    exit;
}

require_once __DIR__ . '/../../lib/api.php';

$current = 'ppdb';

// Formulir PPDB = Google Form milik sekolah. Alamatnya dibaca dari database (pengaturan_sistem.ppdb_form_url)
// lewat GET /api/public/pengaturan, diubah Admin dari PWA Admin. SI-KESA tidak menerima/menyimpan jawaban pendaftar.
$ppdb        = sikesa_ppdb();                 // status: ada | kosong | gagal
$formulirUrl = '#daftar';

// ===== DATA (ubah di sini) =====

// Kartu "Informasi" (ikon: kalender, papan, dokumen, centang) - warna selang-seling
$informasi = [
    ['ikon' => 'kalender', 'warna' => 'green', 'judul' => 'Jadwal',         'isi' => 'Informasi jadwal pembukaan dan penutupan PPDB'],
    ['ikon' => 'papan',    'warna' => 'cream', 'judul' => 'Persyaratan',    'isi' => 'Dokumen dan data yang perlu disiapkan sebelum mendaftar'],
    ['ikon' => 'dokumen',  'warna' => 'cream', 'judul' => 'Formulir Online','isi' => 'Isi formulir pendaftaran secara online dengan mudah'],
    ['ikon' => 'centang',  'warna' => 'green', 'judul' => 'Proses Mudah',   'isi' => 'Ikuti langkah-langkah pendaftaran yang sederhana'],
];

// Persyaratan (ganti dengan persyaratan resmi sekolah)
$persyaratan = [
    '[Persyaratan 1 — sesuai ketentuan sekolah]',
    '[Persyaratan 2 — sesuai ketentuan sekolah]',
    '[Persyaratan 3 — sesuai ketentuan sekolah]',
    '[Persyaratan 4 — sesuai ketentuan sekolah]',
    '[Persyaratan 5 — sesuai ketentuan sekolah]',
];
$catatanPersyaratan = 'Pastikan data yang dimasukkan sesuai dengan dokumen resmi. Hubungi sekolah jika ada pertanyaan.';

// Alur pendaftaran (5 langkah, urutan tampilan diatur oleh CSS)
$alur = [
    ['judul' => 'Persiapkan Data',      'isi' => 'Siapkan semua dokumen dan data yang diperlukan sesuai persyaratan.'],
    ['judul' => 'Isi Formulir',         'isi' => 'Lengkapi formulir pendaftaran online dengan data yang benar.'],
    ['judul' => 'Periksa Data',         'isi' => 'Pastikan seluruh data sudah benar sebelum mengirimkan formulir.'],
    ['judul' => 'Pendaftaran Berhasil', 'isi' => 'Sistem akan memberikan konfirmasi setelah pendaftaran berhasil.'],
    ['judul' => 'Download Formulir',    'isi' => 'Unduh formulir daftar ulang untuk proses selanjutnya.'],
];

// Pertanyaan umum (ganti jawaban sesuai kebijakan sekolah)
$faq = [
    ['q' => 'Bagaimana cara mendaftar PPDB',
     'a' => 'Klik tombol Daftar PPDB, isi formulir online dengan data yang benar, periksa kembali, lalu kirimkan. Langkah lengkapnya ada pada bagian Alur Pendaftaran.'],
    ['q' => 'Data apa saja yang perlu disiapkan?',
     'a' => 'Siapkan data dan dokumen sesuai bagian Persyaratan Pendaftaran. Daftar lengkapnya mengikuti ketentuan resmi sekolah.'],
    ['q' => 'bagaimana jika NIK tidak sesuai?',
     'a' => 'NIK harus berupa 16 digit angka. Periksa kembali dengan dokumen resmi. Jika masih tidak sesuai, hubungi pihak sekolah.'],
    ['q' => 'bagaimana mengetahui pendaftaran berhasil',
     'a' => 'Setelah formulir terkirim, sistem akan memberikan konfirmasi bahwa pendaftaran berhasil.'],
    ['q' => 'Bagaimana cara mendapatkan formulir daftar ulang?',
     'a' => 'Setelah pendaftaran berhasil, formulir daftar ulang dapat diunduh untuk proses selanjutnya.'],
];

// Ikon (SVG) untuk kartu Informasi
function ikon_info(string $nama): string {
    $icons = [
        'kalender' => '<rect x="4" y="5" width="16" height="15" rx="2.5"/><path d="M4 10h16M9 3v4M15 3v4"/>',
        'papan'    => '<rect x="5" y="4" width="14" height="17" rx="2.5"/><path d="M9 4V3h6v1M9 11h6M9 15h6"/>',
        'dokumen'  => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
        'centang'  => '<circle cx="12" cy="12" r="8.5"/><path d="m8.5 12.3 2.5 2.5 4.5-5"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . ($icons[$nama] ?? '') . '</svg>';
}

$title = 'PPDB - SD Negeri Kedungsari 1';

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'ppdb',
    'description' => 'Pendaftaran Peserta Didik Baru (PPDB) SD Negeri Kedungsari 1 - mudah, cepat, dan sepenuhnya online',
    'css'         => 'ppdb',
    'js'          => null,
]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<meta name="description" content="Pendaftaran Peserta Didik Baru (PPDB) SD Negeri Kedungsari 1 - mudah, cepat, dan sepenuhnya online">

<link rel="icon" href="<?= base_url('assets/gambar/logo.png') ?>">
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
    --head-blue:#c9d3ee;
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

/* ===== Hero ===== */
.hero{position:relative;min-height:calc(100vh - 64px);min-height:calc(100svh - 64px);display:flex;align-items:flex-end;
    background:linear-gradient(180deg,rgba(60,90,55,.55),rgba(60,90,55,.82)),var(--bg) center/cover}
.page-hero{align-items:center;justify-content:center;text-align:center}
.page-hero__content{position:relative;color:#fff;padding:0 6vw}
.page-hero h1{font-size:clamp(2.6rem,7vw,4.4rem);font-weight:700;letter-spacing:1px;line-height:1.1}
.page-hero p{font-size:clamp(1rem,2.2vw,1.35rem);font-weight:500;margin-top:6px}
.hero__actions{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-top:22px}
.btn-hero{display:inline-block;padding:8px 22px;border-radius:999px;font-size:.9rem;font-weight:600;color:#fff;
    background:var(--orange);transition:background .2s}
.btn-hero:hover{background:#d9780f}
.btn-hero--soft{background:#f6b042}
.btn-hero--soft:hover{background:#e39d2c}

/* ===== Section umum ===== */
.section{position:relative;padding:72px 6vw;text-align:center;overflow:hidden}
.section--tint{background:var(--sage)}
.section--leaf::before,.section--leaf::after{content:"";position:absolute;width:190px;height:260px;opacity:.28;pointer-events:none;
    background:no-repeat center/contain url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 140'><path d='M50 4C95 40 98 100 50 136 2 100 5 40 50 4Z' fill='%236f8a62'/><path d='M50 14V132' stroke='%23fbfaf4' stroke-width='2'/></svg>")}
.section--leaf::before{left:-60px;top:40px;transform:rotate(-25deg)}
.section--leaf::after{right:-60px;top:20px;transform:rotate(30deg) scaleX(-1)}
.title{position:relative;display:inline-block;font-size:clamp(1.7rem,3.5vw,2.4rem);font-weight:700;color:var(--green);margin-bottom:34px;padding-bottom:12px}
.title::after{content:"";position:absolute;left:50%;bottom:0;width:60%;height:3px;background:var(--green);transform:translateX(-50%);border-radius:3px}
.title--dark{color:var(--ink);text-transform:uppercase}
.title--dark::after{background:var(--ink)}

/* ===== Informasi ===== */
.info-grid{position:relative;z-index:1;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:40px 48px;
    max-width:900px;margin:0 auto;text-align:left}
.info-card{position:relative;padding:20px 22px 24px;border:2px solid #4b5a4a;border-radius:16px;
    box-shadow:0 5px 0 rgba(75,90,74,.45);min-height:150px}
.info-card--green{background:var(--green-soft)}
.info-card--cream{background:var(--cream)}
.info-card__icon{width:36px;height:36px;border-radius:50%;background:#fff;border:2px solid var(--ink);
    display:grid;place-items:center;margin-bottom:6px}
.info-card__icon svg{width:20px;height:20px;color:var(--ink)}
.info-card h3{font-size:1.15rem;font-weight:700;text-transform:uppercase;text-decoration:underline;
    text-underline-offset:4px;color:#101a3a;margin-bottom:6px}
.info-card p{font-size:.88rem;color:#1e2a4a;line-height:1.5}

/* ===== Persyaratan ===== */
.syarat{position:relative;z-index:1;max-width:760px;margin:0 auto;background:var(--card-blue);
    border:2.5px solid var(--green);border-radius:18px;padding:30px clamp(16px,5vw,50px) 22px;text-align:left;
    box-shadow:0 7px 0 rgba(91,117,83,.35)}
.syarat__list{display:grid;gap:0}
.syarat__item{position:relative;display:flex;align-items:center;gap:16px;padding-bottom:24px;
    font-size:.95rem;font-weight:500;color:#101a3a}
.syarat__item:not(:last-child)::before{content:"";position:absolute;left:19px;top:42px;bottom:0;width:2px;background:var(--ink)}
.syarat__check{flex:none;width:40px;height:40px;border-radius:50%;background:#fff;border:2px solid var(--ink);
    display:grid;place-items:center;position:relative;z-index:1}
.syarat__check svg{width:22px;height:22px;color:var(--ink)}
.catatan{display:flex;align-items:center;gap:14px;font-size:.88rem;color:#1e2a4a;margin-top:6px}
.catatan svg{flex:none;width:30px;height:30px;color:var(--orange)}

/* ===== Alur ===== */
.alur{position:relative;z-index:1;display:grid;max-width:1100px;margin:0 auto;gap:26px 10px;text-align:left;
    grid-template-columns:minmax(0,1fr) 70px minmax(0,1fr) minmax(0,1fr);
    grid-template-areas:
        "s1 a1 s2 a2"
        ".  .  .  s3"
        "s5 a4 s4 a3"}
.step{align-self:stretch;padding:14px 16px 18px;border:2px solid #4b5a4a;border-radius:14px;
    box-shadow:0 4px 0 rgba(75,90,74,.45)}
.step--green{background:var(--green-soft)}
.step--cream{background:var(--cream)}
.step__head{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.step__num{flex:none;width:30px;height:30px;border-radius:50%;background:#fff;border:2px solid var(--ink);
    display:grid;place-items:center;font-weight:700;font-size:.9rem;color:var(--ink)}
.step h3{font-size:.95rem;font-weight:700;color:#101a3a;line-height:1.25}
.step p{font-size:.85rem;color:#1e2a4a;line-height:1.5}
.step--1{grid-area:s1}.step--2{grid-area:s2}.step--3{grid-area:s3}.step--4{grid-area:s4}.step--5{grid-area:s5}
.arrow{align-self:center;justify-self:center;width:100%;max-width:70px;color:var(--ink)}
.arrow--1{grid-area:a1}
.arrow--2{grid-area:a2;align-self:end;max-width:90px}
.arrow--3{grid-area:a3;align-self:start;max-width:90px}
.arrow--4{grid-area:a4}

/* ===== Perhatikan NIK ===== */
.nik{position:relative;z-index:1;max-width:760px;margin:0 auto;background:var(--card-blue);
    border:2.5px solid #4b5a8a;border-radius:18px;overflow:hidden;text-align:left;box-shadow:0 7px 0 rgba(75,90,138,.3)}
.nik__head{display:flex;align-items:center;gap:12px;padding:12px 24px;background:var(--head-blue);border-bottom:2.5px solid #4b5a8a}
.nik__head svg{width:30px;height:30px;color:var(--orange);flex:none}
.nik__head h2{font-size:clamp(1.1rem,2.6vw,1.45rem);font-weight:700;color:#101a3a}
.nik__body{padding:22px clamp(16px,4vw,34px) 22px}
.nik__body > p{font-size:.92rem;color:#1e2a4a}
.nik__cmp{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin:18px 0 16px}
.nik__box{padding:12px 16px;border-radius:12px;font-size:.88rem;line-height:1.7}
.nik__box b{display:block;font-size:.8rem;letter-spacing:.3px}
.nik__box strong{display:block;font-weight:700}
.nik__box--ok{background:#c2f0c2;border:3px solid #3aa13a;color:#12381a}
.nik__box--bad{background:#e9b3b3;border:3px solid #c43b3b;color:#4a1414}
.nik__note{font-size:.8rem;color:#1e2a4a}

/* ===== CTA ===== */
.cta{position:relative;padding:76px 6vw;text-align:center;color:#fff;
    background:linear-gradient(180deg,#5f7d57,#587650)}
.cta h2{font-size:clamp(2.2rem,6vw,3.4rem);font-weight:700;line-height:1.15;margin-bottom:10px}
.cta p{font-size:clamp(.95rem,2vw,1.15rem);font-weight:500;line-height:1.5}
.btn-cta{display:inline-flex;align-items:center;gap:12px;margin-top:26px;padding:10px 34px;border-radius:999px;
    background:var(--orange);color:#fff;font-weight:600;font-size:1.05rem;transition:background .2s}
.btn-cta:hover{background:#d9780f}

/* ===== FAQ ===== */
.faq{position:relative;z-index:1;max-width:620px;margin:0 auto;display:grid;gap:14px;text-align:left}
.faq details{background:#e2e2e2;border-radius:12px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,.12)}
.faq summary{display:flex;align-items:center;justify-content:space-between;gap:14px;cursor:pointer;
    padding:11px 20px;font-size:.85rem;font-weight:500;color:#101a3a;list-style:none}
.faq summary::-webkit-details-marker{display:none}
.faq summary svg{flex:none;width:20px;height:20px;transition:transform .2s}
.faq details[open] summary svg{transform:rotate(180deg)}
.faq details p{padding:0 20px 16px;font-size:.85rem;color:#1e2a4a;line-height:1.6}

/* ===== Bantuan ===== */
.bantuan{position:relative;z-index:1}
.bantuan h3{font-size:clamp(1.1rem,2.4vw,1.35rem);font-weight:600;color:var(--ink)}
.bantuan p{font-size:.88rem;margin:10px 0 22px}
.btn-hubungi{display:inline-flex;align-items:center;gap:12px;padding:10px 46px;border-radius:999px;background:#fff;
    border:2.5px solid var(--ink);box-shadow:0 4px 0 rgba(0,0,0,.2);font-size:1.15rem;font-weight:500;color:var(--ink);
    transition:background .2s}
.btn-hubungi:hover{background:var(--sage)}
.btn-hubungi svg{width:30px;height:30px}

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
    .footer__inner{grid-template-columns:1fr 1fr}

    /* Alur: tumpuk ke bawah, panah disembunyikan */
    .alur{grid-template-columns:1fr;grid-template-areas:none;max-width:460px}
    .alur .step{grid-area:auto}
    .arrow{display:none}
    .step--1{order:1}.step--2{order:2}.step--3{order:3}.step--4{order:4}.step--5{order:5}
}
@media (max-width:620px){
    .info-grid{grid-template-columns:1fr;gap:28px}
    .nik__cmp{grid-template-columns:1fr;gap:14px}
}
@media (max-width:520px){
    .section{padding:56px 5vw}
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

<main>

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= asset('gambar/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>PPDB</h1>
            <p>Mulai langkah pendidikan putra-putri Anda bersama<br>SD Negeri Kedungsari 1.<br>Proses pendaftaran mudah, cepat, dan sepenuhnya online.</p>
            <div class="hero__actions">
                <a class="btn-hero" href="<?= esc($formulirUrl) ?>">Daftar Sekarang</a>
                <a class="btn-hero btn-hero--soft" href="#alur">Pelajari Alur</a>
            </div>
        </div>
    </section>

    <!-- ================= INFORMASI ================= -->
    <section class="section section--leaf" id="informasi">
        <h2 class="title title--dark">Informasi</h2>

        <div class="info-grid">
            <?php foreach ($informasi as $i): ?>
                <article class="info-card info-card--<?= esc($i['warna']) ?>">
                    <span class="info-card__icon"><?= ikon_info($i['ikon']) ?></span>
                    <h3><?= esc($i['judul']) ?></h3>
                    <p><?= esc($i['isi']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================= PERSYARATAN ================= -->
    <section class="section section--leaf" id="persyaratan">
        <h2 class="title">Persyaratan Pendaftaran</h2>

        <div class="syarat">
            <ul class="syarat__list">
                <?php foreach ($persyaratan as $p): ?>
                    <li class="syarat__item">
                        <span class="syarat__check" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 12.5 4 4 8-9"/></svg>
                        </span>
                        <span><?= esc($p) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="catatan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3.5 2.5 20h19z"/><path d="M12 10v4.5M12 17.3v.2"/>
                </svg>
                <span><?= esc($catatanPersyaratan) ?></span>
            </p>
        </div>
    </section>

    <!-- ================= ALUR ================= -->
    <section class="section section--leaf" id="alur">
        <h2 class="title">Alur Pendaftaran</h2>

        <div class="alur">
            <?php foreach ($alur as $n => $a): $no = $n + 1; ?>
                <article class="step step--<?= $no ?> <?= $no % 2 ? 'step--green' : 'step--cream' ?>">
                    <div class="step__head">
                        <span class="step__num"><?= $no ?></span>
                        <h3><?= esc($a['judul']) ?></h3>
                    </div>
                    <p><?= esc($a['isi']) ?></p>
                </article>
            <?php endforeach; ?>

            <!-- Panah penunjuk arah (hanya dekorasi) -->
            <svg class="arrow arrow--1" viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h41M35 4l9 8-9 8"/></svg>
            <svg class="arrow arrow--2" viewBox="0 0 90 50" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 10h25q14 0 14 14v20M35 34l10 10 10-10"/></svg>
            <svg class="arrow arrow--3" viewBox="0 0 90 50" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M45 2v22q0 12-14 12H6M16 26 6 36l10 10"/></svg>
            <svg class="arrow arrow--4" viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M45 12H4M13 4 4 12l9 8"/></svg>
        </div>
    </section>

    <!-- ================= PERHATIKAN NIK ================= -->
    <section class="section section--tint" id="nik">
        <div class="nik">
            <div class="nik__head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3.5 2.5 20h19z"/><path d="M12 10v4.5M12 17.3v.2"/>
                </svg>
                <h2>Perhatikan Data NIK</h2>
            </div>

            <div class="nik__body">
                <p>NIK wajib diisi dengan angka dan harus terdiri dari tepat <strong>16 karakter</strong>.</p>

                <div class="nik__cmp">
                    <div class="nik__box nik__box--ok">
                        <b>BENAR</b>
                        <strong>1234567890123456</strong>
                        16 digit.
                    </div>
                    <div class="nik__box nik__box--bad">
                        <b>SALAH</b>
                        <strong>123456789012345</strong>
                        Hanya 15 digit.
                    </div>
                </div>

                <p class="nik__note">* Contoh NIK di atas bukan NIK nyata. Gunakan NIK sesuai dokumen resmi.</p>
            </div>
        </div>
    </section>

    <!-- ================= SIAP MENDAFTAR ================= -->
    <section class="cta" id="daftar">
        <h2>Siap Mendaftar?</h2>
        <?php if ($ppdb['status'] === 'ada'): ?>
            <p>Pastikan data sudah disiapkan sebelum mengisi formulir.<br>Pendaftaran dilakukan melalui formulir online sekolah.</p>
            <a class="btn-cta" href="<?= esc($ppdb['url']) ?>" target="_blank" rel="noopener">
                Buka Formulir
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
            </a>
            <?php if ($ppdb['embed']): ?>
                <div class="form-embed">
                    <iframe src="<?= esc($ppdb['embed']) ?>" title="Formulir PPDB SD Negeri Kedungsari 1" loading="lazy">Memuat formulir&hellip;</iframe>
                </div>
            <?php endif; ?>
        <?php elseif ($ppdb['status'] === 'kosong'): ?>
            <p>Formulir PPDB belum tersedia. Silakan hubungi sekolah.</p>
            <a class="btn-cta" href="<?= page_url('kontak') ?>">Hubungi Kami</a>
        <?php else: ?>
            <p>Formulir gagal dimuat. Periksa koneksi internet Anda lalu coba lagi.</p>
            <a class="btn-cta" href="<?= page_url('ppdb') ?>#daftar">Coba Lagi</a>
        <?php endif; ?>
    </section>

    <!-- ================= PERTANYAAN UMUM ================= -->
    <section class="section section--leaf" id="faq">
        <h2 class="title">Pertanyaan Umum</h2>

        <div class="faq">
            <?php foreach ($faq as $f): ?>
                <details>
                    <summary>
                        <?= esc($f['q']) ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <p><?= esc($f['a']) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================= BANTUAN ================= -->
    <section class="section section--tint" id="bantuan">
        <div class="bantuan">
            <h3>Kesulitan Mengisi Formulir ?</h3>
            <p>Hubungi pihak sekolah untuk mendapatkan bantuan pengisian formulir.</p>
            <a class="btn-hubungi" href="<?= page_url('kontak') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 3.5h3.2l1.6 4.2-2 1.3a11 11 0 0 0 6.2 6.2l1.3-2 4.2 1.6V18a2.5 2.5 0 0 1-2.7 2.5C9.9 20 4 14.1 3.5 6.2A2.5 2.5 0 0 1 5 3.5z"/>
                </svg>
                Hubungi Kami
            </a>
        </div>
    </section>

</main>

<footer class="footer">
    <div class="footer__inner">
        <div class="footer__brand">
            <div class="footer__logo">
                <img src="<?= base_url('assets/gambar/logo.png') ?>" alt="Logo">
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
                <li><a href="#" target="_blank" rel="noopener">Instagram</a></li>
                <li><a href="#" target="_blank" rel="noopener">Facebook</a></li>
            </ul>
        </div>

        <div class="footer__col footer__contact">
            <h4>Kontak &amp; Lokasi</h4>
            <ul>
                <li>Jl. Raya Kolinggang, Kec. Tohasen, Kabupaten Kediri, Jawa Timur 64152</li>
                <li>0000-0000-0000</li>
                <li>sdnegerikedungsari1@gmail.com</li>
            </ul>
            <iframe class="footer__map" loading="lazy" title="Lokasi sekolah"
                    src="https://www.google.com/maps?q=Kediri&output=embed"></iframe>
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
