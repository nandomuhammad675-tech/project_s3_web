<?php
require_once __DIR__ . '/../../lib/bootstrap.php';
/**
 * Halaman PPDB - SD Negeri Kedungsari 1
 * SATU FILE. Letakkan di: website/views/ppdb/index.php
 * Dibuka lewat '?page=ppdb'
 * Formulir pendaftaran = Google Form dari pengaturan_sistem.ppdb_form_url (bagian #daftar di halaman ini)
 */

// Alamat lama '?page=ppdb&step=formulir' dialihkan ke bagian formulir pada halaman ini.
if (($_GET['step'] ?? '') === 'formulir') {
    header('Location: ' . page_url('ppdb') . '#daftar', true, 301);
    exit;
}

require_once __DIR__ . '/../../lib/api.php';

$current = 'ppdb';

// Formulir PPDB = Google Form milik sekolah. Alamatnya dibaca dari database (pengaturan_sistem.ppdb_form_url)
// lewat GET /api/public/pengaturan, diubah Admin dari PWA Admin. SI-KESA tidak menerima/menyimpan jawaban pendaftar.
$ppdb        = sikesa_ppdb();                 // status: ada | kosong | gagal
$formulirUrl = '#daftar';

// ===== DATA (ubah di sini) =====

// Kartu "Informasi" (ikon: kalender, papan, dokumen, centang) - warna selang-seling
$informasi = [
    ['ikon' => 'kalender', 'warna' => 'green', 'judul' => 'Jadwal',         'isi' => 'Informasi jadwal pembukaan dan penutupan PPDB'],
    ['ikon' => 'papan',    'warna' => 'cream', 'judul' => 'Persyaratan',    'isi' => 'Dokumen dan data yang perlu disiapkan sebelum mendaftar'],
    ['ikon' => 'dokumen',  'warna' => 'cream', 'judul' => 'Formulir Online','isi' => 'Isi formulir pendaftaran secara online dengan mudah'],
    ['ikon' => 'centang',  'warna' => 'green', 'judul' => 'Proses Mudah',   'isi' => 'Ikuti langkah-langkah pendaftaran yang sederhana'],
];

// Persyaratan (ganti dengan persyaratan resmi sekolah)
$persyaratan = [
    '[Persyaratan 1 — sesuai ketentuan sekolah]',
    '[Persyaratan 2 — sesuai ketentuan sekolah]',
    '[Persyaratan 3 — sesuai ketentuan sekolah]',
    '[Persyaratan 4 — sesuai ketentuan sekolah]',
    '[Persyaratan 5 — sesuai ketentuan sekolah]',
];
$catatanPersyaratan = 'Pastikan data yang dimasukkan sesuai dengan dokumen resmi. Hubungi sekolah jika ada pertanyaan.';

// Alur pendaftaran (5 langkah, urutan tampilan diatur oleh CSS)
$alur = [
    ['judul' => 'Persiapkan Data',      'isi' => 'Siapkan semua dokumen dan data yang diperlukan sesuai persyaratan.'],
    ['judul' => 'Isi Formulir',         'isi' => 'Lengkapi formulir pendaftaran online dengan data yang benar.'],
    ['judul' => 'Periksa Data',         'isi' => 'Pastikan seluruh data sudah benar sebelum mengirimkan formulir.'],
    ['judul' => 'Pendaftaran Berhasil', 'isi' => 'Sistem akan memberikan konfirmasi setelah pendaftaran berhasil.'],
    ['judul' => 'Download Formulir',    'isi' => 'Unduh formulir daftar ulang untuk proses selanjutnya.'],
];

