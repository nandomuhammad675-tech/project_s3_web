<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Mata Pelajaran</h1>
    <p>Kelola mata pelajaran <?= e($config['schoolName']) ?>.</p>
  </div>
  <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Mata Pelajaran</button>
</div>

<section class="table-card" aria-label="Daftar mata pelajaran">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="mp-search" placeholder="Cari mata pelajaran..." autocomplete="off" aria-label="Cari mata pelajaran">
    </div>
    <button type="button" class="btn btn--soft" id="mp-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
  </div>

  <div class="table-wrap">
    <table class="table table--master" id="mp-table">
      <thead>
        <tr>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="kode">Kode <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nama">Nama Mata Pelajaran <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="mp-body">
        <tr class="is-skeleton"><td colspan="3"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="3"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="3"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="mp-state" hidden></div>

  <div class="pager-bar" id="mp-footer" hidden>
    <span id="mp-count"></span>
    <nav class="pager" id="mp-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>