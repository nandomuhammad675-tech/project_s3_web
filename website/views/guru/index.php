<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman Profil (Sejarah, Sambutan, Visi & Misi, Guru) - SD Negeri Kedungsari 1
 * SATU FILE. Letakkan di: website/views/guru/index.php
 */

$current = 'profil';

// ===== DATA =====
$data = [
    'title' => 'Profil - SD Negeri Kedungsari 1',

    // ---- Visi & Misi ----
    'visi' => 'Menumbuhkan kebaikan budi dan kecerdasan anak dalam kehangatan keluarga sekolah.',
    'misi' => [
        'Membangun iklim akademik yang mendalam dengan mengintegrasikan eksplorasi, kolaborasi, dan refleksi untuk mendukung pencapaian visi sekolah.',
        'Menyiapkan lulusan yang memiliki karakter yang kuat, kemampuan berpikir kritis, inovatif, serta memiliki daya saing yang tinggi melalui pendidikan yang mengutamakan pembelajaran mendalam.',
        'Menyelenggarakan pembelajaran berbasis teknologi dan informasi yang mendorong kolaborasi, kreativitas, dan penguasaan soft skill.',
        'Melaksanakan dan menjamin mutu layanan akademik standar nasional dan internasional yang mendukung pembelajaran mendalam.',
        'Memberikan layanan manajerial yang bermutu dan berkolaborasi dengan Dunia Usaha dan Dunia Industri (DUDI).',
    ],

    // ---- Guru ----
    'kepsek' => null,   // diisi dari database (Guru berjabatan "Kepala Sekolah")

    'guru' => [
        ['nama' => 'Aji Ariya K. D.',   'nip' => 'E41252202', 'foto' => 'assets/gambar/guru-default.svg'],
        ['nama' => 'Muhammad A. R.',    'nip' => 'E41252251', 'foto' => 'assets/gambar/guru-default.svg'],
        ['nama' => 'Muhammad R. F.',    'nip' => 'E41252552', 'foto' => 'assets/gambar/guru-default.svg'],
        ['nama' => 'Putra Suis A.',     'nip' => 'E41252606', 'foto' => 'assets/gambar/guru-default.svg'],
        ['nama' => 'Muhammad A.N.M.',   'nip' => 'E41253115', 'foto' => 'assets/gambar/guru-default.svg'],
        ['nama' => 'Muhammad V. S.',    'nip' => 'E41252273', 'foto' => 'assets/gambar/guru-default.svg'],
    ],
];
require_once __DIR__ . '/../../lib/api.php';
$data = sikesa_guru($data);
extract($data);

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'profil',
    'description' => 'Website resmi SD Negeri Kedungsari 1 - Cerdas, Berkarakter, Berprestasi',
    'css'         => 'guru',
    'js'          => 'home',
]);
?>
<main>

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= asset('gambar/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>PROFIL</h1>
            <p>Mengenal lebih dekat<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <!-- ================= SEJARAH ================= -->
    <section class="section section--leaf" id="sejarah">
        <h2 class="title">Sejarah</h2>

        <article class="sejarah-card">
            <h3 class="title title--sm">Awal Berdirinya</h3>

            <h4>26 Januari 1953</h4>
            <p>SDN Kedungsari 1 merupakan salah satu sekolah jenjang SD berstatus Negeri yang berada di wilayah Kec. Tarokan, Kab. Kediri, Jawa Timur. SDN Kedungsari 1 didirikan pada tanggal 26 Januari 1953 dengan Nomor SK Pendirian yang berada dalam naungan Kementerian Pendidikan dan Kebudayaan. Dalam kegiatan pembelajaran, sekolah yang memiliki 234 siswa ini dibimbing oleh 15 guru yang profesional di bidangnya. Kepala Sekolah SDN Kedungsari 1 saat ini adalah Fipin Nur Widyarini. Operator yang bertanggung jawab adalah Achmad Yunus.</p>
            <p>Dengan adanya keberadaan SDN Kedungsari 1, diharapkan dapat memberikan kontribusi dalam mencerdaskan anak bangsa di wilayah Kec. Tarokan, Kab. Kediri.</p>

            <h4>Akreditasi dan Sertifikasi</h4>
            <p>Sekolah ini telah terakreditasi B dengan Nomor SK Akreditasi 1334/BAN-SM/SK/2019 pada tanggal 30 November 2019.</p>

            <h4>Peserta Didik</h4>
            <p>Pada saat artikel ini ditulis, SDN Kedungsari 1 memiliki total 234 siswa yang terdiri dari 129 siswa laki-laki dan 105 siswa perempuan, di mana jumlah siswa laki-laki lebih banyak dari siswa perempuan.</p>
        </article>
    </section>

    <!-- ================= SAMBUTAN KEPALA SEKOLAH ================= -->
    <section class="section section--tint" id="sambutan">
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

    <!-- ================= GURU ================= -->
    <section class="section section--leaf" id="guru">
        <?php if ($kepsek): ?>
        <h2 class="title">Kepala Sekolah</h2>

        <figure class="guru guru--kepsek">
            <img src="<?= sikesa_img($kepsek['foto']) ?>" alt="<?= esc($kepsek['nama']) ?>" loading="lazy">
            <figcaption>
                <strong><?= esc($kepsek['nama']) ?></strong>
                <?php if (!empty($kepsek['jabatan'])): ?><em><?= esc($kepsek['jabatan']) ?></em><?php endif; ?>
                <?php if (!empty($kepsek['nip'])): ?><span>NIP. <?= esc($kepsek['nip']) ?></span><?php endif; ?>
            </figcaption>
        </figure>
        <?php endif; ?>

        <h2 class="title title--gap">Guru - Guru</h2>

        <div class="guru-grid">
            <?php foreach ($guru as $g): ?>
                <figure class="guru">
                    <img src="<?= sikesa_img($g['foto']) ?>" alt="<?= esc($g['nama']) ?>" loading="lazy">
                    <figcaption>
                        <strong><?= esc($g['nama']) ?></strong>
                        <?php if (!empty($g['jabatan'])): ?><em><?= esc($g['jabatan']) ?></em><?php endif; ?>
                        <?php if (!empty($g['nip'])): ?><span>NIP. <?= esc($g['nip']) ?></span><?php endif; ?>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </section>

</main>
<?php layout_end(); ?>
