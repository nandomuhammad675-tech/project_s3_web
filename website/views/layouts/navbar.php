<?php
// Tentukan menu aktif dari URL
$uri = service('uri');
$seg = $uri->getTotalSegments() ? $uri->getSegment(1) : 'home';

$menu = [
    'home'    => 'Home',
    'profil'  => 'Profil',
    'berita'  => 'Berita',
    'ekstra'  => 'Ekstra',
    'galeri'  => 'Galeri',
    'kontak'  => 'Hubungi kami',
];
?>
<nav class="navbar" id="navbar">
    <a href="<?= base_url('/') ?>" class="navbar__brand">
        <img src="<?= base_url('icon/logo_sekolah.png') ?>" alt="Logo SD Negeri Kedungsari 1">
        <span>SD NEGERI KEDUNGSARI 1</span>
    </a>

    <button class="navbar__toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>

    <ul class="navbar__menu" id="navMenu">
        <?php foreach ($menu as $slug => $label): ?>
            <li>
                <a href="<?= base_url($slug === 'home' ? '/' : $slug) ?>"
                   class="<?= $seg === $slug ? 'is-active' : '' ?>"><?= $label ?></a>
            </li>
        <?php endforeach; ?>
        <li><a href="<?= base_url('ppdb') ?>" class="btn-ppdb">Daftar PPDB</a></li>
    </ul>
</nav>