<?php
/**
 * Halaman Home - SD Negeri Kedungsari 1
 * File tunggal (PHP biasa, tanpa CodeIgniter).
 * Tes: taruh di website/public/home.php, buka http://localhost/project_s3_web/website/public/home.php
 * Pakai sebagai halaman utama: salin seluruh isinya ke website/views/home/index.php
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fungsi bantu (dilewati jika framework Anda sudah punya)
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        static $base = null;
        if ($base === null) {
            $base = '';
            // Cari folder yang berisi "assets" (website/public), lalu hitung URL-nya dari document root
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
            // Cadangan jika tidak ketemu
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

// ===== ALAMAT HALAMAN (ubah di sini jika route proyek Anda berbeda) =====
// Alamat dibaca oleh public/index.php: '?page=guru' membuka views/guru/index.php
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

$current = 'home';

// ===== DATA =====
$data = [
    'title' => 'SD Negeri Kedungsari 1',

    'berita' => [
        ['img' => 'assets/img/berita/berita-1.jpg', 'judul' => 'Lorong kelas'],
        ['img' => 'assets/img/berita/berita-2.jpg', 'judul' => 'Halaman sekolah'],
        ['img' => 'assets/img/berita/berita-3.jpg', 'judul' => 'Gedung sekolah'],
    ],

    'kedisiplinan' => [
        ['img' => 'assets/img/kedisiplinan/upacara.jpg', 'nama' => 'Senin Disiplin',  'ket' => '(Upacara)'],
        ['img' => 'assets/img/kedisiplinan/senam.jpg',   'nama' => 'Selasa Bugar',    'ket' => '(Senam)'],
        ['img' => 'assets/img/kedisiplinan/yasin.jpg',   'nama' => 'Rabu Religi',     'ket' => '(Tahlil dan pembacaan Yasin)'],
        ['img' => 'assets/img/kedisiplinan/budaya.jpg',  'nama' => 'Kamis Berbudaya', 'ket' => '(Pengenalan Bahasa daerah dan sopan santun/etika)'],
        ['img' => 'assets/img/kedisiplinan/infaq.jpg',   'nama' => "Jum'at Ikhlas",   'ket' => '(Infaq)'],
        ['img' => 'assets/img/kedisiplinan/sabtu.jpg',   'nama' => 'Sabtu Ceria',     'ket' => '(Ekstra Pramuka, Tari, dll)'],
    ],

    'ekstra' => [
        ['img' => 'assets/img/ekstra/tari.jpg',     'nama' => 'Ekstra', 'ket' => '(Tari)'],
        ['img' => 'assets/img/ekstra/hadroh.jpg',   'nama' => 'Ekstra', 'ket' => '(Hadroh)'],
        ['img' => 'assets/img/ekstra/senirupa.jpg', 'nama' => 'Ekstra', 'ket' => '(Seni Rupa)'],
        ['img' => 'assets/img/ekstra/pramuka.jpg',  'nama' => 'Ekstra', 'ket' => '(Pramuka)'],
        ['img' => 'assets/img/ekstra/menyanyi.jpg', 'nama' => 'Ekstra', 'ket' => '(Menyanyi)'],
        ['img' => 'assets/img/ekstra/komputer.jpg', 'nama' => 'Ekstra Hari Kamis', 'ket' => '(Komputer)'],
    ],

    'visi' => 'Menumbuhkan kebaikan budi dan kecerdasan anak dalam kehangatan keluarga sekolah.',

    'misi' => [
        'Membangun iklim akademik yang mendalam dengan mengintegrasikan eksplorasi, kolaborasi, dan refleksi untuk mendukung pencapaian visi sekolah.',
        'Menyiapkan lulusan yang memiliki karakter yang kuat, kemampuan berpikir kritis, inovatif, serta memiliki daya saing yang tinggi melalui pendidikan yang mengutamakan pembelajaran mendalam.',
        'Menyelenggarakan pembelajaran berbasis teknologi dan informasi yang mendorong kolaborasi, kreativitas, dan penguasaan soft skill.',
        'Melaksanakan dan menjamin mutu layanan akademik standar nasional dan internasional yang mendukung pembelajaran mendalam.',
        'Memberikan layanan manajerial yang bermutu dan berkolaborasi dengan Dunia Usaha dan Dunia Industri (DUDI).',
    ],

    'youtubeId' => 'YOUTUBE_VIDEO_ID', // ganti dengan ID video sekolah
];
extract($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title ?? 'SD Negeri Kedungsari 1') ?></title>
<meta name="description" content="Website resmi SD Negeri Kedungsari 1 - Cerdas, Berkarakter, Berprestasi">

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
.hero__content{position:relative;color:#fff;padding:0 6vw 12vh;max-width:720px}
.hero__small{font-size:.85rem;font-weight:600;letter-spacing:.5px}
.hero h1{font-size:clamp(2.2rem,5vw,3.4rem);line-height:1.1;font-weight:700;margin:6px 0 10px}
.hero__tag{font-size:1.05rem;margin-bottom:18px}
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
.title--plain{color:var(--ink);margin-bottom:8px}
.title--plain::after{display:none}
.kicker{color:var(--muted);font-weight:500;letter-spacing:.5px}
.subtitle{color:var(--muted);font-size:.9rem;margin:-18px 0 34px}
.title--plain + .subtitle{margin-top:0}

/* ===== Sambutan ===== */
.sambutan{position:relative;max-width:760px;margin:70px auto 0;padding-top:0}
.sambutan__photo{position:absolute;top:-52px;left:50%;transform:translateX(-50%);z-index:2;
    width:104px;height:104px;border-radius:50%;overflow:hidden;border:4px solid #fff;box-shadow:0 4px 14px rgba(0,0,0,.2)}
.sambutan__photo img{width:100%;height:100%;object-fit:cover}
.sambutan__card{background:var(--card-blue);border:2px solid #4b5a4a;border-radius:18px;padding:70px 40px 28px;
    box-shadow:0 6px 0 #8b9a86;text-align:center}
.sambutan__card blockquote{font-weight:600;color:var(--green-dark);margin-bottom:18px}
.sambutan__card p{font-size:.78rem;color:#4b5a4a;margin-bottom:12px;text-align:justify}
.sambutan__name{text-align:left;margin-top:26px}
.sambutan__name strong{display:block;font-size:.85rem}
.sambutan__name small{font-size:.65rem;color:var(--muted)}

/* ===== Carousel ===== */
.carousel{position:relative;height:340px;max-width:900px;margin:0 auto}
.carousel__slide{position:absolute;top:50%;left:50%;width:32%;height:80%;border-radius:var(--radius);overflow:hidden;
    opacity:0;transform:translate(-50%,-50%) scale(.7);transition:all .5s ease;pointer-events:none}
.carousel__slide img{width:100%;height:100%;object-fit:cover}
.carousel__slide.is-active{opacity:1;z-index:3;width:34%;height:100%;transform:translate(-50%,-50%);box-shadow:0 10px 26px rgba(0,0,0,.25)}
.carousel__slide.is-prev{opacity:.6;z-index:1;transform:translate(-155%,-50%)}
.carousel__slide.is-next{opacity:.6;z-index:1;transform:translate(55%,-50%)}
.carousel__dots{display:flex;justify-content:center;gap:10px;margin:26px 0 18px}
.carousel__dots button{width:9px;height:9px;border-radius:50%;border:0;background:#c8cabb;cursor:pointer}
.carousel__dots button.is-on{background:var(--green);width:22px;border-radius:9px}
.carousel__nav{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
.pill{font:inherit;font-size:.75rem;font-weight:600;padding:5px 16px;border-radius:999px;cursor:pointer;
    background:#fff;color:var(--green-dark);border:1.5px solid var(--green)}
.pill--solid{background:var(--green);color:#fff}
.pill:hover{background:var(--green-dark);color:#fff}

/* ===== Grid foto ===== */
.grid3{position:relative;z-index:1;display:grid;grid-template-columns:repeat(3,1fr);gap:34px 28px;max-width:1000px;margin:0 auto}
.item img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.15)}
.item h3{margin-top:12px;font-size:.88rem;font-weight:600;color:var(--green);line-height:1.35}
.item h3 span{font-weight:500}

/* ===== Visi Misi ===== */
.visimisi{max-width:620px;margin:0 auto;display:grid;gap:22px}
.visi{background:var(--green-soft);border-radius:12px;padding:20px 28px;text-align:center}
.visi h3,.misi h3{font-size:1.1rem;margin-bottom:8px}
.visi p{font-weight:600;font-size:.9rem}
.misi{background:var(--cream);border-radius:12px;padding:22px 30px;text-align:left}
.misi h3{text-align:center}
.misi ol{padding-left:18px;font-size:.82rem;font-weight:600;display:grid;gap:6px}

/* ===== Video ===== */
.video{position:relative;max-width:860px;aspect-ratio:16/9;margin:34px auto 0;border-radius:18px;overflow:hidden;
    background:#3b4a38 var(--bg) center/cover;border:3px solid var(--green);cursor:pointer}
.video iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.video__play{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:64px;height:64px;border-radius:50%;
    border:0;background:#fff;color:#111;display:grid;place-items:center;box-shadow:0 4px 14px rgba(0,0,0,.4);cursor:pointer}

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
    .grid3{grid-template-columns:repeat(2,1fr)}
    .footer__inner{grid-template-columns:1fr 1fr}
    .carousel{height:260px}
}
@media (max-width:520px){
    .grid3{grid-template-columns:1fr}
    .footer__inner{grid-template-columns:1fr}
    .sambutan__card{padding:70px 20px 24px}
    .carousel__slide.is-prev,.carousel__slide.is-next{opacity:0}
    .carousel__slide.is-active{width:80%}
}
    </style>
</head>
<body>
<?php
// Menu aktif ditentukan oleh variabel $current (home, profil, ...)
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
    <section class="hero" style="--bg:url('<?= base_url('assets/home.jpg') ?>')">
        <div class="hero__content">
            <p class="hero__small">SELAMAT DATANG DI WEBSITE</p>
            <h1>SD NEGERI<br>KEDUNGSARI 1</h1>
            <p class="hero__tag">Cerdas &bull; Berkarakter &bull; Berprestasi</p>
            <a href="<?= base_url('ppdb') ?>" class="btn-ppdb btn-ppdb--hero">Pendaftaran Siswa Baru</a>
        </div>
    </section>

    <!-- ================= SAMBUTAN KEPALA SEKOLAH ================= -->
    <section class="section section--leaf" id="sambutan">
        <h2 class="title">Sambutan Kepala Sekolah</h2>

        <div class="sambutan">
            <div class="sambutan__photo">
                <img src="<?= base_url('assets/profil.jpg') ?>" alt="Lestari, S.Pd., M.Pd.">
            </div>

            <div class="sambutan__card">
                <blockquote>"<?= esc($visi) ?>"</blockquote>
                <p>Selamat datang di SD Negeri Kedungsari 1. Kami percaya bahwa pendidikan dasar adalah pondasi utama pembentukan jiwa. Setiap anak memiliki keunikan dan potensi tersendiri yang kami rawat dengan penuh ketulusan, rasa aman, dan kasih sayang.</p>
                <p>Melalui kerja sama erat bersama para orang tua, kami berkomitmen memberikan generasi yang berakhlak mulia, berwawasan luas, serta siap menyongsong masa depan dengan percaya diri.</p>

                <div class="sambutan__name">
                    <strong>Lestari, S.Pd., M.Pd.</strong>
                    <small>Kepala Sekolah SDN Kedungsari 1</small>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= ARTIKEL & BERITA ================= -->
    <section class="section section--tint" id="berita">
        <h2 class="title">Artikel &amp; Berita</h2>
        <p class="subtitle">Ikuti update terbaru seputar kegiatan dan informasi sekolah</p>

        <div class="carousel">
            <?php foreach ($berita as $b): ?>
                <figure class="carousel__slide">
                    <img src="<?= base_url($b['img']) ?>" alt="<?= esc($b['judul']) ?>">
                </figure>
            <?php endforeach; ?>
        </div>

        <div class="carousel__dots">
            <?php foreach ($berita as $i => $b): ?>
                <button type="button" aria-label="Berita ke-<?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>

        <div class="carousel__nav">
            <button type="button" id="prevBtn" class="pill">Sebelumnya</button>
            <a href="<?= base_url('berita') ?>" class="pill pill--solid">Lihat Semua Berita</a>
            <button type="button" id="nextBtn" class="pill">Selanjutnya</button>
        </div>
    </section>

    <!-- ================= KEDISIPLINAN ================= -->
    <section class="section section--leaf" id="kedisiplinan">
        <h2 class="title">Kedisiplinan</h2>
        <div class="grid3">
            <?php foreach ($kedisiplinan as $item): ?>
                <article class="item">
                    <img src="<?= base_url($item['img']) ?>" alt="<?= esc($item['nama'] . ' ' . $item['ket']) ?>" loading="lazy">
                    <h3><?= esc($item['nama']) ?><br><span><?= esc($item['ket']) ?></span></h3>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================= EKSTRAKURIKULER ================= -->
    <section class="section section--tint" id="ekstra">
        <h2 class="title">Ekstrakurikuler</h2>
        <div class="grid3">
            <?php foreach ($ekstra as $item): ?>
                <article class="item">
                    <img src="<?= base_url($item['img']) ?>" alt="<?= esc($item['nama'] . ' ' . $item['ket']) ?>" loading="lazy">
                    <h3><?= esc($item['nama']) ?><br><span><?= esc($item['ket']) ?></span></h3>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================= VISI & MISI ================= -->
    <section class="section" id="visi-misi">
        <h2 class="title">Visi &amp; Misi</h2>

        <div class="visimisi">
            <div class="visi">
                <h3>VISI</h3>
                <p>"<?= esc($visi) ?>"</p>
            </div>

            <div class="misi">
                <h3>MISI</h3>
                <ol>
                    <?php foreach ($misi as $m): ?>
                        <li><?= esc($m) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </section>

    <!-- ================= VIDEO PROFIL ================= -->
    <section class="section section--leaf" id="video">
        <p class="kicker">PROFIL SEKOLAH</p>
        <h2 class="title title--plain">Video Profil Sekolah</h2>
        <p class="subtitle">Mengenal lebih dekat SD Negeri Kedungsari 1 melalui video profil kami.</p>

        <div class="video" id="videoBox" data-yt="<?= esc($youtubeId) ?>"
             style="--bg:url('<?= base_url('assets/img/video-thumb.jpg') ?>')">
            <button type="button" class="video__play" aria-label="Putar video">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            </button>
        </div>
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
                <li><a href="<?= base_url('profil#visi-misi') ?>">Visi &amp; Misi</a></li>
                <li><a href="<?= base_url('ppdb') ?>">Pendaftaran Siswa Baru</a></li>
                <li><a href="<?= base_url('berita') ?>">Artikel &amp; Berita Terkini</a></li>
                <li><a href="<?= base_url('profil#unit') ?>">Unit-Unit</a></li>
                <li><a href="<?= base_url('guru') ?>">Guru &amp; Karyawan</a></li>
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
function initNavbar() {
    const toggle = document.getElementById('navToggle');
    const menu   = document.getElementById('navMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open);
    });
}

/* ===== Carousel berita ===== */
function initCarousel() {
    const slides = document.querySelectorAll('.carousel__slide');
    const dots   = document.querySelectorAll('.carousel__dots button');
    const total  = slides.length;
    if (!total) return;

    let current = Math.min(1, total - 1);
    let timer;

    const render = () => {
        slides.forEach((slide, i) => {
            slide.classList.toggle('is-active', i === current);
            slide.classList.toggle('is-prev', i === (current - 1 + total) % total && i !== current);
            slide.classList.toggle('is-next', i === (current + 1) % total && i !== current);
        });
        dots.forEach((dot, i) => dot.classList.toggle('is-on', i === current));
    };

    const goTo = (index) => {
        current = (index + total) % total;
        render();
        restartAutoplay();
    };

    const restartAutoplay = () => {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), 5000);
    };

    document.getElementById('prevBtn')?.addEventListener('click', () => goTo(current - 1));
    document.getElementById('nextBtn')?.addEventListener('click', () => goTo(current + 1));
    dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));

    render();
    restartAutoplay();
}

/* ===== Video profil (iframe dimuat saat diklik) ===== */
function initVideo() {
    const box = document.getElementById('videoBox');
    if (!box) return;

    box.addEventListener('click', () => {
        const iframe = document.createElement('iframe');
        iframe.src = `https://www.youtube.com/embed/${box.dataset.yt}?autoplay=1`;
        iframe.allow = 'autoplay; encrypted-media';
        iframe.allowFullscreen = true;
        iframe.title = 'Video profil sekolah';
        box.replaceChildren(iframe);
    }, { once: true });
}

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initCarousel();
    initVideo();
});

</script>
</body>
</html>