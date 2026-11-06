<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
$sel = function (string $id, string $label, string $wrapId = '') use ($ico) {
    return '<div class="select-wrap ak-select"' . ($wrapId ? ' id="' . $wrapId . '"' : '') . '>' .
        '<select class="input select" id="' . $id . '" aria-label="' . $label . '"></select>' .
        $ico('chevron', 'ico--xs select-wrap__arrow') . '</div>';
};
?>
<div class="page__head">
  <h1>Data Akademik</h1>
  <p>Pusat pemantauan data akademik seluruh kelas.</p>
</div>

<div class="ak-note" role="note">
  <?= $ico('lock', 'ico--sm') ?>
  <strong>Data ini diinput Guru. Admin hanya dapat melihat.</strong>
</div>

<div class="ak-tabs" role="tablist" aria-label="Jenis data akademik">
  <button type="button" class="ak-tab" role="tab" id="tab-absensi" data-tab="absensi" aria-controls="ak-panel" aria-selected="true">Absensi</button>
  <button type="button" class="ak-tab" role="tab" id="tab-nilai" data-tab="nilai" aria-controls="ak-panel" aria-selected="false" tabindex="-1">Nilai</button>
  <button type="button" class="ak-tab" role="tab" id="tab-disiplin" data-tab="disiplin" aria-controls="ak-panel" aria-selected="false" tabindex="-1">Kedisiplinan</button>
</div>

<section class="table-card" id="ak-panel" role="tabpanel" aria-labelledby="tab-absensi">
  <div class="toolbar ak-toolbar">
    <div class="ak-filters">
      <?= $sel('ak-ta', 'Tahun ajaran') ?>
      <?= $sel('ak-sem', 'Semester', 'ak-sem-wrap') ?>
      <?= $sel('ak-kelas', 'Kelas') ?>
      <?= $sel('ak-mapel', 'Mata pelajaran', 'ak-mapel-wrap') ?>
      <label class="ak-date" id="ak-tgl-wrap"><span>Tanggal</span>
        <input type="date" class="input input--date" id="ak-tgl" aria-label="Tanggal"></label>
      <label class="ak-date" id="ak-dari-wrap"><span>Dari</span>
        <input type="date" class="input input--date" id="ak-dari" aria-label="Tanggal awal"></label>
      <label class="ak-date" id="ak-sampai-wrap"><span>Sampai</span>
        <input type="date" class="input input--date" id="ak-sampai" aria-label="Tanggal akhir"></label>
      <div class="toolbar__search ak-siswa" role="search">
        <?= $ico('search', 'ico--sm') ?>
        <input type="search" class="input" id="ak-siswa" placeholder="Cari nama atau NISN siswa..." autocomplete="off" aria-label="Cari siswa">
      </div>
      <button type="button" class="btn btn--soft" id="ak-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
    </div>
    <button type="button" class="btn btn--outline" id="ak-export" disabled><?= $ico('download', 'ico--sm') ?> Ekspor Tampilan</button>
  </div>

  <div class="notice notice--warn ak-warn" id="ak-warn" role="alert" hidden></div>

  <div class="table-wrap">
    <table class="table table--akademik" id="ak-table">
      <thead id="ak-head"></thead>
      <tbody id="ak-body"></tbody>
    </table>
  </div>

  <div class="state-box" id="ak-state" hidden></div>

  <div class="pager-bar" id="ak-footer" hidden>
    <span id="ak-count"></span>
    <nav class="pager" id="ak-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>