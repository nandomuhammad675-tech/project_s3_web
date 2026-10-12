<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman Home - SD Negeri Kedungsari 1
 * File tunggal (PHP biasa, tanpa CodeIgniter).
 * Tes: taruh di website/public/home.php, buka http://localhost/project_s3_web/website/public/home.php
 * Pakai sebagai halaman utama: salin seluruh isinya ke website/views/home/index.php
 */

$current = 'home';

// ===== DATA =====
// Teks tetap (visi & misi). Selebihnya dibaca dari database lewat /api/public/* (lib/api.php).
$visi = 'Menumbuhkan kebaikan budi dan kecerdasan anak dalam kehangatan keluarga sekolah.';
$misi = [
    'Membangun iklim akademik yang mendalam dengan mengintegrasikan eksplorasi, kolaborasi, dan refleksi untuk mendukung pencapaian visi sekolah.',
    'Menyiapkan lulusan yang memiliki karakter yang kuat, kemampuan berpikir kritis, inovatif, serta memiliki daya saing yang tinggi melalui pendidikan yang mengutamakan pembelajaran mendalam.',
    'Menyelenggarakan pembelajaran berbasis teknologi dan informasi yang mendorong kolaborasi, kreativitas, dan penguasaan soft skill.',
    'Melaksanakan dan menjamin mutu layanan akademik standar nasional dan internasional yang mendukung pembelajaran mendalam.',
    'Memberikan layanan manajerial yang bermutu dan berkolaborasi dengan Dunia Usaha dan Dunia Industri (DUDI).',
];

require_once __DIR__ . '/../../lib/api.php';

// 3 kegiatan terbaru (event status_tampil = 1; terbaru di atas)
$beritaAll = sikesa_berita(require __DIR__ . '/../kegiatan/data.php');
usort($beritaAll, fn($a, $b) => strcmp((string) $b['tanggal'], (string) $a['tanggal']));
$berita = array_slice($beritaAll, 0, 3);

// Cuplikan program pembiasaan (null = API tidak terjangkau)
$HARI    = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => "Jum'at", 'sabtu' => 'Sabtu'];
$program = sikesa_program();

// Ekstrakurikuler (nama + deskripsi + ikon dari database) dan 3 foto galeri mandiri
$ekstra = array_slice(sikesa_ekskul(require __DIR__ . '/../ekstrakurikuler/data.php'), 0, 6);
$galeri = array_slice(sikesa_galeri(require __DIR__ . '/../galeri/data.php'), 0, 3);

// Video profil: hanya tampil bila video_profil_url (pengaturan_sistem) berisi alamat YouTube yang valid
$youtubeId = sikesa_youtube_id();

// Kepala Sekolah: Guru berjabatan "Kepala Sekolah" di database
$kepsek = sikesa_kepsek();

