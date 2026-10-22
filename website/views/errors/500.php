<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
layout_start([
    'title'   => 'Terjadi gangguan - SD Negeri Kedungsari 1',
    'current' => '',
    'css'     => 'program',
]);
?>
<main>
    <section class="hero page-hero" style="--bg:url('<?= asset('img/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>500</h1>
            <p>Maaf, ada kendala di sisi kami<br>SD NEGERI KEDUNGSARI 1</p>
        </div>
    </section>

    <section class="section section--leaf">
        <h2 class="title">Terjadi gangguan</h2>
        <p class="status">Halaman belum dapat ditampilkan. Silakan coba lagi beberapa saat lagi.</p>
        <p style="margin-top:20px"><a class="pill pill--solid" href="<?= esc(page_url('home')) ?>">Kembali ke Beranda</a></p>
    </section>
</main>
<?php layout_end(); ?>
