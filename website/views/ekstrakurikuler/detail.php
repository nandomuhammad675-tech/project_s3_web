<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman Detail Ekstrakurikuler - SD Negeri Kedungsari 1
 * Letakkan di: website/views/ekstrakurikuler/detail.php
 * Dibuka lewat: ?page=ekstrakurikuler&slug=<slug>  (diteruskan oleh index.php)
 */

$current = 'ekstra';

// ===== Cari ekstrakurikuler berdasarkan slug =====
require_once __DIR__ . '/../../lib/api.php';
$ekstraList = sikesa_ekskul(require __DIR__ . '/data.php');
$slug  = (string) ($_GET['slug'] ?? '');
$ekstra = null;
foreach ($ekstraList as $item) {
    if ($item['slug'] === $slug) { $ekstra = $item; break; }
}
if (!$ekstra) {
    http_response_code(404);
}

$nama  = $ekstra['nama'] ?? '';
$title = ($ekstra ? 'Ekstrakurikuler ' . $nama : 'Ekstrakurikuler tidak ditemukan') . ' - SD Negeri Kedungsari 1';
$heroFoto = $ekstra ? $ekstra['foto'] : 'assets/home.jpg';

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'ekstra',
    'description' => $ekstra ? mb_strimwidth($ekstra['tentang'], 0, 150, '…', 'UTF-8') : 'Ekstrakurikuler SD Negeri Kedungsari 1',
    'css'         => 'ekstrakurikuler-detail',
    'js'          => null,
]);
?>
<main>

<?php if (!$ekstra): ?>

    <section class="section section--leaf">
        <h1 class="title">Ekstrakurikuler tidak ditemukan</h1>
        <div class="kosong">
            <p>Ekstrakurikuler yang Anda cari tidak tersedia atau sudah dipindahkan.</p>
            <a class="pill pill--solid" href="<?= page_url('ekstra') ?>">Kembali ke Ekstrakurikuler</a>
        </div>
    </section>

<?php else: ?>

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= sikesa_img($heroFoto) ?>')">
        <div class="page-hero__content">
            <h1><?= esc($nama) ?></h1>
            <p>Mengembangkan minat, bakat, dan potensi siswa<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <!-- ================= DETAIL ================= -->
    <section class="section section--leaf" id="detail">
        <h2 class="title"><?= esc($nama) ?></h2>

        <article class="detail-card">
            <h2>Tentang Ekstrakurikuler <?= esc($nama) ?></h2>
            <p><?= esc($ekstra['tentang']) ?></p>

            <?php if (!empty($ekstra['kegiatan'])): ?>
                <h2>Kegiatan yang Dilakukan</h2>
                <ul>
                    <?php foreach ($ekstra['kegiatan'] as $k): ?>
                        <li><?= esc($k) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($ekstra['materi'])): ?>
                <h2><?= esc($ekstra['materi_judul'] ?? 'Materi yang Dipelajari') ?></h2>
                <?php foreach ($ekstra['materi'] as $m): ?>
                    <div class="materi">
                        <strong><?= esc($m['nama']) ?></strong>
                        <p><?= esc($m['deskripsi']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($ekstra['manfaat'])): ?>
                <h2>Manfaat Mengikuti <?= esc($nama) ?></h2>
                <ul>
                    <?php foreach ($ekstra['manfaat'] as $m): ?>
                        <li><?= esc($m) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="detail__actions">
                <a class="pill" href="<?= page_url('ekstra') ?>">Kembali ke Ekstrakurikuler</a>
            </div>
        </article>
    </section>

<?php endif; ?>

</main>
<?php layout_end(); ?>
