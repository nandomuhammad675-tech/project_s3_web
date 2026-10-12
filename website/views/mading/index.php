<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_once __DIR__ . '/../../lib/api.php';
/**
 * Halaman Mading (Karya Siswa) - dibuka lewat '?page=mading'
 * Data: GET /api/public/mading (hanya karya status_tampil = 1). Tiga keadaan: ada / kosong / gagal memuat.
 */
$perHalaman = 9;
$halaman    = max(1, (int) ($_GET['hal'] ?? 1));
$mading     = sikesa_mading($halaman, $perHalaman);       // null = API tidak terjangkau
$items      = $mading['items'] ?? [];
$total      = (int) ($mading['total'] ?? 0);
$totalHal   = max(1, (int) ceil($total / $perHalaman));

layout_start([
    'title'       => 'Mading Karya Siswa - SD Negeri Kedungsari 1',
    'current'     => 'mading',
    'description' => 'Mading karya siswa SD Negeri Kedungsari 1 - gambar, tulisan, dan kreasi siswa',
    'css'         => 'mading',
]);
?>
<main>
    <section class="hero page-hero" style="--bg:url('<?= asset('gambar/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>MADING</h1>
            <p>Karya siswa-siswi yang membanggakan<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <section class="section section--leaf" id="mading">
        <h2 class="title">Karya Siswa</h2>

        <?php if ($mading === null): ?>
            <div class="status status--gagal" role="alert">
                <p>Data mading gagal dimuat. Periksa koneksi internet lalu coba lagi.</p>
                <a class="pill pill--solid" href="<?= page_url('mading') ?>">Coba Lagi</a>
            </div>
        <?php elseif (!$items): ?>
            <p class="status">Belum ada karya yang dipublikasikan.</p>
        <?php else: ?>
            <div class="mading-grid">
                <?php foreach ($items as $k): ?>
                    <article class="mading-card">
                        <img class="mading-card__img" src="<?= esc(sikesa_img($k['file_gambar'] ?? '')) ?>"
                             alt="<?= esc($k['judul_karya']) ?>" loading="lazy">
                        <div class="mading-card__body">
                            <?php if (!empty($k['kategori'])): ?><span class="mading-card__kat"><?= esc($k['kategori']) ?></span><?php endif; ?>
                            <h3><?= esc($k['judul_karya']) ?></h3>
                            <?php if (!empty($k['nama_penulis'])): ?><p class="mading-card__penulis">Oleh <?= esc($k['nama_penulis']) ?></p><?php endif; ?>
                            <?php if (!empty($k['deskripsi'])): ?><p class="mading-card__desk"><?= esc($k['deskripsi']) ?></p><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalHal > 1): ?>
                <nav class="pager" aria-label="Halaman mading">
                    <div class="pager__nav">
                        <a class="pill <?= $halaman <= 1 ? 'is-disabled' : '' ?>" href="<?= page_url('mading') ?>&amp;hal=<?= max(1, $halaman - 1) ?>#mading">Sebelumnya</a>
                        <span class="pager__info">Halaman <?= $halaman ?> dari <?= $totalHal ?></span>
                        <a class="pill <?= $halaman >= $totalHal ? 'is-disabled' : '' ?>" href="<?= page_url('mading') ?>&amp;hal=<?= min($totalHal, $halaman + 1) ?>#mading">Selanjutnya</a>
                    </div>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
<?php layout_end(); ?>
