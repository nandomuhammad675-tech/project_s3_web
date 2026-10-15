<?php
/**
 * Halaman Beranda Guru — skrip: assets/js/dashboard.js (GET {apiBase}/guru/dashboard)
 */
$pintasan = [
    ['key' => 'nilai',    'judul' => 'Nilai',        'sub' => 'Input dan rekap nilai', 'icon' => 'book-open'],
    ['key' => 'jadwal',   'judul' => 'Jadwal',       'sub' => 'Pelajaran hari ini',    'icon' => 'calendar'],
    ['key' => 'piket',    'judul' => 'Piket',        'sub' => 'Petugas kebersihan',    'icon' => 'key-round'],
    ['key' => 'disiplin', 'judul' => 'Kedisiplinan', 'sub' => 'Catatan siswa',         'icon' => 'shield-check'],
    ['key' => 'laporan',  'judul' => 'Laporan',      'sub' => 'Unduh data kelas',      'icon' => 'file-text'],
];
?>
<section class="hero">
  <div class="container">
    <p class="hero-date" id="tanggalHariIni">&nbsp;</p>
    <h1 class="hero-title" id="sapaan">Selamat datang</h1>
    <p class="hero-sub">Semoga kegiatan belajar hari ini berjalan lancar.</p>
  </div>
</section>

<div class="container stack pull-up" id="dashboard" aria-live="polite">

<?php
$errorId = 'dashError';
require __DIR__ . '/../partials/error-state.php';
?>

  <!-- Kelas yang diampu -->
  <section class="card kelas-card" id="kartuKelas" aria-busy="true">
    <div class="kelas-info">
      <p class="eyebrow">Kelas yang Anda wali</p>
      <h2 class="kelas-nama" id="kelasNama">&nbsp;</h2>
      <p class="muted" id="kelasMeta">&nbsp;</p>
    </div>
    <div class="stat" id="kelasStat">
      <?= icon('users') ?>
      <div><strong id="jumlahSiswa">-</strong><small>siswa aktif</small></div>
    </div>
  </section>

  <!-- Absensi hari ini -->
  <section class="card absen-card" id="kartuAbsen" aria-busy="true">
    <div class="absen-head">
      <p class="eyebrow">Absensi hari ini</p>
      <span class="chip chip--muted" id="absenChip">Memuat</span>
    </div>
    <h2 class="absen-tanggal" id="absenTanggal">&nbsp;</h2>
    <p class="muted" id="absenPesan">Memeriksa status absensi...</p>

    <ul class="rekap" id="absenRekap" hidden>
      <li class="rekap-hadir"><strong data-rekap="hadir">0</strong><span>Hadir</span></li>
      <li class="rekap-sakit"><strong data-rekap="sakit">0</strong><span>Sakit</span></li>
      <li class="rekap-izin"><strong data-rekap="izin">0</strong><span>Izin</span></li>
      <li class="rekap-alpa"><strong data-rekap="alpa">0</strong><span>Alpa</span></li>
    </ul>

    <a class="btn btn--gold" id="absenTombol" href="<?= e($root) ?>index.php?page=absensi" hidden>
      <?= icon('clipboard-check') ?><span>Isi Absensi</span>
    </a>
  </section>

  <!-- Pintasan -->
  <nav class="shortcuts" aria-label="Pintasan">
<?php foreach ($pintasan as $p): ?>
    <a class="card shortcut" href="<?= e($root) ?>index.php?page=<?= e($p['key']) ?>">
      <span class="shortcut-icon"><?= icon($p['icon']) ?></span>
      <span class="shortcut-text"><strong><?= e($p['judul']) ?></strong><small><?= e($p['sub']) ?></small></span>
      <?= icon('chevron-right', 'i--sm shortcut-arrow') ?>
    </a>
<?php endforeach; ?>
  </nav>
</div>