// Pertanyaan umum (ganti jawaban sesuai kebijakan sekolah)
$faq = [
    ['q' => 'Bagaimana cara mendaftar PPDB',
     'a' => 'Klik tombol Daftar PPDB, isi formulir online dengan data yang benar, periksa kembali, lalu kirimkan. Langkah lengkapnya ada pada bagian Alur Pendaftaran.'],
    ['q' => 'Data apa saja yang perlu disiapkan?',
     'a' => 'Siapkan data dan dokumen sesuai bagian Persyaratan Pendaftaran. Daftar lengkapnya mengikuti ketentuan resmi sekolah.'],
    ['q' => 'bagaimana jika NIK tidak sesuai?',
     'a' => 'NIK harus berupa 16 digit angka. Periksa kembali dengan dokumen resmi. Jika masih tidak sesuai, hubungi pihak sekolah.'],
    ['q' => 'bagaimana mengetahui pendaftaran berhasil',
     'a' => 'Setelah formulir terkirim, sistem akan memberikan konfirmasi bahwa pendaftaran berhasil.'],
    ['q' => 'Bagaimana cara mendapatkan formulir daftar ulang?',
     'a' => 'Setelah pendaftaran berhasil, formulir daftar ulang dapat diunduh untuk proses selanjutnya.'],
];

