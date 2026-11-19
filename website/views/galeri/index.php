<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_once __DIR__ . '/../../lib/api.php';
/**
 * Halaman Galeri - dibuka lewat '?page=galeri[&hal=2]'
 * Desain kartu, paginasi, dan posisi tengah sama dengan Kegiatan & Prestasi. Foto bisa diperbesar (lightbox, js/galeri.js).
 * Data: galeri mandiri dari database (/api/public/galeri); data.php = cadangan bila API tidak terjangkau.
 */
$galeriList = sikesa_galeri(require __DIR__ . '/data.php');

$perHalaman = 6;
$total      = count($galeriList);
$totalHal   = max(1, (int) ceil($total / $perHalaman));
$hal        = max(1, min($totalHal, (int) ($_GET['hal'] ?? 1)));
$tampil     = array_slice($galeriList, ($hal - 1) * $perHalaman, $perHalaman);
$urlHal     = static fn(int $h): string => page_url('galeri', $h > 1 ? ['hal' => $h] : []);

layout_start([
    'title'       => 'Galeri - SD Negeri Kedungsari 1',
    'current'     => 'galeri',
    'description' => 'Galeri kegiatan SD Negeri Kedungsari 1 - merekam momen berharga dan dokumentasi kegiatan',
    'js'          => 'galeri',
]);
?>
<main>
    <?php ui_hero('Galeri', 'Merekam momen berharga dan dokumentasi kegiatan<br>SD NEGERI KEDUNGSARI 1'); ?>

    <section class="section section--leaf" id="galeri">
        <h2 class="title">Galeri</h2>

        <?php if (!$tampil): ui_kosong('Belum ada foto yang dipublikasikan.');
        else: ?>
            <div class="grid grid--3 grid--center">
                <?php foreach ($tampil as $g): $u = sikesa_img($g['foto']); ?>
                    <article class="kartu kartu--post">
                        <a class="kartu__link zoom" href="<?= esc($u) ?>" data-caption="<?= esc($g['judul']) ?>">
                            <img class="kartu__img" src="<?= esc($u) ?>" alt="<?= esc($g['judul']) ?>" loading="lazy" width="600" height="400">
                            <div class="kartu__body">
                                <h3><?= esc($g['judul']) ?></h3>
                                <?php if (!empty($g['tanggal'])): ?><time datetime="<?= esc($g['tanggal']) ?>"><?= esc(tanggal_id($g['tanggal'])) ?></time><?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php ui_pager($hal, $totalHal, $urlHal, 'Halaman galeri', 'galeri'); ?>
        <?php endif; ?>
    </section>
</main>
<?php layout_end(); ?>
