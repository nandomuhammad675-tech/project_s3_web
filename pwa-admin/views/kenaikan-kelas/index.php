<?php
/**
 * Tahun Ajaran & Kenaikan Kelas (wizard 6 langkah) | dokumen 01 bagian 3.6.
 * Kerangka saja; isi langkah dirender oleh assets/js/kenaikan-kelas.js.
 */
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head">
  <h1>Tahun Ajaran &amp; Kenaikan Kelas</h1>
  <p id="wiz-sub">Proses terpadu untuk menyiapkan tahun ajaran berikutnya.</p>
</div>

<!-- Wizard (stepper + isi langkah) -->
<div id="wiz" class="wiz" aria-live="polite">
  <section class="card"><span class="skeleton skeleton--row"></span></section>
  <section class="card"><span class="skeleton skeleton--row"></span><br><br><span class="skeleton skeleton--row"></span></section>
</div>

<!-- Log Kenaikan Kelas (hanya lihat) -->
<section class="card card--flush" aria-labelledby="log-title">
  <div class="abs-head">
    <div>
      <h2 id="log-title" class="h3">Log Kenaikan Kelas</h2>
      <p class="muted small">Riwayat proses siswa terbaru</p>
    </div>
    <button type="button" class="btn btn--ghost btn--sm" id="log-toggle" hidden>Lihat Semua</button>
  </div>
  <div class="table-wrap">
    <table class="table table--plain" id="log-table">
      <thead>
        <tr>
          <th scope="col">Tanggal</th>
          <th scope="col">Siswa</th>
          <th scope="col">Dari</th>
          <th scope="col">Ke</th>
          <th scope="col">Status</th>
        </tr>
      </thead>
      <tbody id="log-body">
        <tr><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>
  <div class="state-box" id="log-state" hidden></div>
</section>

<!-- Konfirmasi menjalankan kenaikan kelas (teks WAJIB persis dokumen) -->
<dialog class="dialog" id="dlg-run" aria-labelledby="dlg-run-title">
  <form method="dialog" id="form-run" class="dialog__body">
    <h2 id="dlg-run-title" class="h3">Jalankan Kenaikan Kelas</h2>
    <p class="dialog__text">Proses ini tidak dapat dibatalkan lewat sistem. Lanjutkan?</p>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="run-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="run-ok">Lanjutkan</button>
    </div>
  </form>
</dialog>