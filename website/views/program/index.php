<?php
/** Program Pembiasaan: enam kartu Senin-Sabtu. Data: GET /api/public/program-pembiasaan. */
$konten  = require __DIR__ . '/../../lib/konten.php';
$program = sikesa_program();                // null = API gagal
layout_start([
    'title'       => 'Program Pembiasaan - SD Negeri Kedungsari 1',
    'current'     => 'program',
    'description' => 'Program pembiasaan harian SD Negeri Kedungsari 1, Senin sampai Sabtu',
    'css'         => 'program',
]);
?>
<main>
    <?php ui_hero('Program Pembiasaan', 'Membentuk karakter lewat kebiasaan baik setiap hari<br>SD NEGERI KEDUNGSARI 1'); ?>
    <section class="section section--leaf" id="program">
        <h2 class="title">Enam Hari, Enam Kebiasaan</h2>
        <?php if ($program === null): ui_gagal('Program pembiasaan', page_url('program', [], 'program'));
        elseif (!$program): ui_kosong('Belum ada data program pembiasaan.');
        else: ?>
            <div class="program-grid">
                <?php foreach ($program as $p): ?>
                    <article class="program-card">
                        <span class="program-card__hari"><?= esc($konten['hari'][$p['hari']] ?? ucfirst((string) $p['hari'])) ?></span>
                        <?php if (!empty($p['ikon'])): ?>
                            <img class="program-card__ikon" src="<?= esc(sikesa_img($p['ikon'])) ?>" alt="" loading="lazy" width="48" height="48">
                        <?php endif; ?>
                        <h3><?= esc($p['nama_program']) ?></h3>
                        <?php if (!empty($p['deskripsi'])): ?><p><?= esc($p['deskripsi']) ?></p><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php layout_end(); ?>
