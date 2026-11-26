<?php
/** Profil: sejarah, sambutan, visi & misi (teks tetap) + Kepala Sekolah dan Guru (database: /api/public/guru). */
$konten = require __DIR__ . '/../../lib/konten.php';
$data   = sikesa_guru();                       // null = API gagal
$kepsek = $data['kepsek'] ?? null;
$guru   = $data['guru'] ?? [];

layout_start([
    'title'       => 'Profil - SD Negeri Kedungsari 1',
    'current'     => 'profil',
    'description' => 'Profil SD Negeri Kedungsari 1: sejarah, sambutan kepala sekolah, visi dan misi, serta guru dan karyawan.',
    'css'         => 'profil',
]);
?>
<main>
    <?php ui_hero('Profil', 'Mengenal lebih dekat<br>SD NEGERI KEDUNGSARI 1'); ?>

    <section class="section section--leaf" id="sejarah">
        <h2 class="title">Sejarah</h2>
        <article class="sejarah-card">
            <h3 class="title title--sm">Awal Berdirinya</h3>
            <?php foreach ($konten['sejarah'] as $blok): ?>
                <h4><?= esc($blok['judul']) ?></h4>
                <?php foreach ($blok['isi'] as $p): ?><p><?= esc($p) ?></p><?php endforeach; ?>
            <?php endforeach; ?>
        </article>
    </section>

    <section class="section section--tint" id="sambutan">
        <h2 class="title">Sambutan Kepala Sekolah</h2>
        <div class="sambutan">
            <div class="sambutan__photo">
                <img src="<?= esc(sikesa_img($kepsek['foto'] ?? '', 'img/guru-default.svg')) ?>" alt="<?= esc($kepsek['nama'] ?? 'Kepala Sekolah') ?>" width="104" height="104">
            </div>
            <div class="sambutan__card">
                <blockquote>&ldquo;<?= esc($konten['visi']) ?>&rdquo;</blockquote>
                <?php foreach ($konten['sambutan'] as $p): ?><p><?= esc($p) ?></p><?php endforeach; ?>
                <div class="sambutan__name">
                    <?php if ($kepsek): ?><strong><?= esc($kepsek['nama']) ?></strong><?php endif; ?>
                    <small><?= esc($kepsek['jabatan'] ?? '') ?: 'Kepala Sekolah' ?> SDN Kedungsari 1</small>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="visi-misi">
        <h2 class="title">Visi &amp; Misi</h2>
        <div class="visimisi">
            <div class="visi"><h3>VISI</h3><p>&ldquo;<?= esc($konten['visi']) ?>&rdquo;</p></div>
            <div class="misi"><h3>MISI</h3><ol><?php foreach ($konten['misi'] as $m): ?><li><?= esc($m) ?></li><?php endforeach; ?></ol></div>
        </div>
    </section>

    <section class="section section--leaf" id="guru">
        <?php if ($data === null): ?>
            <h2 class="title">Guru &amp; Karyawan</h2>
            <?php ui_gagal('Data guru', page_url('profil', [], 'guru')); ?>
        <?php elseif (!$kepsek && !$guru): ?>
            <h2 class="title">Guru &amp; Karyawan</h2>
            <?php ui_kosong('Belum ada data guru yang dipublikasikan.'); ?>
        <?php else: ?>
            <?php if ($kepsek): ?>
                <h2 class="title">Kepala Sekolah</h2>
                <?php $g = $kepsek; $kls = 'guru guru--kepsek'; require __DIR__ . '/kartu-guru.php'; ?>
            <?php endif; ?>

            <?php if ($guru): ?>
                <h2 class="title<?= $kepsek ? ' title--gap' : '' ?>">Guru</h2>
                <div class="guru-grid">
                    <?php foreach ($guru as $g): $kls = 'guru'; require __DIR__ . '/kartu-guru.php'; endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
<?php layout_end(); ?>
