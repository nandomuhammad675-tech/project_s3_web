<?php
/**
 * Dasbor Admin (Beranda).
 * Markup kerangka saja; isi diisi oleh assets/js/dashboard.js.
 * Setiap blok punya tiga keadaan: memuat, kosong, gagal (dengan tombol coba lagi).
 */
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head">
  <h1>Dasbor</h1>
  <p>Pusat kontrol setup dan operasional <?= e($config['schoolName']) ?>.</p>
</div>

<!-- 1. Prasyarat sistem -->
<section class="setup card card--accent" id="setup" aria-labelledby="setup-title" hidden>
  <div class="setup__top">
    <div>
      <div class="eyebrow">Prasyarat sistem</div>
      <h2 id="setup-title">Status Setup Sistem</h2>
      <p class="muted">Selesaikan langkah berikut secara berurutan agar fitur operasional dapat digunakan.</p>
    </div>
    <span class="pill pill--amber" id="setup-count"></span>
  </div>
  <ol class="steps" id="setup-steps"></ol>
  <div class="setup__foot">
    <div class="progress progress--amber" role="progressbar" aria-valuemin="0" aria-valuemax="100" id="setup-bar">
      <span class="progress__fill" id="setup-fill"></span>
    </div>
    <span class="muted small" id="setup-pct"></span>
    <a class="btn btn--amber" id="setup-next" href="#"><?= $ico('chevron', 'ico--sm') ?> Lanjutkan Setup</a>
  </div>
</section>

<!-- 2. Tahun ajaran aktif + kesiapan operasional -->
<div class="grid-2" id="overview">
  <section class="card" aria-labelledby="ta-title">
    <div class="card__row">
      <div>
        <div class="label-sm" id="ta-title">Tahun ajaran aktif</div>
        <div class="big" id="ta-name"><span class="skeleton skeleton--lg"></span></div>
      </div>
      <span class="pill pill--green" id="ta-status" hidden>Aktif</span>
    </div>
    <hr class="divider">
    <div class="card__row">
      <div>
        <div class="label-xs">Semester berjalan</div>
        <div class="semester" id="ta-sem"><span class="skeleton"></span></div>
      </div>
      <button type="button" class="btn btn--outline" id="btn-semester" hidden>Ganti Semester</button>
    </div>
    <hr class="divider">
    <a class="btn btn--outline btn--block" href="<?= e(url('kenaikan-kelas')) ?>">
      <?= $ico('calendar', 'ico--sm') ?> Kelola Tahun Ajaran &amp; Kenaikan Kelas
    </a>
    <div class="state-error" id="ov-error-a" hidden></div>
  </section>

  <section class="card" aria-labelledby="ks-title">
    <div class="card__row">
      <div>
        <div class="label-sm" id="ks-title">Kesiapan operasional</div>
        <div class="medium" id="ks-head"><span class="skeleton"></span></div>
      </div>
      <span class="dot" id="ks-dot" aria-hidden="true"></span>
    </div>
    <hr class="divider">
    <ul class="checklist" id="ks-list">
      <li><span class="skeleton skeleton--row"></span></li>
      <li><span class="skeleton skeleton--row"></span></li>
      <li><span class="skeleton skeleton--row"></span></li>
    </ul>
    <div class="state-error" id="ov-error-b" hidden></div>
  </section>
</div>

<!-- 3. Status absensi semua kelas -->
<section class="card card--flush" aria-labelledby="abs-title">
  <div class="abs-head">
    <div>
      <h2 id="abs-title" class="h3">Status Absensi Kelas</h2>
      <p class="muted small">Rekap kehadiran per kelas</p>
    </div>
    <div class="abs-controls">
      <button type="button" class="btn btn--chip" id="btn-kemarin">Kemarin</button>
      <button type="button" class="btn btn--chip is-active" id="btn-hariini">Hari ini</button>
      <label class="date-btn" for="abs-date">
        <?= $ico('calendar', 'ico--sm') ?>
        <span id="abs-date-label">-</span>
        <input type="date" id="abs-date" class="date-btn__input" aria-label="Pilih tanggal absensi">
      </label>
    </div>
  </div>
</section>

<div class="summary" id="abs-summary" hidden>
  <div class="summary__text">
    <strong id="abs-sum-main"></strong>
    <span class="muted xs" id="abs-sum-sub"></span>
  </div>
  <div class="progress progress--green" role="progressbar" aria-valuemin="0" aria-valuemax="100" id="abs-bar">
    <span class="progress__fill" id="abs-fill"></span>
  </div>
  <span class="pill pill--green" id="abs-pct"></span>
</div>

<div class="alerts" id="alerts" aria-live="polite"></div>

<section class="table-card" aria-label="Tabel status absensi per kelas">
  <div class="table-wrap">
    <table class="table" id="abs-table">
      <thead>
        <tr>
          <th scope="col">Kelas</th>
          <th scope="col">Wali kelas</th>
          <th scope="col">Status absensi</th>
          <th scope="col">Terakhir diperbarui</th>
        </tr>
      </thead>
      <tbody id="abs-body">
        <tr class="is-skeleton"><td colspan="4"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="4"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="4"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>
  <div class="state-box" id="abs-state" hidden></div>
  <p class="table-note"><?= $ico('info', 'ico--xs') ?> Hari Minggu otomatis menampilkan Libur untuk semua kelas.</p>
</section>

<!-- Dialog Ganti Semester (teks WAJIB persis seperti dokumen 01 bagian 3.6) -->
<dialog class="dialog" id="dlg-semester" aria-labelledby="dlg-sem-title">
  <form method="dialog" id="form-semester" class="dialog__body">
    <h2 id="dlg-sem-title" class="h3">Ganti Semester</h2>
    <p class="dialog__text">Semester akan diubah dari Ganjil ke Genap. Tindakan ini <strong>TIDAK DAPAT DIKEMBALIKAN</strong>. Absensi yang dicatat setelah ini akan tercatat sebagai semester genap. Lanjutkan?</p>
    <label class="check">
      <input type="checkbox" id="sem-confirm">
      <span>Saya mengerti</span>
    </label>
    <div class="notice notice--info" id="sem-msg" role="alert" hidden></div>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="sem-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="sem-ok" disabled>Lanjutkan</button>
    </div>
  </form>
</dialog>