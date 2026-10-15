<?php
/** Footer bersama. Butuh: page_url(), base_url(), esc(). Data kontak dari partials/site-info.php. */
$info = require __DIR__ . '/site-info.php';
?>
<footer class="footer">
    <div class="footer__inner">
        <div class="footer__brand">
            <div class="footer__logo">
                <img src="<?= asset('icon/logo_sekolah.png') ?>" alt="Logo">
                <strong>SD NEGERI KEDUNGSARI 1</strong>
            </div>
            <p>Situs resmi SD Negeri Kedungsari 1 untuk informasi akademik dan kegiatan sekolah.</p>
        </div>

        <div class="footer__col">
            <h4>Informasi Sekolah</h4>
            <ul>
                <li><a href="<?= page_url('profil') ?>#visi-misi">Visi &amp; Misi</a></li>
                <li><a href="<?= page_url('ppdb') ?>">Pendaftaran Siswa Baru</a></li>
                <li><a href="<?= page_url('berita') ?>">Artikel &amp; Berita Terkini</a></li>
                <li><a href="<?= page_url('program') ?>">Program Pembiasaan</a></li>
                <li><a href="<?= page_url('ekstra') ?>">Ekstrakurikuler</a></li>
                <li><a href="<?= page_url('mading') ?>">Mading Karya Siswa</a></li>
                <li><a href="<?= page_url('profil') ?>">Guru &amp; Karyawan</a></li>
                <li><a href="<?= page_url('kontak') ?>">Hubungi Kami</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h4>Sosial media</h4>
            <ul>
                <li><a href="<?= esc($info['instagram']) ?>" target="_blank" rel="noopener">Instagram</a></li>
                <li><a href="<?= esc($info['facebook']) ?>" target="_blank" rel="noopener">Facebook</a></li>
            </ul>
        </div>

        <div class="footer__col footer__contact">
            <h4>Kontak &amp; Lokasi</h4>
            <ul>
                <li><?= esc($info['alamat']) ?></li>
                <li><?= esc($info['telepon']) ?></li>
                <li><?= esc($info['email']) ?></li>
            </ul>
            <iframe class="footer__map" loading="lazy" title="Lokasi sekolah" src="<?= esc($info['peta']) ?>"></iframe>
        </div>
    </div>
    <div class="footer__copy">&copy; <?= date('Y') ?> SD Negeri Kedungsari 1. Semua hak dilindungi.</div>
</footer>
