<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
// Jika ada parameter "slug" -> tampilkan halaman detail ekstrakurikuler
if (isset($_GET['slug'])) { require __DIR__ . '/detail.php'; return; }

/**
 * Halaman Ekstrakurikuler - SD Negeri Kedungsari 1
 * Letakkan di: website/views/ekstrakurikuler/index.php
 * Dibuka lewat '?page=ekstrakurikuler'. Klik kartu -> '?page=ekstrakurikuler&slug=...'
 */

if (!function_exists('ekstra_url')) {
    function ekstra_url(array $params = []): string {
        return page_url('ekstra') . ($params ? '&' . http_build_query($params) : '');
    }
}

$current = 'ekstra';

// ===== DATA =====
require_once __DIR__ . '/../../lib/api.php';
$ekstraList = sikesa_ekskul(require __DIR__ . '/data.php');

// ===== Paginasi =====
// ?hal=2 -> halaman ke-2 | ?hal=semua -> tampilkan semuanya
// Navigasi paginasi baru muncul jika jumlah ekstrakurikuler lebih dari $perHalaman.
$perHalaman   = 6;
$total        = count($ekstraList);
$totalHalaman = max(1, (int) ceil($total / $perHalaman));
$semua        = (($_GET['hal'] ?? '') === 'semua');

if ($semua) {
    $halaman = 1;
    $tampil  = $ekstraList;
} else {
    $halaman = max(1, min($totalHalaman, (int) ($_GET['hal'] ?? 1)));
    $tampil  = array_slice($ekstraList, ($halaman - 1) * $perHalaman, $perHalaman);
}

$title = 'Ekstrakurikuler - SD Negeri Kedungsari 1';

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'ekstra',
    'description' => 'Ekstrakurikuler SD Negeri Kedungsari 1 - mengembangkan minat, bakat, dan potensi siswa',
    'css'         => 'ekstrakurikuler',
    'js'          => null,
]);
?>
<main>

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= asset('img/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>EKSTRAKURIKULER</h1>
            <p>Mengembangkan minat, bakat, dan potensi siswa<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <!-- ================= DAFTAR EKSTRAKURIKULER ================= -->
    <section class="section section--leaf" id="ekstra">
        <h2 class="title">Ekstrakurikuler</h2>

        <?php if (!$tampil): ?>
            <p>Belum ada ekstrakurikuler yang dipublikasikan.</p>
        <?php else: ?>
            <div class="ekstra-grid">
                <?php foreach ($tampil as $e): ?>
                    <article class="ekstra-card">
                        <a class="ekstra-card__link" href="<?= esc(ekstra_url(['slug' => $e['slug']])) ?>">
                            <img class="ekstra-card__img" src="<?= sikesa_img($e['foto']) ?>"
                                 alt="Ekstrakurikuler <?= esc($e['nama']) ?>" loading="lazy">
                            <h3><?= esc($e['nama']) ?></h3>
                            <p><?= esc($e['ringkas']) ?></p>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalHalaman > 1): ?>
                <!-- ===== Paginasi (muncul jika lebih dari satu halaman) ===== -->
                <nav class="pager" aria-label="Halaman ekstrakurikuler">
                    <?php if (!$semua): ?>
                        <div class="pager__dots">
                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                <a href="<?= esc(ekstra_url(['hal' => $i])) ?>#ekstra"
                                   class="<?= $i === $halaman ? 'is-on' : '' ?>"
                                   aria-label="Halaman <?= $i ?>"
                                   <?= $i === $halaman ? 'aria-current="page"' : '' ?>></a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>

                    <div class="pager__nav">
                        <a class="pill <?= ($semua || $halaman <= 1) ? 'is-disabled' : '' ?>"
                           href="<?= esc(ekstra_url(['hal' => max(1, $halaman - 1)])) ?>#ekstra">Sebelumnya</a>

                        <?php if ($semua): ?>
                            <a class="pill pill--solid" href="<?= esc(ekstra_url()) ?>#ekstra">Tampilkan Per Halaman</a>
                        <?php else: ?>
                            <a class="pill pill--solid" href="<?= esc(ekstra_url(['hal' => 'semua'])) ?>#ekstra">Lihat Semua Ekstra</a>
                        <?php endif; ?>

                        <a class="pill <?= ($semua || $halaman >= $totalHalaman) ? 'is-disabled' : '' ?>"
                           href="<?= esc(ekstra_url(['hal' => min($totalHalaman, $halaman + 1)])) ?>#ekstra">Selanjutnya</a>
                    </div>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>

</main>
<?php layout_end(); ?>
