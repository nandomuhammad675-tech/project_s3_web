<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_once __DIR__ . '/../../lib/api.php';
/** Detail kegiatan: /?page=kegiatan&slug=e12. Event tanpa galeri/deskripsi tetap tampil rapi (tidak error). */
$slug  = (string) $_GET['slug'];
$b     = sikesa_event_cari($slug);                  // array | false (tidak ada) | null (API gagal)
$kat   = sikesa_kategori();
$lain  = [];
if (is_array($b)) {
    $h = sikesa_event(null, 1, 4);
    foreach (($h['items'] ?? []) as $e) { if ($e['slug'] !== $b['slug'] && count($lain) < 2) { $lain[] = $e; } }
}
if ($b === false) { http_response_code(404); }
elseif ($b === null) { http_response_code(503); }

layout_start([
    'title'       => (is_array($b) ? $b['judul'] : 'Kegiatan tidak ditemukan') . ' - SD Negeri Kedungsari 1',
    'current'     => 'berita',
    'description' => is_array($b) && $b['ringkas'] !== '' ? $b['ringkas'] : 'Kegiatan SD Negeri Kedungsari 1',
    'css'         => 'detail',
    'js'          => is_array($b) && $b['galeri'] ? 'galeri' : [],
    'image'       => is_array($b) ? sikesa_img($b['foto']) : null,
]);
?>
<main>
    <section class="section section--leaf" id="detail-berita">
    <?php if ($b === null): ?>
        <h1 class="title">Kegiatan gagal dimuat</h1>
        <?php ui_gagal('Kegiatan', page_url('berita', ['slug' => $slug])); ?>
    <?php elseif ($b === false): ?>
        <h1 class="title">Kegiatan tidak ditemukan</h1>
        <div class="kosong">
            <p>Kegiatan yang Anda cari tidak tersedia atau sudah dipindahkan.</p>
            <a class="pill pill--solid" href="<?= esc(page_url('berita')) ?>">Kembali ke Kegiatan &amp; Prestasi</a>
        </div>
    <?php else: ?>
        <div class="detail">
            <p class="detail__crumb"><a href="<?= esc(page_url('berita')) ?>">Kegiatan &amp; Prestasi</a> / Detail</p>
            <article class="detail__card">
                <img class="detail__img" src="<?= esc(sikesa_img($b['foto'])) ?>" alt="<?= esc($b['judul']) ?>">
                <div class="detail__body">
                    <?php if ($b['kategori'] !== ''): ?><span class="badge"><?= esc($kat[$b['kategori']] ?? ucfirst($b['kategori'])) ?></span><?php endif; ?>
                    <h1><?= esc($b['judul']) ?></h1>
                    <?php if ($b['tanggal'] !== ''): ?><time class="detail__date" datetime="<?= esc($b['tanggal']) ?>"><?= esc(tanggal_id($b['tanggal'])) ?></time><?php endif; ?>

                    <?php foreach ($b['isi'] as $p): ?><p><?= esc($p) ?></p><?php endforeach; ?>

                    <?php if ($b['galeri']): ?>
                        <h2 class="detail__sub">Dokumentasi</h2>
                        <div class="detail__galeri">
                            <?php foreach ($b['galeri'] as $f): $u = sikesa_img($f); ?>
                                <a class="zoom" href="<?= esc($u) ?>" data-caption="<?= esc($b['judul']) ?>">
                                    <img src="<?= esc($u) ?>" alt="Dokumentasi <?= esc($b['judul']) ?>" loading="lazy" width="320" height="240">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="detail__actions"><a class="pill" href="<?= esc(page_url('berita')) ?>">Kembali ke Kegiatan &amp; Prestasi</a></div>
                </div>
            </article>
        </div>

        <?php if ($lain): ?>
            <div class="lain">
                <h2 class="title title--sm">Kegiatan Lainnya</h2>
                <div class="lain__grid">
                    <?php foreach ($lain as $e): ?>
                        <a class="kartu lain-card" href="<?= esc(page_url('berita', ['slug' => $e['slug']])) ?>">
                            <img src="<?= esc(sikesa_img($e['foto'])) ?>" alt="" loading="lazy" width="600" height="400">
                            <div>
                                <h3><?= esc(mb_strimwidth($e['judul'], 0, 90, '…', 'UTF-8')) ?></h3>
                                <?php if ($e['tanggal'] !== ''): ?><time datetime="<?= esc($e['tanggal']) ?>"><?= esc(tanggal_id($e['tanggal'])) ?></time><?php endif; ?>
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