// Ikon (SVG) untuk kartu Informasi
function ikon_info(string $nama): string {
    $icons = [
        'kalender' => '<rect x="4" y="5" width="16" height="15" rx="2.5"/><path d="M4 10h16M9 3v4M15 3v4"/>',
        'papan'    => '<rect x="5" y="4" width="14" height="17" rx="2.5"/><path d="M9 4V3h6v1M9 11h6M9 15h6"/>',
        'dokumen'  => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
        'centang'  => '<circle cx="12" cy="12" r="8.5"/><path d="m8.5 12.3 2.5 2.5 4.5-5"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . ($icons[$nama] ?? '') . '</svg>';
}

$title = 'PPDB - SD Negeri Kedungsari 1';

layout_start([
    'title'       => $title ?? 'SD Negeri Kedungsari 1',
    'current'     => 'ppdb',
    'description' => 'Pendaftaran Peserta Didik Baru (PPDB) SD Negeri Kedungsari 1 - mudah, cepat, dan sepenuhnya online',
    'css'         => 'ppdb',
    'js'          => null,
]);
?>
<main>

    <!-- ================= HERO ================= -->
    <section class="hero page-hero" style="--bg:url('<?= asset('img/home.jpg') ?>')">
        <div class="page-hero__content">
            <h1>PPDB</h1>
            <p>Mulai langkah pendidikan putra-putri Anda bersama<br>SD Negeri Kedungsari 1.<br>Proses pendaftaran mudah, cepat, dan sepenuhnya online.</p>
            <div class="hero__actions">
                <a class="btn-hero" href="<?= esc($formulirUrl) ?>">Daftar Sekarang</a>
                <a class="btn-hero btn-hero--soft" href="#alur">Pelajari Alur</a>
            </div>
        </div>
    </section>

    <!-- ================= INFORMASI ================= -->
    <section class="section section--leaf" id="informasi">
        <h2 class="title title--dark">Informasi</h2>

        <div class="info-grid">
            <?php foreach ($informasi as $i): ?>
                <article class="info-card info-card--<?= esc($i['warna']) ?>">
                    <span class="info-card__icon"><?= ikon_info($i['ikon']) ?></span>
                    <h3><?= esc($i['judul']) ?></h3>
                    <p><?= esc($i['isi']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================= PERSYARATAN ================= -->
    <section class="section section--leaf" id="persyaratan">
        <h2 class="title">Persyaratan Pendaftaran</h2>

        <div class="syarat">
            <ul class="syarat__list">
                <?php foreach ($persyaratan as $p): ?>
                    <li class="syarat__item">
                        <span class="syarat__check" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 12.5 4 4 8-9"/></svg>
                        </span>
                        <span><?= esc($p) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="catatan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3.5 2.5 20h19z"/><path d="M12 10v4.5M12 17.3v.2"/>
                </svg>
                <span><?= esc($catatanPersyaratan) ?></span>
            </p>
        </div>
    </section>

    <!-- ================= ALUR ================= -->
    <section class="section section--leaf" id="alur">
        <h2 class="title">Alur Pendaftaran</h2>

        <div class="alur">
            <?php foreach ($alur as $n => $a): $no = $n + 1; ?>
                <article class="step step--<?= $no ?> <?= $no % 2 ? 'step--green' : 'step--cream' ?>">
                    <div class="step__head">
                        <span class="step__num"><?= $no ?></span>
                        <h3><?= esc($a['judul']) ?></h3>
                    </div>
                    <p><?= esc($a['isi']) ?></p>
                </article>
            <?php endforeach; ?>

            <!-- Panah penunjuk arah (hanya dekorasi) -->
            <svg class="arrow arrow--1" viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h41M35 4l9 8-9 8"/></svg>
            <svg class="arrow arrow--2" viewBox="0 0 90 50" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 10h25q14 0 14 14v20M35 34l10 10 10-10"/></svg>
            <svg class="arrow arrow--3" viewBox="0 0 90 50" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M45 2v22q0 12-14 12H6M16 26 6 36l10 10"/></svg>
            <svg class="arrow arrow--4" viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M45 12H4M13 4 4 12l9 8"/></svg>
        </div>
    </section>

    <!-- ================= PERHATIKAN NIK ================= -->
    <section class="section section--tint" id="nik">
        <div class="nik">
            <div class="nik__head">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3.5 2.5 20h19z"/><path d="M12 10v4.5M12 17.3v.2"/>
                </svg>
                <h2>Perhatikan Data NIK</h2>
            </div>

            <div class="nik__body">
                <p>NIK wajib diisi dengan angka dan harus terdiri dari tepat <strong>16 karakter</strong>.</p>

                <div class="nik__cmp">
                    <div class="nik__box nik__box--ok">
                        <b>BENAR</b>
                        <strong>1234567890123456</strong>
                        16 digit.
                    </div>
                    <div class="nik__box nik__box--bad">
                        <b>SALAH</b>
                        <strong>123456789012345</strong>
                        Hanya 15 digit.
                    </div>
                </div>

                <p class="nik__note">* Contoh NIK di atas bukan NIK nyata. Gunakan NIK sesuai dokumen resmi.</p>
            </div>
        </div>
    </section>

    <!-- ================= SIAP MENDAFTAR ================= -->
    <section class="cta" id="daftar">
        <h2>Siap Mendaftar?</h2>
        <?php if ($ppdb['status'] === 'ada'): ?>
            <p>Pastikan data sudah disiapkan sebelum mengisi formulir.<br>Pendaftaran dilakukan melalui formulir online sekolah.</p>
            <a class="btn-cta" href="<?= esc($ppdb['url']) ?>" target="_blank" rel="noopener">
                Buka Formulir
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
            </a>
            <?php if ($ppdb['embed']): ?>
                <div class="form-embed">
                    <iframe src="<?= esc($ppdb['embed']) ?>" title="Formulir PPDB SD Negeri Kedungsari 1" loading="lazy">Memuat formulir&hellip;</iframe>
                </div>
            <?php endif; ?>
        <?php elseif ($ppdb['status'] === 'kosong'): ?>
            <p>Formulir PPDB belum tersedia. Silakan hubungi sekolah.</p>
            <a class="btn-cta" href="<?= page_url('kontak') ?>">Hubungi Kami</a>
        <?php else: ?>
            <p>Formulir gagal dimuat. Periksa koneksi internet Anda lalu coba lagi.</p>
            <a class="btn-cta" href="<?= page_url('ppdb') ?>#daftar">Coba Lagi</a>
        <?php endif; ?>
    </section>

    <!-- ================= PERTANYAAN UMUM ================= -->
    <section class="section section--leaf" id="faq">
        <h2 class="title">Pertanyaan Umum</h2>

        <div class="faq">
            <?php foreach ($faq as $f): ?>
                <details>
                    <summary>
                        <?= esc($f['q']) ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <p><?= esc($f['a']) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================= BANTUAN ================= -->
    <section class="section section--tint" id="bantuan">
        <div class="bantuan">
            <h3>Kesulitan Mengisi Formulir ?</h3>
            <p>Hubungi pihak sekolah untuk mendapatkan bantuan pengisian formulir.</p>
            <a class="btn-hubungi" href="<?= page_url('kontak') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 3.5h3.2l1.6 4.2-2 1.3a11 11 0 0 0 6.2 6.2l1.3-2 4.2 1.6V18a2.5 2.5 0 0 1-2.7 2.5C9.9 20 4 14.1 3.5 6.2A2.5 2.5 0 0 1 5 3.5z"/>
                </svg>
                Hubungi Kami
            </a>
        </div>
    </section>

</main>
<?php layout_end(); ?>
