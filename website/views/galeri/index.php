<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman Galeri - SD Negeri Kedungsari 1
 * Letakkan di: website/views/galeri/index.php
 * Dibuka lewat '?page=galeri'
 */

if (!function_exists('galeri_url')) {
    function galeri_url(array $params = []): string {
        return page_url('galeri') . ($params ? '&' . http_build_query($params) : '');
    }
}

$current = 'galeri';

// ===== DATA (urutan mengikuti data.php) =====
require_once __DIR__ . '/../../lib/api.php';
$galeriList = sikesa_galeri(require __DIR__ . '/data.php');

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

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'galeri',
    'description' => 'Galeri kegiatan SD Negeri Kedungsari 1 - merekam momen berharga dan dokumentasi kegiatan',
    'css'         => 'galeri',
    'js'          => null,
]);
?>
<main>

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= asset('img/home.jpg') ?>')">
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
                        <img class="galeri-card__img" src="<?= sikesa_img($g['foto']) ?>"
                             alt="<?= esc($g['judul']) ?>" loading="lazy">
                        <h3><?= esc($g['judul']) ?></h3>
                        <?php if (!empty($g['tanggal'])): ?><time datetime="<?= esc($g['tanggal']) ?>"><?= esc(tanggal_id($g['tanggal'])) ?></time><?php endif; ?>
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
<?php layout_end(); ?>
