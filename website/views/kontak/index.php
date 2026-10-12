<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman Hubungi Kami - SD Negeri Kedungsari 1
 * SATU FILE. Letakkan di: website/views/kontak/index.php
 * Dibuka lewat '?page=kontak'
 */

$current = 'kontak';

// ===== DATA KONTAK: satu sumber (views/partials/site-info.php), sama dengan footer semua halaman =====
// Sesuai dokumen, kontak ditulis tetap di kode (bukan dari database / API) dan tidak ada formulir kirim pesan.
$kontak  = require __DIR__ . '/../partials/site-info.php';
$petaUrl = $kontak['peta'];

// Link bantu: nomor telepon & WhatsApp
$telHref = 'tel:' . preg_replace('/[^0-9+]/', '', $kontak['telepon']);
$waDigit = preg_replace('/\D/', '', $kontak['whatsapp']);
if (strpos($waDigit, '0') === 0) {
    $waDigit = '62' . substr($waDigit, 1);
}
$waHref = 'https://wa.me/' . $waDigit;

$title = 'Hubungi Kami - SD Negeri Kedungsari 1';

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'kontak',
    'description' => 'Hubungi SD Negeri Kedungsari 1 - alamat, telepon, email, WhatsApp, dan lokasi sekolah',
    'css'         => 'kontak',
    'js'          => null,
]);
?>
<main>
    <section class="section section--leaf kontak-section" id="kontak">
        <h1 class="title">Hubungi Kami</h1>

        <!-- ================= INFORMASI KONTAK ================= -->
        <div class="panel panel--info">
            <div class="panel__head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 6.5C10.2 5.2 7.6 4.5 3 4.5v13c4.6 0 7.2.7 9 2 1.8-1.3 4.4-2 9-2v-13c-4.6 0-7.2.7-9 2z"/>
                    <path d="M12 6.5v13"/>
                </svg>
                <h2>Informasi Kontak</h2>
            </div>

            <ul class="info-list">
                <li class="info-item">
                    <span class="info-item__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/>
                            <circle cx="12" cy="9.5" r="2.6"/>
                        </svg>
                    </span>
                    <span class="info-item__text"><?= esc($kontak['alamat']) ?></span>
                </li>

                <li class="info-item">
                    <span class="info-item__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 3.5h3.2l1.6 4.2-2 1.3a11 11 0 0 0 6.2 6.2l1.3-2 4.2 1.6V18a2.5 2.5 0 0 1-2.7 2.5C9.9 20 4 14.1 3.5 6.2A2.5 2.5 0 0 1 5 3.5z"/>
                        </svg>
                    </span>
                    <a class="info-item__text" href="<?= esc($telHref) ?>"><?= esc($kontak['telepon']) ?></a>
                </li>

                <li class="info-item">
                    <span class="info-item__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m3.5 7 8.5 6 8.5-6"/>
                        </svg>
                    </span>
                    <a class="info-item__text" href="mailto:<?= esc($kontak['email']) ?>"><?= esc($kontak['email']) ?></a>
                </li>

                <li class="info-item">
                    <span class="info-item__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3.5 20.5 5 15.8A8.5 8.5 0 1 1 8.4 19z"/>
                            <path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1.2-1.4-2.2-1.1-1 .8a4.6 4.6 0 0 1-2-2l.8-1-1.1-2.2z"/>
                        </svg>
                    </span>
                    <a class="info-item__text" href="<?= esc($waHref) ?>" target="_blank" rel="noopener"><?= esc($kontak['whatsapp']) ?></a>
                </li>
            </ul>
        </div>

        <!-- ================= PETA & MEDIA SOSIAL ================= -->
        <div class="kontak-row">
            <div class="panel panel--map">
                <iframe loading="lazy" title="Lokasi SD Negeri Kedungsari 1"
                        src="<?= esc($petaUrl) ?>" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

            <aside class="panel panel--sosmed">
                <div class="panel__head"><h2>Hubungi Kami</h2></div>

                <div class="sosmed-list">
                    <a class="sosmed-item" href="<?= esc($kontak['instagram']) ?>" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5"/>
                            <circle cx="12" cy="12" r="4.2"/>
                            <circle cx="17.3" cy="6.7" r="1" fill="currentColor" stroke="none"/>
                        </svg>
                        <span>Instagram</span>
                    </a>

                    <a class="sosmed-item" href="<?= esc($kontak['facebook']) ?>" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14.5 21v-8h2.7l.5-3.2h-3.2V7.9c0-.9.4-1.7 1.8-1.7h1.5V3.4c-.3 0-1.3-.2-2.4-.2-2.5 0-4 1.5-4 4.1v2.5H8.3V13H11v8"/>
                        </svg>
                        <span>Facebook</span>
                    </a>
                </div>
            </aside>
        </div>
    </section>
</main>
<?php layout_end(); ?>
