<?php
/**
 * Halaman Berita & Artikel - SD Negeri Kedungsari 1
 * SATU FILE. Letakkan di: website/views/kegiatan/index.php
 * (dibuka lewat '?page=kegiatan', sesuai peta di page_url())
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===== Fungsi bantu (dilewati jika framework Anda sudah punya) =====
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

$current = 'berita';

// ===== Format tanggal Indonesia =====
function tanggal_id(string $ymd): string {
    static $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                     'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = strtotime($ymd);
    return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
}

// ===== DATA BERITA =====
// Tambah / ubah berita di sini (nanti bisa diganti dengan data dari database).
// 'tanggal' memakai format YYYY-MM-DD. 'foto' relatif terhadap folder public.
$beritaList = [
    [
        'slug'    => 'kelas-digital-nanga-pinoh',
        'judul'   => 'Guru di Nanga Pinoh, Kabupaten Melawi Menyelenggarakan Pembelajaran Kelas secara Digital yang Menyenangkan untuk Siswa SD Kelas 1',
        'ringkas' => 'Veronica Dewi Sartika, S. Pd. SD merupakan guru kelas 1 SD yang bertugas di Kabupaten Melawi, Kalimantan Barat.',
        'tanggal' => '2026-09-18',
        'foto'    => 'assets/img/berita/berita-1.jpg',
    ],
    [
        'slug'    => 'sd-makassar-disegel',
        'judul'   => 'Kompleks SD di Makassar disegel dan seribu murid \'dirumahkan\' – Mengapa perlindungan hak pendidikan anak \'lemah\'?',
        'ringkas' => 'Sekitar seribu murid Sekolah Dasar (SD) di Makassar, Sulawesi Selatan, disebut belajar dari rumah karena kompleks sekolah mereka disegel. Kompleks sekolah tersebut menjadi pusat sengketa lahan.',
        'tanggal' => '2024-07-24',
        'foto'    => 'assets/img/berita/berita-2.jpg',
    ],
    [
        'slug'    => 'anak-sd-berkunjung-balai-kota',
        'judul'   => 'Keseruan Anak SD Berkunjung ke Balai Kota Jakarta',
        'ringkas' => 'Sebanyak 50 murid Sekolah Dasar (SD) dari wilayah Jakarta Pusat berkunjung ke Balai Kota DKI Jakarta, Jalan Medan Merdeka Selatan, Gambir, Rabu (23/7). Dalam kunjungannya anak-anak diajak berkeliling dan berdialog langsung dengan Gubernur DKI Jakarta, Pramono Anung.',
        'tanggal' => '2025-07-23',
        'foto'    => 'assets/img/berita/berita-3.jpg',
    ],
    [
        'slug'    => 'empat-siswa-disetrum-kepsek',
        'judul'   => 'Empat siswa SD di Malang yang disetrum kepsek untuk \'terapi\'',
        'ringkas' => 'Polisi melakukan penyelidikan atas dugaan penyetruman terhadap empat siswa sekolah dasar oleh kepala sekolah, kendati orang tua siswa dan sekolah menyebut persoalannya sudah \'diselesaikan secara kekeluargaan\'.',
        'tanggal' => '2017-05-03',
        'foto'    => 'assets/img/berita/berita-4.jpg',
    ],
    [
        'slug'    => 'keracunan-mbg-blora',
        'judul'   => '19 Siswa SD Diduga Keracunan MBG, Dinkes Blora Buka Suara',
        'ringkas' => 'BLORA, Beritajateng.id – Dinas Kesehatan (Dinkes) Kabupaten Blora buka suara mengenai dugaan keracunan makanan dalam program Makan Bergizi Gratis (MBG) di SDN Sendangrejo, Kecamatan Ngawen, pada Senin, 20 Juli 2026.',
        'tanggal' => '2026-07-20',
        'foto'    => 'assets/img/berita/berita-5.jpg',
    ],
    [
        'slug'    => 'sekolah-siaga-bencana',
        'judul'   => 'Dinsos Abdya Lakukan Penyuluhan Sekolah Siaga Bencana kepada Murid SD',
        'ringkas' => 'BLANGPIDIE – Dinas Sosial (Dinsos) Aceh Barat Daya (Abdya) menerjunkan Penyuluh Sosial dan Pekerja Sosial untuk memberikan penyuluhan Sekolah Siaga Bencana (SSB) kepada pelajar tingkat sekolah dasar (SD) di wilayah kabupaten setempat.',
        'tanggal' => '2026-08-29',
        'foto'    => 'assets/img/berita/berita-6.jpg',
    ],
];

// Urutkan dari yang terbaru
usort($beritaList, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

// ===== Halaman berita (paginasi) =====
// ?hal=2 -> halaman ke-2 | ?hal=semua -> tampilkan seluruh berita
$perHalaman = 6;
$totalBerita = count($beritaList);
$totalHalaman = max(1, (int) ceil($totalBerita / $perHalaman));
$semua = (($_GET['hal'] ?? '') === 'semua');

if ($semua) {
    $halaman = 1;
    $tampil  = $beritaList;
} else {
    $halaman = max(1, min($totalHalaman, (int) ($_GET['hal'] ?? 1)));
    $tampil  = array_slice($beritaList, ($halaman - 1) * $perHalaman, $perHalaman);
}

// Alamat tautan halaman ini + detail berita
function berita_url(array $params = []): string {
    return page_url('berita') . ($params ? '&' . http_build_query($params) : '');
}
function detail_url(string $slug): string {
    // Ubah di sini jika halaman detail berita Anda memakai route lain
    return page_url('berita') . '&slug=' . rawurlencode($slug);
}

$title = 'Berita & Artikel - SD Negeri Kedungsari 1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<meta name="description" content="Informasi terbaru dan kabar terkini seputar kegiatan SD Negeri Kedungsari 1">

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
.btn-ppdb--hero{padding:9px 22px!important;font-size:.9rem}

/* ===== Section umum ===== */
.section{position:relative;padding:72px 6vw;text-align:center;overflow:hidden}
.section--tint{background:var(--sage)}
.section--leaf::before,.section--leaf::after{content:"";position:absolute;width:190px;height:260px;opacity:.28;pointer-events:none;
    background:no-repeat center/contain url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 140'><path d='M50 4C95 40 98 100 50 136 2 100 5 40 50 4Z' fill='%236f8a62'/><path d='M50 14V132' stroke='%23fbfaf4' stroke-width='2'/></svg>")}
.section--leaf::before{left:-60px;top:40px;transform:rotate(-25deg)}
.section--leaf::after{right:-60px;top:20px;transform:rotate(30deg) scaleX(-1)}
.title{position:relative;display:inline-block;font-size:clamp(1.7rem,3.5vw,2.4rem);font-weight:700;color:var(--green);margin-bottom:34px;padding-bottom:12px}
.title::after{content:"";position:absolute;left:50%;bottom:0;width:60%;height:3px;background:var(--green);transform:translateX(-50%);border-radius:3px}

/* ===== Halaman Berita: hero ===== */
.page-hero{min-height:calc(100vh - 64px);min-height:calc(100svh - 64px);align-items:center;justify-content:center;text-align:center}
.page-hero__content{position:relative;color:#fff;padding:0 6vw}
.page-hero h1{font-size:clamp(2.6rem,7vw,4.4rem);font-weight:700;letter-spacing:1px;line-height:1.1}
.page-hero p{font-size:clamp(1rem,2.2vw,1.35rem);font-weight:500;margin-top:6px}

/* ===== Halaman Berita: kartu ===== */
.berita-grid{position:relative;z-index:1;display:grid;grid-template-columns:repeat(2,1fr);gap:44px 40px;
    max-width:1100px;margin:0 auto;text-align:left}
.berita-card{display:flex;flex-direction:column;background:var(--card-grey);border:3px solid var(--green);
    border-radius:18px;overflow:hidden;box-shadow:0 6px 0 rgba(91,117,83,.35);
    transition:transform .2s,box-shadow .2s}
.berita-card:hover{transform:translateY(-4px);box-shadow:0 10px 0 rgba(91,117,83,.35)}
.berita-card__link{display:flex;flex-direction:column;height:100%}
.berita-card__img{aspect-ratio:3/2;width:100%;object-fit:cover;background:#c9d1bf;border-bottom:3px solid var(--green)}
.berita-card__body{display:flex;flex-direction:column;flex:1;padding:16px 18px 14px}
.berita-card h3{font-size:.95rem;font-weight:700;line-height:1.35;color:#111a2e;margin-bottom:8px}
.berita-card p{font-size:.82rem;color:#2b3550;line-height:1.5;margin-bottom:14px}
.berita-card time{margin-top:auto;font-size:.75rem;color:var(--muted)}
.berita-kosong{position:relative;z-index:1;color:var(--muted)}

/* ===== Paginasi ===== */
.pager__dots{position:relative;z-index:1;display:flex;justify-content:center;gap:10px;margin:46px 0 18px}
.pager__dots a{width:11px;height:11px;border-radius:50%;background:#c8cabb;display:block}
.pager__dots a.is-on{background:var(--green);width:26px;border-radius:9px}
.pager__nav{position:relative;z-index:1;display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
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
    .berita-grid{grid-template-columns:1fr}
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
            <h1>Berita &amp; Artikel</h1>
            <p>Informasi terbaru dan kabar terkini seputar kegiatan<br>SD NEGERI KEDUNGSARI 1.</p>
        </div>
    </section>

    <!-- ================= DAFTAR BERITA ================= -->
    <section class="section section--leaf" id="berita">
        <h2 class="title">Berita &amp; Artikel</h2>

        <?php if (!$tampil): ?>
            <p class="berita-kosong">Belum ada berita yang dipublikasikan.</p>
        <?php else: ?>
            <div class="berita-grid">
                <?php foreach ($tampil as $b): ?>
                    <article class="berita-card">
                        <a class="berita-card__link" href="<?= esc(detail_url($b['slug'])) ?>">
                            <img class="berita-card__img"
                                 src="<?= base_url($b['foto']) ?>"
                                 alt="<?= esc($b['judul']) ?>" loading="lazy">
                            <div class="berita-card__body">
                                <h3><?= esc($b['judul']) ?></h3>
                                <p><?= esc(mb_strimwidth($b['ringkas'], 0, 190, '…', 'UTF-8')) ?></p>
                                <time datetime="<?= esc($b['tanggal']) ?>"><?= esc(tanggal_id($b['tanggal'])) ?></time>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- ===== Paginasi ===== -->
            <nav class="pager" aria-label="Halaman berita">
                <?php if ($totalHalaman > 1 && !$semua): ?>
                    <div class="pager__dots">
                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                            <a href="<?= esc(berita_url(['hal' => $i])) ?>#berita"
                               class="<?= $i === $halaman ? 'is-on' : '' ?>"
                               aria-label="Halaman <?= $i ?>"
                               <?= $i === $halaman ? 'aria-current="page"' : '' ?>></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <div class="pager__nav">
                    <a class="pill <?= ($semua || $halaman <= 1) ? 'is-disabled' : '' ?>"
                       href="<?= esc(berita_url(['hal' => max(1, $halaman - 1)])) ?>#berita">Sebelumnya</a>

                    <?php if ($semua): ?>
                        <a class="pill pill--solid" href="<?= esc(berita_url()) ?>#berita">Tampilkan Per Halaman</a>
                    <?php else: ?>
                        <a class="pill pill--solid" href="<?= esc(berita_url(['hal' => 'semua'])) ?>#berita">Lihat Semua Berita</a>
                    <?php endif; ?>

                    <a class="pill <?= ($semua || $halaman >= $totalHalaman) ? 'is-disabled' : '' ?>"
                       href="<?= esc(berita_url(['hal' => min($totalHalaman, $halaman + 1)])) ?>#berita">Selanjutnya</a>
                </div>
            </nav>
        <?php endif; ?>
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
            <!-- Ganti dengan embed Google Maps sekolah -->
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