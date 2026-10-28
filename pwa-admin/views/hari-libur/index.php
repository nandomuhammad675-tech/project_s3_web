<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Hari Libur</h1>
    <p>Kelola hari libur <?= e($config['schoolName']) ?>.</p>
  </div>
  <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Hari Libur</button>
</div>

<div class="notice notice--info notice--row" role="note">
  <?= $ico('info', 'ico--sm') ?>
  <span>Hari Minggu otomatis libur, tidak perlu diinput.</span>
</div>

<section class="table-card" aria-label="Daftar hari libur">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="hl-search" placeholder="Cari hari libur..." autocomplete="off" aria-label="Cari hari libur">
    </div>
    <button type="button" class="btn btn--soft" id="hl-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
  </div>

  <div class="table-wrap">
    <table class="table table--libur" id="hl-table">
      <thead>
        <tr>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="tanggal">Tanggal <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="ket">Keterangan <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Berlaku Untuk</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="hl-body">
        <tr class="is-skeleton"><td colspan="4"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="4"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="4"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="hl-state" hidden></div>

  <div class="pager-bar" id="hl-footer" hidden>
    <span id="hl-count"></span>
    <nav class="pager" id="hl-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>