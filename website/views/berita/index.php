<?php
/**
 * Kegiatan & Prestasi. Daftar: /?page=berita[&kategori=prestasi][&hal=2]. Detail: /?page=berita&slug=e12
 * Data: GET /api/public/event (hanya status_tampil = 1, terbaru di atas; urutan & halaman dari server).
 */
if (isset($_GET['slug'])) { require __DIR__ . '/detail.php'; return; }

$kategoriAll = sikesa_kategori();
$kategori    = (string) ($_GET['kategori'] ?? '');
if (!isset($kategoriAll[$kategori])) { $kategori = ''; }
$perHalaman  = 6;
$hal         = max(1, (int) ($_GET['hal'] ?? 1));

$data  = sikesa_event($kategori ?: null, $hal, $perHalaman);       // null = API gagal
$items = $data['items'] ?? [];
$total = (int) ($data['total'] ?? 0);
$totalHal = max(1, (int) ceil($total / $perHalaman));
if ($data !== null && $hal > $totalHal) { $hal = $totalHal; $data = sikesa_event($kategori ?: null, $hal, $perHalaman); $items = $data['items'] ?? []; }

$urlHal = static fn(int $h): string => page_url('berita', array_filter(['kategori' => $kategori, 'hal' => $h > 1 ? $h : null]));

layout_start([
    'title'       => ($kategori ? $kategoriAll[$kategori] . ' - ' : '') . 'Kegiatan & Prestasi - SD Negeri Kedungsari 1',
    'current'     => 'berita',
    'description' => 'Kegiatan, event, prestasi, dan kolaborasi terbaru SD Negeri Kedungsari 1',
    'css'         => 'berita',
]);
?>
<main>
    <?php ui_hero('Kegiatan & Prestasi', 'Informasi terbaru dan kabar terkini seputar kegiatan<br>SD NEGERI KEDUNGSARI 1'); ?>

    <section class="section section--leaf" id="berita">
        <h2 class="title">Kegiatan &amp; Prestasi</h2>

        <nav class="filter" aria-label="Saring menurut kategori">
            <a class="pill<?= $kategori === '' ? ' pill--solid' : '' ?>" href="<?= esc(page_url('berita', [], 'berita')) ?>"<?= $kategori === '' ? ' aria-current="true"' : '' ?>>Semua</a>
            <?php foreach ($kategoriAll as $k => $label): ?>
                <a class="pill<?= $kategori === $k ? ' pill--solid' : '' ?>" href="<?= esc(page_url('berita', ['kategori' => $k], 'berita')) ?>"<?= $kategori === $k ? ' aria-current="true"' : '' ?>><?= esc($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($data === null): ui_gagal('Daftar kegiatan', $urlHal($hal) . '#berita');
        elseif (!$items): ui_kosong($kategori ? 'Belum ada ' . strtolower($kategoriAll[$kategori]) . ' yang dipublikasikan.' : 'Belum ada kegiatan yang dipublikasikan.');
        else: ?>
            <div class="grid grid--2">
                <?php foreach ($items as $b): ?>
                    <article class="kartu berita-card">
                        <a class="berita-card__link" href="<?= esc(page_url('berita', ['slug' => $b['slug']])) ?>">
                            <img class="berita-card__img" src="<?= esc(sikesa_img($b['foto'])) ?>" alt="" loading="lazy" width="600" height="400">
                            <div class="berita-card__body">
                                <?php if ($b['kategori'] !== ''): ?><span class="badge"><?= esc($kategoriAll[$b['kategori']] ?? ucfirst($b['kategori'])) ?></span><?php endif; ?>
                                <h3><?= esc($b['judul']) ?></h3>
                                <?php if ($b['ringkas'] !== ''): ?><p><?= esc($b['ringkas']) ?></p><?php endif; ?>
                                <?php if ($b['tanggal'] !== ''): ?><time datetime="<?= esc($b['tanggal']) ?>"><?= esc(tanggal_id($b['tanggal'])) ?></time><?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php ui_pager($hal, $totalHal, $urlHal, 'Halaman kegiatan', 'berita'); ?>
        <?php endif; ?>
    </section>
</main>
<?php layout_end(); ?>
