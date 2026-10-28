<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Tahun Ajaran</h1>
    <p>Kelola tahun ajaran <?= e($config['schoolName']) ?>.</p>
  </div>
  <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Tahun Ajaran</button>
</div>

<section class="table-card" aria-label="Daftar tahun ajaran">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="ta-search" placeholder="Cari tahun ajaran..." autocomplete="off" aria-label="Cari tahun ajaran">
    </div>
    <button type="button" class="btn btn--soft" id="ta-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
  </div>

  <div class="table-wrap">
    <table class="table table--ta" id="ta-table">
      <thead>
        <tr>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nama">Nama Tahun <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Semester Berjalan</th>
          <th scope="col">Status</th>
          <th scope="col">Tindakan</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="ta-body">
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="ta-state" hidden></div>

  <div class="pager-bar" id="ta-footer" hidden>
    <span id="ta-count"></span>
    <nav class="pager" id="ta-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>