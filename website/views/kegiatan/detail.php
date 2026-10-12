<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman Detail Berita - SD Negeri Kedungsari 1
 * SATU FILE. Letakkan di: website/views/kegiatan/detail.php
 * Dibuka lewat: ?page=kegiatan&slug=<slug-berita>
 * (index.php meneruskan ke file ini jika ada parameter "slug")
 */

if (!function_exists('detail_url')) {
    function detail_url(string $slug): string {
        return page_url('berita') . '&slug=' . rawurlencode($slug);
    }
}

$current = 'berita';

// ===== Ambil data & cari berita berdasarkan slug =====
require_once __DIR__ . '/../../lib/api.php';
$beritaList = sikesa_berita(require __DIR__ . '/data.php');
usort($beritaList, fn($a, $b) => strcmp((string) $b['tanggal'], (string) $a['tanggal']));

$slug   = (string) ($_GET['slug'] ?? '');
$berita = null;
foreach ($beritaList as $item) {
    if ($item['slug'] === $slug) { $berita = $item; break; }
}

if (!$berita) {
    http_response_code(404);
}

// Paragraf isi (cadangan: ringkasan)
$paragraf = [];
if ($berita) {
    $paragraf = !empty($berita['isi']) ? $berita['isi'] : [$berita['ringkas']];
}

// Berita lainnya (maksimal 2, selain yang sedang dibaca)
$lainnya = array_slice(array_filter($beritaList, fn($b) => $b['slug'] !== $slug), 0, 2);

$title = ($berita ? $berita['judul'] : 'Berita tidak ditemukan') . ' - SD Negeri Kedungsari 1';

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'berita',
    'description' => $berita ? mb_strimwidth($berita['ringkas'], 0, 150, '…', 'UTF-8') : 'Berita SD Negeri Kedungsari 1',
    'css'         => 'kegiatan-detail',
    'js'          => null,
]);
?>
<main>
    <section class="section section--leaf" id="detail-berita">

    <?php if (!$berita): ?>

        <h1 class="title">Berita tidak ditemukan</h1>
        <div class="kosong">
            <p>Berita yang Anda cari tidak tersedia atau sudah dipindahkan.</p>
            <a class="pill pill--solid" href="<?= page_url('berita') ?>">Kembali ke Berita &amp; Artikel</a>
        </div>

    <?php else: ?>

        <div class="detail">
            <p class="detail__crumb">
                <a href="<?= page_url('berita') ?>">Berita &amp; Artikel</a> / Detail
            </p>

            <article class="detail__card">
                <img class="detail__img" src="<?= sikesa_img($berita['foto']) ?>" alt="<?= esc($berita['judul']) ?>">

                <div class="detail__body">
                    <h1><?= esc($berita['judul']) ?></h1>
                    <?php if (!empty($berita['tanggal'])): ?><time class="detail__date" datetime="<?= esc($berita['tanggal']) ?>"><?= esc(tanggal_id($berita['tanggal'])) ?></time><?php endif; ?>

                    <?php foreach ($paragraf as $p): ?>
                        <p><?= esc($p) ?></p>
                    <?php endforeach; ?>

                    <?php if (!empty($berita['galeri'])): ?>
                        <div class="detail__galeri">
                            <?php foreach ($berita['galeri'] as $f): ?>
                                <img src="<?= esc(sikesa_img($f)) ?>" alt="Dokumentasi <?= esc($berita['judul']) ?>" loading="lazy">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="detail__actions">
                        <a class="pill" href="<?= page_url('berita') ?>">Kembali ke Berita</a>
                    </div>
                </div>
            </article>
        </div>

        <?php if ($lainnya): ?>
            <div class="lain">
                <h2 class="title title--sm">Berita Lainnya</h2>
                <div class="lain__grid">
                    <?php foreach ($lainnya as $b): ?>
                        <a class="lain-card" href="<?= esc(detail_url($b['slug'])) ?>">
                            <img src="<?= sikesa_img($b['foto']) ?>" alt="<?= esc($b['judul']) ?>" loading="lazy">
                            <div>
                                <h3><?= esc(mb_strimwidth($b['judul'], 0, 90, '…', 'UTF-8')) ?></h3>
                                <?php if (!empty($b['tanggal'])): ?><time datetime="<?= esc($b['tanggal']) ?>"><?= esc(tanggal_id($b['tanggal'])) ?></time><?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    </section>
</main>
<?php layout_end(); ?>
