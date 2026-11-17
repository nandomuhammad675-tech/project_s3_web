<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_once __DIR__ . '/../../lib/api.php';
/**
 * Detail Ekstrakurikuler: ?page=ekstrakurikuler&slug=<slug> (diteruskan oleh index.php).
 * Desain sama dengan detail Kegiatan & Prestasi (css/pages/detail.css).
 */
$semua = sikesa_ekskul(require __DIR__ . '/data.php');
$slug  = (string) ($_GET['slug'] ?? '');
$e     = null;
foreach ($semua as $item) {
    if ($item['slug'] === $slug) { $e = $item; break; }
}
if (!$e) { http_response_code(404); }

$nama  = $e['nama'] ?? '';
$lain  = $e ? array_slice(array_values(array_filter($semua, fn($x) => $x['slug'] !== $slug)), 0, 2) : [];

layout_start([
    'title'       => ($e ? 'Ekstrakurikuler ' . $nama : 'Ekstrakurikuler tidak ditemukan') . ' - SD Negeri Kedungsari 1',
    'current'     => 'ekstra',
    'description' => $e && !empty($e['tentang']) ? mb_strimwidth($e['tentang'], 0, 150, '…', 'UTF-8') : 'Ekstrakurikuler SD Negeri Kedungsari 1',
    'css'         => 'detail',
]);
?>
<main>
    <section class="section section--leaf" id="detail">
    <?php if (!$e): ?>
        <h1 class="title">Ekstrakurikuler tidak ditemukan</h1>
        <div class="kosong">
            <p>Ekstrakurikuler yang Anda cari tidak tersedia atau sudah dipindahkan.</p>
            <a class="pill pill--solid" href="<?= esc(page_url('ekstra')) ?>">Kembali ke Ekstrakurikuler</a>
        </div>
    <?php else: ?>
        <div class="detail">
            <p class="detail__crumb"><a href="<?= esc(page_url('ekstra')) ?>">Ekstrakurikuler</a> / Detail</p>
            <article class="detail__card">
                <img class="detail__img" src="<?= esc(sikesa_img($e['foto'] ?? '')) ?>" alt="Ekstrakurikuler <?= esc($nama) ?>">
                <div class="detail__body">
                    <span class="badge">Ekstrakurikuler</span>
                    <h1><?= esc($nama) ?></h1>

                    <h2 class="detail__sub">Tentang Ekstrakurikuler <?= esc($nama) ?></h2>
                    <p><?= !empty($e['tentang']) ? esc($e['tentang']) : 'Informasi lengkap belum tersedia. Silakan hubungi sekolah.' ?></p>

                    <?php if (!empty($e['kegiatan'])): ?>
                        <h2 class="detail__sub">Kegiatan yang Dilakukan</h2>
                        <ul class="detail__list"><?php foreach ($e['kegiatan'] as $k): ?><li><?= esc($k) ?></li><?php endforeach; ?></ul>
                    <?php endif; ?>

                    <?php if (!empty($e['materi'])): ?>
                        <h2 class="detail__sub"><?= esc($e['materi_judul'] ?? 'Materi yang Dipelajari') ?></h2>
                        <?php foreach ($e['materi'] as $m): ?>
                            <div class="detail__materi"><strong><?= esc($m['nama']) ?></strong><p><?= esc($m['deskripsi']) ?></p></div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($e['manfaat'])): ?>
                        <h2 class="detail__sub">Manfaat Mengikuti <?= esc($nama) ?></h2>
                        <ul class="detail__list"><?php foreach ($e['manfaat'] as $m): ?><li><?= esc($m) ?></li><?php endforeach; ?></ul>
                    <?php endif; ?>

                    <div class="detail__actions"><a class="pill" href="<?= esc(page_url('ekstra')) ?>">Kembali ke Ekstrakurikuler</a></div>
                </div>
            </article>
        </div>

        <?php if ($lain): ?>
            <div class="lain">
                <h2 class="title title--sm">Ekstrakurikuler Lainnya</h2>
                <div class="lain__grid">
                    <?php foreach ($lain as $x): ?>
                        <a class="kartu lain-card" href="<?= esc(page_url('ekstra', ['slug' => $x['slug']])) ?>">
                            <img src="<?= esc(sikesa_img($x['foto'] ?? '')) ?>" alt="" loading="lazy" width="600" height="400">
                            <div><h3><?= esc($x['nama']) ?></h3></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    </section>
</main>
<?php layout_end(); ?>
