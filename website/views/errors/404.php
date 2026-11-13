<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
layout_start([
    'title'   => 'Halaman tidak ditemukan - SD Negeri Kedungsari 1',
    'current' => '',
    'css'     => 'program',
]);
?>
<main>
    <section class="hero page-hero" style="--bg:url('<?= asset('img/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>404</h1>
            <p>Halaman yang Anda cari tidak ada<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <section class="section section--leaf">
        <h2 class="title">Halaman tidak ditemukan</h2>
        <p class="status">Alamat yang dibuka tidak tersedia atau sudah dipindahkan. Gunakan menu di atas untuk melanjutkan.</p>
        <p style="margin-top:20px"><a class="pill pill--solid" href="<?= esc(page_url('home')) ?>">Kembali ke Beranda</a></p>
    </section>
</main>
<?php layout_end(); ?>
