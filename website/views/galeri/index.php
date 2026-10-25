<?php
/**
 * Halaman Galeri - SD Negeri Kedungsari 1
 * Letakkan di: website/views/galeri/index.php
 * Dibuka lewat '?page=galeri'
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
if (!function_exists('tanggal_id')) {
    function tanggal_id(string $ymd): string {
        static $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                         'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $t = strtotime($ymd);
        return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
    }
}
if (!function_exists('galeri_url')) {
    function galeri_url(array $params = []): string {
        return page_url('galeri') . ($params ? '&' . http_build_query($params) : '');
    }
}

$current = 'galeri';

// ===== DATA (urutan mengikuti data.php) =====
$galeriList = require __DIR__ . '/data.php';

// ===== Paginasi =====
// ?hal=2 -> halaman ke-2 | ?hal=semua -> tampilkan semuanya
// Navigasi paginasi baru muncul jika jumlah foto lebih dari $perHalaman.
$perHalaman   = 4;
$total        = count($galeriList);
$totalHalaman = max(1, (int) ceil($total / $perHalaman));
$semua        = (($_GET['hal'] ?? '') === 'semua');

if ($semua) {
    $halaman = 1;
    $tampil  = $galeriList;
} else {
    $halaman = max(1, min($totalHalaman, (int) ($_GET['hal'] ?? 1)));
    $tampil  = array_slice($galeriList, ($halaman - 1) * $perHalaman, $perHalaman);
}

$title = 'Galeri - SD Negeri Kedungsari 1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<meta name="description" content="Galeri kegiatan SD Negeri Kedungsari 1 - merekam momen berharga dan dokumentasi kegiatan">

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
    --card-grey:#dcdcdc;
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

/* ===== Section umum ===== */
.section{position:relative;padding:72px 6vw;text-align:center;overflow:hidden}
.section--leaf::before,.section--leaf::after{content:"";position:absolute;width:190px;height:260px;opacity:.28;pointer-events:none;
    background:no-repeat center/contain url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 140'><path d='M50 4C95 40 98 100 50 136 2 100 5 40 50 4Z' fill='%236f8a62'/><path d='M50 14V132' stroke='%23fbfaf4' stroke-width='2'/></svg>")}
.section--leaf::before{left:-60px;top:40px;transform:rotate(-25deg)}
.section--leaf::after{right:-60px;top:20px;transform:rotate(30deg) scaleX(-1)}
.title{position:relative;display:inline-block;font-size:clamp(1.7rem,3.5vw,2.4rem);font-weight:700;color:var(--green);margin-bottom:34px;padding-bottom:12px}
.title::after{content:"";position:absolute;left:50%;bottom:0;width:60%;height:3px;background:var(--green);transform:translateX(-50%);border-radius:3px}

/* ===== Kartu galeri ===== */
.galeri-grid{position:relative;z-index:1;display:grid;grid-template-columns:repeat(2,minmax(0,430px));
    justify-content:center;gap:56px clamp(30px,8vw,110px);margin:0 auto}
.galeri-card{display:flex;flex-direction:column;background:var(--card-grey);border:3px solid var(--green);
    border-radius:18px;padding:10px 10px 12px;box-shadow:0 6px 0 rgba(91,117,83,.35);text-align:left}
.galeri-card__img{width:100%;aspect-ratio:9/10;object-fit:cover;background:#c9d1bf;
    border:3px solid var(--green);border-radius:16px;box-shadow:0 4px 10px rgba(0,0,0,.2)}
.galeri-card h3{margin:12px 6px 0;font-size:1.05rem;font-weight:600;line-height:1.25;color:#111a2e;text-align:center}
.galeri-card time{margin:auto 0 0 4px;padding-top:18px;font-size:.78rem;color:var(--muted)}
.galeri-kosong{position:relative;z-index:1;color:var(--muted)}

/* ===== Paginasi ===== */
.pager__dots{position:relative;z-index:1;display:flex;justify-content:center;gap:10px;margin:46px 0 18px}
.pager__dots a{width:11px;height:11px;border-radius:50%;background:#c8cabb;display:block}
.pager__dots a.is-on{background:var(--green);width:26px;border-radius:9px}
.pager__nav{position:relative;z-index:1;display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:46px}
.pager__dots + .pager__nav{margin-top:0}
.pill{display:inline-block;font:inherit;font-size:.85rem;font-weight:600;padding:6px 20px;border-radius:999px;cursor:pointer;
    background:#fff;color:var(--green-dark);border:1.5px solid var(--green);transition:background .2s,color .2s}
.pill--solid{background:var(--green);color:#fff}
.pill:hover{background:var(--green-dark);color:#fff}
.pill.is-disabled{opacity:.4;pointer-events:none}

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
    .galeri-grid{grid-template-columns:minmax(0,430px)}
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

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= base_url('assets/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>GALERI</h1>
            <p>Merekam momen berharga dan dokumentasi kegiatan<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <!-- ================= DAFTAR FOTO ================= -->
    <section class="section section--leaf" id="galeri">
        <h2 class="title">Galeri</h2>

        <?php if (!$tampil): ?>
            <p class="galeri-kosong">Belum ada foto yang dipublikasikan.</p>
        <?php else: ?>
            <div class="galeri-grid">
                <?php foreach ($tampil as $g): ?>
                    <figure class="galeri-card">
                        <img class="galeri-card__img" src="<?= base_url($g['foto']) ?>"
                             alt="<?= esc($g['judul']) ?>" loading="lazy">
                        <h3><?= esc($g['judul']) ?></h3>
                        <time datetime="<?= esc($g['tanggal']) ?>"><?= esc(tanggal_id($g['tanggal'])) ?></time>
                    </figure>
                <?php endforeach; ?>
            </div>

            <?php if ($totalHalaman > 1): ?>
                <!-- ===== Paginasi (muncul jika lebih dari satu halaman) ===== -->
                <nav class="pager" aria-label="Halaman galeri">
                    <?php if (!$semua): ?>
                        <div class="pager__dots">
                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                <a href="<?= esc(galeri_url(['hal' => $i])) ?>#galeri"
                                   class="<?= $i === $halaman ? 'is-on' : '' ?>"
                                   aria-label="Halaman <?= $i ?>"
                                   <?= $i === $halaman ? 'aria-current="page"' : '' ?>></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>

                    <div class="pager__nav">
                        <a class="pill <?= ($semua || $halaman <= 1) ? 'is-disabled' : '' ?>"
                           href="<?= esc(galeri_url(['hal' => max(1, $halaman - 1)])) ?>#galeri">Sebelumnya</a>

                        <?php if ($semua): ?>
                            <a class="pill pill--solid" href="<?= esc(galeri_url()) ?>#galeri">Tampilkan Per Halaman</a>
                        <?php else: ?>
                            <a class="pill pill--solid" href="<?= esc(galeri_url(['hal' => 'semua'])) ?>#galeri">Lihat Semua Foto</a>
                        <?php endif; ?>

                        <a class="pill <?= ($semua || $halaman >= $totalHalaman) ? 'is-disabled' : '' ?>"
                           href="<?= esc(galeri_url(['hal' => min($totalHalaman, $halaman + 1)])) ?>#galeri">Selanjutnya</a>
                    </div>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

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
