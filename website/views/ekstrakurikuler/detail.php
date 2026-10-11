<?php
/**
 * Halaman Detail Ekstrakurikuler - SD Negeri Kedungsari 1
 * Letakkan di: website/views/ekstrakurikuler/detail.php
 * Dibuka lewat: ?page=ekstrakurikuler&slug=<slug>  (diteruskan oleh index.php)
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===== Fungsi bantu (dilewati jika sudah ada) =====
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        static $base = null;
        if ($base === null) {
            $base = '';
            $root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ''), '/');
            foreach ([__DIR__ . '/../../public', __DIR__ . '/public', __DIR__, __DIR__ . '/..'] as $dir) {
                $assets = realpath($dir . '/assets');
                if ($assets) {
                    $pub = str_replace('\\', '/', dirname($assets));
                    if ($root !== '' && stripos($pub, $root) === 0) {
                        $base = substr($pub, strlen($root)) . '/';
                        break;
                    }
                }
            }
            if ($base === '') {
                $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
            }
        }
        return $base . ltrim($path, '/');
    }
}
if (!function_exists('esc')) {
    function esc($text): string {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('page_url')) {
    function page_url(string $key): string {
        static $map = [
            'home'    => '',
            'profil'  => '?page=guru',
            'berita'  => '?page=kegiatan',
            'ekstra'  => '?page=ekstrakurikuler',
            'galeri'  => '?page=galeri',
            'kontak'  => '?page=kontak',
            'ppdb'    => '?page=ppdb',
        ];
        return base_url($map[$key] ?? $key);
    }
}

$current = 'ekstra';

// ===== Cari ekstrakurikuler berdasarkan slug =====
$ekstraList = require __DIR__ . '/data.php';
$slug  = (string) ($_GET['slug'] ?? '');
$ekstra = null;
foreach ($ekstraList as $item) {
    if ($item['slug'] === $slug) { $ekstra = $item; break; }
}
if (!$ekstra) {
    http_response_code(404);
}

$nama  = $ekstra['nama'] ?? '';
$title = ($ekstra ? 'Ekstrakurikuler ' . $nama : 'Ekstrakurikuler tidak ditemukan') . ' - SD Negeri Kedungsari 1';
$heroFoto = $ekstra ? $ekstra['foto'] : 'assets/home.jpg';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<meta name="description" content="<?= esc($ekstra ? mb_strimwidth($ekstra['tentang'], 0, 150, '…', 'UTF-8') : 'Ekstrakurikuler SD Negeri Kedungsari 1') ?>">

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
.page-hero h1{font-size:clamp(2.6rem,7vw,4.4rem);font-weight:700;letter-spacing:1px;line-height:1.1;text-transform:uppercase}
.page-hero p{font-size:clamp(1rem,2.2vw,1.35rem);font-weight:500;margin-top:6px}

/* ===== Section umum ===== */
.section{position:relative;padding:72px 6vw;text-align:center;overflow:hidden}
.section--leaf::before,.section--leaf::after{content:"";position:absolute;width:190px;height:260px;opacity:.28;pointer-events:none;
    background:no-repeat center/contain url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 140'><path d='M50 4C95 40 98 100 50 136 2 100 5 40 50 4Z' fill='%236f8a62'/><path d='M50 14V132' stroke='%23fbfaf4' stroke-width='2'/></svg>")}
.section--leaf::before{left:-60px;top:40px;transform:rotate(-25deg)}
.section--leaf::after{right:-60px;top:20px;transform:rotate(30deg) scaleX(-1)}
.title{position:relative;display:inline-block;font-size:clamp(1.7rem,3.5vw,2.4rem);font-weight:700;color:var(--green);margin-bottom:34px;padding-bottom:12px}
.title::after{content:"";position:absolute;left:50%;bottom:0;width:60%;height:3px;background:var(--green);transform:translateX(-50%);border-radius:3px}

/* ===== Tombol ===== */
.pill{display:inline-block;font:inherit;font-size:.85rem;font-weight:600;padding:6px 20px;border-radius:999px;cursor:pointer;
    background:#fff;color:var(--green-dark);border:1.5px solid var(--green);transition:background .2s,color .2s}
.pill--solid{background:var(--green);color:#fff}
.pill:hover{background:var(--green-dark);color:#fff}

/* ===== Kartu detail ===== */
.detail-card{position:relative;z-index:1;max-width:1000px;margin:0 auto;background:var(--card-blue);
    border:3px solid var(--green);border-radius:22px;padding:40px clamp(20px,5vw,50px) 44px;text-align:left;
    box-shadow:0 8px 0 rgba(91,117,83,.35)}
.detail-card h2{font-size:clamp(1.3rem,2.6vw,1.8rem);font-weight:700;color:var(--green-dark);margin:30px 0 12px}
.detail-card h2:first-child{margin-top:0}
.detail-card p{font-size:.95rem;color:#1e2a4a;line-height:1.6}
.detail-card ul{list-style:disc;padding-left:28px;font-size:.95rem;color:#1e2a4a;display:grid;gap:2px}
.detail-card .materi + .materi{margin-top:14px}
.detail-card .materi strong{display:block;font-size:.95rem;color:#101a3a}
.detail__actions{margin-top:34px}

/* ===== Tidak ditemukan ===== */
.kosong{position:relative;z-index:1;max-width:560px;margin:0 auto;padding:40px 20px;background:var(--card-blue);
    border:3px solid var(--green);border-radius:22px;box-shadow:0 8px 0 rgba(91,117,83,.35)}
.kosong p{color:var(--muted);margin-bottom:20px}

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

<?php if (!$ekstra): ?>

    <section class="section section--leaf">
        <h1 class="title">Ekstrakurikuler tidak ditemukan</h1>
        <div class="kosong">
            <p>Ekstrakurikuler yang Anda cari tidak tersedia atau sudah dipindahkan.</p>
            <a class="pill pill--solid" href="<?= page_url('ekstra') ?>">Kembali ke Ekstrakurikuler</a>
        </div>
    </section>

<?php else: ?>

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= base_url($heroFoto) ?>')">
        <div class="page-hero__content">
            <h1><?= esc($nama) ?></h1>
            <p>Mengembangkan minat, bakat, dan potensi siswa<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <!-- ================= DETAIL ================= -->
    <section class="section section--leaf" id="detail">
        <h2 class="title"><?= esc($nama) ?></h2>

        <article class="detail-card">
            <h2>Tentang Ekstrakurikuler <?= esc($nama) ?></h2>
            <p><?= esc($ekstra['tentang']) ?></p>

            <?php if (!empty($ekstra['kegiatan'])): ?>
                <h2>Kegiatan yang Dilakukan</h2>
                <ul>
                    <?php foreach ($ekstra['kegiatan'] as $k): ?>
                        <li><?= esc($k) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($ekstra['materi'])): ?>
                <h2><?= esc($ekstra['materi_judul'] ?? 'Materi yang Dipelajari') ?></h2>
                <?php foreach ($ekstra['materi'] as $m): ?>
                    <div class="materi">
                        <strong><?= esc($m['nama']) ?></strong>
                        <p><?= esc($m['deskripsi']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($ekstra['manfaat'])): ?>
                <h2>Manfaat Mengikuti <?= esc($nama) ?></h2>
                <ul>
                    <?php foreach ($ekstra['manfaat'] as $m): ?>
                        <li><?= esc($m) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="detail__actions">
                <a class="pill" href="<?= page_url('ekstra') ?>">Kembali ke Ekstrakurikuler</a>
            </div>
        </article>
    </section>

<?php endif; ?>

</main>

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