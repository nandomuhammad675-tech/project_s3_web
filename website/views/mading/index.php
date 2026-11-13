<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_once __DIR__ . '/../../lib/api.php';
/**
 * Halaman Mading (Karya Siswa) - dibuka lewat '?page=mading[&hal=2]'
 * Data: GET /api/public/mading (hanya karya status_tampil = 1). Tiga keadaan: ada / kosong / gagal memuat.
 * Desain kartu, saringan kategori, dan paginasi sama dengan Kegiatan & Prestasi (kartu .kartu--post di style.css).
 */
$perHalaman = 9;
$hal      = max(1, (int) ($_GET['hal'] ?? 1));
$kategori = trim((string) ($_GET['kategori'] ?? ''));
$mading   = sikesa_mading($hal, $perHalaman, $kategori);          // null = API tidak terjangkau
$items    = $mading['items'] ?? [];
$totalHal = max(1, (int) ceil((int) ($mading['total'] ?? 0) / $perHalaman));
if ($mading !== null && $hal > $totalHal) { $hal = $totalHal; $mading = sikesa_mading($hal, $perHalaman, $kategori); $items = $mading['items'] ?? []; }
$kategoriAll = $mading['kategori_tersedia'] ?? [];               // kategori tersedia dari database (Puisi, Gambar, Poster, dst.)
if ($kategori !== '' && !in_array($kategori, $kategoriAll, true) && $mading !== null && !$items) { $kategori = ''; $mading = sikesa_mading($hal, $perHalaman); $items = $mading['items'] ?? []; $totalHal = max(1, (int) ceil((int) ($mading['total'] ?? 0) / $perHalaman)); }
$urlHal   = static fn(int $h): string => page_url('mading', array_filter(['kategori' => $kategori, 'hal' => $h > 1 ? $h : null]));

layout_start([
    'title'       => 'Mading Karya Siswa - SD Negeri Kedungsari 1',
    'current'     => 'mading',
    'description' => 'Mading karya siswa SD Negeri Kedungsari 1 - gambar, tulisan, dan kreasi siswa',
]);
?>
<main>
    <?php ui_hero('Mading', 'Karya siswa-siswi yang membanggakan<br>SD NEGERI KEDUNGSARI 1'); ?>

    <section class="section section--leaf" id="mading">
        <h2 class="title">Karya Siswa</h2>

        <?php if ($kategoriAll): ?>
        <nav class="filter" aria-label="Saring menurut kategori">
            <a class="pill<?= $kategori === '' ? ' pill--solid' : '' ?>" href="<?= esc(page_url('mading', [], 'mading')) ?>"<?= $kategori === '' ? ' aria-current="true"' : '' ?>>Semua</a>
            <?php foreach ($kategoriAll as $k): ?>
                <a class="pill<?= $kategori === $k ? ' pill--solid' : '' ?>" href="<?= esc(page_url('mading', ['kategori' => $k], 'mading')) ?>"<?= $kategori === $k ? ' aria-current="true"' : '' ?>><?= esc($k) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <?php if ($mading === null): ui_gagal('Data mading', $urlHal($hal) . '#mading');
        elseif (!$items): ui_kosong($kategori !== '' ? 'Belum ada karya pada kategori ' . $kategori . '.' : 'Belum ada karya yang dipublikasikan.');
        else: ?>
            <div class="grid grid--3 grid--center">
                <?php foreach ($items as $k): ?>
                    <article class="kartu kartu--post">
                        <img class="kartu__img" src="<?= esc(sikesa_img($k['file_gambar'] ?? '')) ?>" alt="<?= esc($k['judul_karya']) ?>" loading="lazy" width="600" height="400">
                        <div class="kartu__body">
                            <?php if (!empty($k['kategori'])): ?><span class="badge"><?= esc($k['kategori']) ?></span><?php endif; ?>
                            <h3><?= esc($k['judul_karya']) ?></h3>
                            <?php if (!empty($k['nama_penulis'])): ?><p class="kartu__meta">Oleh <?= esc($k['nama_penulis']) ?></p><?php endif; ?>
                            <?php if (!empty($k['deskripsi'])): ?><p><?= esc($k['deskripsi']) ?></p><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php ui_pager($hal, $totalHal, $urlHal, 'Halaman mading', 'mading'); ?>
        <?php endif; ?>
    </section>
</main>
<?php layout_end(); ?>
