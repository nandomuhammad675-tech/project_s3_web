<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
require_once __DIR__ . '/../../lib/api.php';
/**
 * Halaman Program Pembiasaan - dibuka lewat '?page=program-pembiasaan'
 * Data: GET /api/public/program-pembiasaan (enam program, urut Senin-Sabtu).
 */
$program = sikesa_program();       // null = API tidak terjangkau
$HARI = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => "Jum'at", 'sabtu' => 'Sabtu'];

layout_start([
    'title'       => 'Program Pembiasaan - SD Negeri Kedungsari 1',
    'current'     => 'program',
    'description' => 'Program pembiasaan harian SD Negeri Kedungsari 1, Senin sampai Sabtu',
    'css'         => 'program',
]);
?>
<main>
    <section class="hero page-hero" style="--bg:url('<?= asset('gambar/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>PROGRAM PEMBIASAAN</h1>
            <p>Membentuk karakter lewat kebiasaan baik setiap hari<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <section class="section section--leaf" id="program">
        <h2 class="title">Enam Hari, Enam Kebiasaan</h2>

        <?php if ($program === null): ?>
            <div class="status status--gagal" role="alert">
                <p>Program pembiasaan gagal dimuat. Periksa koneksi internet lalu coba lagi.</p>
                <a class="pill pill--solid" href="<?= page_url('program') ?>">Coba Lagi</a>
            </div>
        <?php elseif (!$program): ?>
            <p class="status">Belum ada data program pembiasaan.</p>
        <?php else: ?>
            <div class="program-grid">
                <?php foreach ($program as $p): ?>
                    <article class="program-card">
                        <span class="program-card__hari"><?= esc($HARI[$p['hari']] ?? ucfirst((string) $p['hari'])) ?></span>
                        <?php if (!empty($p['ikon'])): ?>
                            <img class="program-card__ikon" src="<?= esc(sikesa_img($p['ikon'])) ?>" alt="" loading="lazy">
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