layout_start([
    'title'       => 'SD Negeri Kedungsari 1',
    'current'     => 'home',
    'description' => 'Website resmi SD Negeri Kedungsari 1 - Cerdas, Berkarakter, Berprestasi',
    'css'         => 'home',
    'js'          => 'home',
]);
?>
<main>

    <!-- ================= HERO ================= -->
    <section class="hero" style="--bg:url('<?= asset('gambar/home.jpg') ?>')">
        <div class="hero__content">
            <p class="hero__small">SELAMAT DATANG DI WEBSITE</p>
            <h1>SD NEGERI<br>KEDUNGSARI 1</h1>
            <p class="hero__tag">Cerdas &bull; Berkarakter &bull; Berprestasi</p>
            <a href="<?= page_url('ppdb') ?>" class="btn-ppdb btn-ppdb--hero">Pendaftaran Siswa Baru</a>
        </div>
    </section>

    <!-- ================= SAMBUTAN KEPALA SEKOLAH ================= -->
    <section class="section section--leaf" id="sambutan">
        <h2 class="title">Sambutan Kepala Sekolah</h2>

        <div class="sambutan">
            <div class="sambutan__photo">
                <img src="<?= $kepsek ? esc(sikesa_img($kepsek['foto'])) : asset('gambar/guru-default.svg') ?>" alt="<?= $kepsek ? esc($kepsek['nama']) : 'Kepala Sekolah' ?>">
            </div>

            <div class="sambutan__card">
                <blockquote>"<?= esc($visi) ?>"</blockquote>
                <p>Selamat datang di SD Negeri Kedungsari 1. Kami percaya bahwa pendidikan dasar adalah pondasi utama pembentukan jiwa. Setiap anak memiliki keunikan dan potensi tersendiri yang kami rawat dengan penuh ketulusan, rasa aman, dan kasih sayang.</p>
                <p>Melalui kerja sama erat bersama para orang tua, kami berkomitmen memberikan generasi yang berakhlak mulia, berwawasan luas, serta siap menyongsong masa depan dengan percaya diri.</p>

                <div class="sambutan__name">
                    <?php if ($kepsek): ?><strong><?= esc($kepsek['nama']) ?></strong><?php endif; ?>
                    <small><?= esc(($kepsek['jabatan'] ?? '') ?: 'Kepala Sekolah') ?> SDN Kedungsari 1</small>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= ARTIKEL & BERITA ================= -->
    <section class="section section--tint" id="berita">
        <h2 class="title">Artikel &amp; Berita</h2>
        <p class="subtitle">Ikuti update terbaru seputar kegiatan dan informasi sekolah</p>

        <?php if (!$berita): ?>
            <p class="kosong">Belum ada kegiatan yang dipublikasikan.</p>
            <div class="carousel__nav"><a href="<?= page_url('berita') ?>" class="pill pill--solid">Lihat Semua Berita</a></div>
        <?php else: ?>
        <div class="carousel">
            <?php foreach ($berita as $b): ?>
                <figure class="carousel__slide">
                    <a href="<?= page_url('berita') ?>&amp;slug=<?= rawurlencode($b['slug']) ?>">
                        <img src="<?= esc(sikesa_img($b['foto'])) ?>" alt="<?= esc($b['judul']) ?>">
                    </a>
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
            <a href="<?= page_url('berita') ?>" class="pill pill--solid">Lihat Semua Berita</a>
            <button type="button" id="nextBtn" class="pill">Selanjutnya</button>
        </div>
        <?php endif; ?>
    </section>

    <!-- ================= PROGRAM PEMBIASAAN ================= -->
    <section class="section section--leaf" id="program">
        <h2 class="title">Program Pembiasaan</h2>
        <?php if ($program === null): ?>
            <p class="kosong">Program pembiasaan gagal dimuat. <a href="<?= page_url('home') ?>#program"><u>Coba lagi</u></a></p>
        <?php elseif (!$program): ?>
            <p class="kosong">Belum ada data program pembiasaan.</p>
        <?php else: ?>
            <div class="grid3">
                <?php foreach ($program as $p): ?>
                    <article class="item">
                        <?php if (!empty($p['ikon'])): ?>
                            <img src="<?= esc(sikesa_img($p['ikon'])) ?>" alt="<?= esc($p['nama_program']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="item__hari" aria-hidden="true"><?= esc($HARI[$p['hari']] ?? $p['hari']) ?></div>
                        <?php endif; ?>
                        <h3><?= esc($p['nama_program']) ?><?php if (!empty($p['deskripsi'])): ?><br><span><?= esc($p['deskripsi']) ?></span><?php endif; ?></h3>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="carousel__nav" style="margin-top:30px"><a href="<?= page_url('program') ?>" class="pill pill--solid">Lihat Program Pembiasaan</a></div>
        <?php endif; ?>
    </section>

    <!-- ================= EKSTRAKURIKULER ================= -->
    <section class="section section--tint" id="ekstra">
        <h2 class="title">Ekstrakurikuler</h2>
        <?php if (!$ekstra): ?>
            <p class="kosong">Belum ada data ekstrakurikuler.</p>
        <?php else: ?>
            <div class="grid3">
                <?php foreach ($ekstra as $item): ?>
                    <article class="item">
                        <a href="<?= page_url('ekstra') ?>&amp;slug=<?= rawurlencode($item['slug']) ?>">
                            <img src="<?= esc(sikesa_img($item['foto'] ?? '')) ?>" alt="Ekstrakurikuler <?= esc($item['nama']) ?>" loading="lazy">
                            <h3>Ekstra<br><span><?= esc($item['nama']) ?></span></h3>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="carousel__nav" style="margin-top:30px"><a href="<?= page_url('ekstra') ?>" class="pill pill--solid">Lihat Semua Ekstrakurikuler</a></div>
        <?php endif; ?>
    </section>

    <!-- ================= GALERI ================= -->
    <section class="section section--leaf" id="galeri">
        <h2 class="title">Galeri</h2>
        <?php if (!$galeri): ?>
            <p class="kosong">Belum ada foto yang dipublikasikan.</p>
        <?php else: ?>
            <div class="grid3">
                <?php foreach ($galeri as $g): ?>
                    <article class="item">
                        <img src="<?= esc(sikesa_img($g['foto'])) ?>" alt="<?= esc($g['judul']) ?>" loading="lazy">
                        <h3><?= esc($g['judul']) ?></h3>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="carousel__nav" style="margin-top:30px"><a href="<?= page_url('galeri') ?>" class="pill pill--solid">Lihat Semua Foto</a></div>
        <?php endif; ?>
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

    <!-- ================= VIDEO PROFIL (disembunyikan bila video_profil_url kosong) ================= -->
    <?php if ($youtubeId): ?>
    <section class="section section--leaf" id="video">
        <p class="kicker">PROFIL SEKOLAH</p>
        <h2 class="title title--plain">Video Profil Sekolah</h2>
        <p class="subtitle">Mengenal lebih dekat SD Negeri Kedungsari 1 melalui video profil kami.</p>

        <div class="video" id="videoBox" data-yt="<?= esc($youtubeId) ?>"
             style="--bg:url('https://i.ytimg.com/vi/<?= esc($youtubeId) ?>/hqdefault.jpg')">
            <button type="button" class="video__play" aria-label="Putar video">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            </button>
        </div>
    </section>
    <?php endif; ?>

</main>
<?php layout_end(); ?>
