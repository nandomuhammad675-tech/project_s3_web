<?php
/** Navbar bersama (dipanggil dari layouts/main.php). Butuh: $current (kunci menu aktif), page_url(), asset(). */
$menu = [
    'home'    => 'Home',
    'profil'  => 'Profil',
    'berita'  => 'Berita',
    'program' => 'Program',
    'ekstra'  => 'Ekstra',
    'galeri'  => 'Galeri',
    'mading'  => 'Mading',
    'kontak'  => 'Hubungi Kami',
];
?>
<header class="site-header" id="navbar">
    <nav class="navbar" aria-label="Menu utama">
        <a href="<?= esc(page_url('home')) ?>" class="navbar__brand">
            <img src="<?= asset('icon/logo_sekolah.png') ?>" alt="" width="42" height="42">
            <span>SD NEGERI KEDUNGSARI 1</span>
        </a>

        <button class="navbar__toggle" id="navToggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="navMenu">
            <span></span><span></span><span></span>
        </button>

        <ul class="navbar__menu" id="navMenu">
            <?php foreach ($menu as $key => $label): ?>
                <li><a href="<?= esc(page_url($key)) ?>"<?= $current === $key ? ' class="is-active" aria-current="page"' : '' ?>><?= esc($label) ?></a></li>
            <?php endforeach; ?>
            <li class="navbar__cta"><a href="<?= esc(page_url('ppdb')) ?>" class="btn-ppdb<?= $current === 'ppdb' ? ' is-active' : '' ?>"<?= $current === 'ppdb' ? ' aria-current="page"' : '' ?>>Daftar PPDB</a></li>
        </ul>
    </nav>
</header>
