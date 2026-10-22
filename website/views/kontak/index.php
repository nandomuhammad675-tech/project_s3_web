<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Hubungi Kami - dibuka lewat '?page=kontak'
 * Kontak ditulis tetap di views/partials/site-info.php (BUKAN dari database/API) dan tidak ada formulir kirim pesan.
 * Baris yang nilainya kosong disembunyikan, KECUALI media sosial: Instagram dan Facebook selalu tampil.
 */
$k = site_info();
$telHref = $k['telepon'] !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $k['telepon']) : '';
$wa = preg_replace('/\D/', '', $k['whatsapp']);
if ($wa !== '' && $wa[0] === '0') { $wa = '62' . substr($wa, 1); }
$waHref = $wa !== '' ? 'https://wa.me/' . $wa : '';
$sosmed = [
    ['Instagram', $k['instagram'] ?: '#', 'sosmed__ig', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.3" cy="6.7" r="1" fill="currentColor" stroke="none"/></svg>'],
    ['Facebook',  $k['facebook'] ?: '#',  'sosmed__fb', '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg>'],
];

layout_start([
    'title'       => 'Hubungi Kami - SD Negeri Kedungsari 1',
    'current'     => 'kontak',
    'description' => 'Hubungi SD Negeri Kedungsari 1 - alamat, telepon, email, media sosial, jam layanan, dan lokasi sekolah',
    'css'         => 'kontak',
]);
?>
<main>
    <?php ui_hero('Hubungi Kami', 'Kami siap membantu informasi sekolah dan pendaftaran'); ?>
    <section class="section section--leaf" id="kontak">
        <h2 class="title">Informasi Kontak</h2>
        <div class="kontak">
            <ul class="info-list">
                <?php if ($k['alamat'] !== ''): ?>
                <li class="info-item"><span class="info-item__label">Alamat</span><span><?= esc($k['alamat']) ?></span></li>
                <?php endif; if ($k['telepon'] !== ''): ?>
                <li class="info-item"><span class="info-item__label">Telepon</span><a href="<?= esc($telHref) ?>"><?= esc($k['telepon']) ?></a></li>
                <?php endif; if ($k['whatsapp'] !== ''): ?>
                <li class="info-item"><span class="info-item__label">WhatsApp</span><a href="<?= esc($waHref) ?>" target="_blank" rel="noopener noreferrer"><?= esc($k['whatsapp']) ?></a></li>
                <?php endif; if ($k['email'] !== ''): ?>
                <li class="info-item"><span class="info-item__label">Email</span><a href="mailto:<?= esc($k['email']) ?>"><?= esc($k['email']) ?></a></li>
                <?php endif; if (!empty($k['jam'])): ?>
                <li class="info-item"><span class="info-item__label">Jam Layanan</span><span><?= esc($k['jam']) ?></span></li>
                <?php endif; ?>
                <li class="info-item">
                    <span class="info-item__label">Media Sosial</span>
                    <span class="sosmed">
                        <?php foreach ($sosmed as [$nama, $url, $kelas, $ikon]): ?>
                            <a class="<?= $kelas ?>" href="<?= esc($url) ?>" target="_blank" rel="noopener noreferrer"><?= $ikon ?><span><?= esc($nama) ?></span></a>
                        <?php endforeach; ?>
                    </span>
                </li>
            </ul>

            <div class="aksi">
                <a class="pill" href="<?= esc($k['peta_link']) ?>" target="_blank" rel="noopener noreferrer">Buka di Google Maps</a>
            </div>

            <div class="peta">
                <iframe loading="lazy" title="Lokasi SD Negeri Kedungsari 1" src="<?= esc($k['peta']) ?>" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>
</main>
<?php layout_end(); ?>
