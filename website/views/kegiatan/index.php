<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman Berita & Artikel - SD Negeri Kedungsari 1
 * SATU FILE. Letakkan di: website/views/kegiatan/index.php
 * (dibuka lewat '?page=kegiatan', sesuai peta di page_url())
 */

$current = 'berita';

// ?page=kegiatan&slug=... -> halaman detail
if (isset($_GET['slug'])) { require __DIR__ . '/detail.php'; return; }

// Data dibaca dari database lewat /api/public/event; data.php hanya cadangan bila API tidak terjangkau.
require_once __DIR__ . '/../../lib/api.php';
$beritaList = sikesa_berita(require __DIR__ . '/data.php');

// Urutkan dari yang terbaru
usort($beritaList, fn($a, $b) => strcmp((string) $b['tanggal'], (string) $a['tanggal']));

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

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'berita',
    'description' => 'Informasi terbaru dan kabar terkini seputar kegiatan SD Negeri Kedungsari 1',
    'css'         => 'kegiatan',
    'js'          => null,
]);
?>
<main>

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= asset('img/home.jpg') ?>')">
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
                                 src="<?= sikesa_img($b['foto']) ?>"
                                 alt="<?= esc($b['judul']) ?>" loading="lazy">
                            <div class="berita-card__body">
                                <h3><?= esc($b['judul']) ?></h3>
                                <p><?= esc(mb_strimwidth($b['ringkas'], 0, 190, '…', 'UTF-8')) ?></p>
                                <?php if (!empty($b['tanggal'])): ?><time datetime="<?= esc($b['tanggal']) ?>"><?= esc(tanggal_id($b['tanggal'])) ?></time><?php endif; ?>
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
<?php layout_end(); ?>
