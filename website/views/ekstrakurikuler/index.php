<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_once __DIR__ . '/../../lib/api.php';
/**
 * Halaman Ekstrakurikuler - dibuka lewat '?page=ekstrakurikuler[&hal=2]'. Klik kartu -> '?page=ekstrakurikuler&slug=...'
 * Desain kartu dan paginasi sama dengan Kegiatan & Prestasi (kartu .kartu--post di style.css).
 * Data: daftar dari database (/api/public/ekstrakurikuler); data.php = cadangan bila API tidak terjangkau.
 */
if (isset($_GET['slug'])) { require __DIR__ . '/detail.php'; return; }

$semua      = sikesa_ekskul(require __DIR__ . '/data.php');
$perHalaman = 6;
$total      = count($semua);
$totalHal   = max(1, (int) ceil($total / $perHalaman));
$hal        = max(1, min($totalHal, (int) ($_GET['hal'] ?? 1)));
$tampil     = array_slice($semua, ($hal - 1) * $perHalaman, $perHalaman);
$urlHal     = static fn(int $h): string => page_url('ekstra', $h > 1 ? ['hal' => $h] : []);

layout_start([
    'title'       => 'Ekstrakurikuler - SD Negeri Kedungsari 1',
    'current'     => 'ekstra',
    'description' => 'Ekstrakurikuler SD Negeri Kedungsari 1 - mengembangkan minat, bakat, dan potensi siswa',
]);
?>
<main>
    <?php ui_hero('Ekstrakurikuler', 'Mengembangkan minat, bakat, dan potensi siswa<br>SD NEGERI KEDUNGSARI 1'); ?>

    <section class="section section--leaf" id="ekstra">
        <h2 class="title">Ekstrakurikuler</h2>

        <?php if (!$tampil): ui_kosong('Belum ada ekstrakurikuler yang dipublikasikan.');
        else: ?>
            <div class="grid grid--2 grid--center">
                <?php foreach ($tampil as $e): ?>
                    <article class="kartu kartu--post">
                        <a class="kartu__link" href="<?= esc(page_url('ekstra', ['slug' => $e['slug']])) ?>">
                            <img class="kartu__img" src="<?= esc(sikesa_img($e['foto'] ?? '')) ?>" alt="Ekstrakurikuler <?= esc($e['nama']) ?>" loading="lazy" width="600" height="400">
                            <div class="kartu__body">
                                <span class="badge">Ekstrakurikuler</span>
                                <h3><?= esc($e['nama']) ?></h3>
                                <?php if (!empty($e['ringkas'])): ?><p><?= esc(mb_strimwidth($e['ringkas'], 0, 200, '…', 'UTF-8')) ?></p><?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php ui_pager($hal, $totalHal, $urlHal, 'Halaman ekstrakurikuler', 'ekstra'); ?>
        <?php endif; ?>
    </section>
</main>
<?php layout_end(); ?>
