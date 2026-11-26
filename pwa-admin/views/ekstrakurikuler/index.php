<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Ekstrakurikuler</h1>
    <p>Kelola ekstrakurikuler <?= e($config['schoolName']) ?>.</p>
  </div>
  <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Ekstrakurikuler</button>
</div>

<section class="table-card" aria-label="Daftar ekstrakurikuler">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="ek-search" placeholder="Cari ekstrakurikuler..." autocomplete="off" aria-label="Cari ekstrakurikuler">
    </div>
    <button type="button" class="btn btn--soft" id="ek-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
  </div>

  <div class="table-wrap">
    <table class="table table--ekskul" id="ek-table">
      <thead>
        <tr>
          <th scope="col">Ikon</th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nama">Nama <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Deskripsi</th>
          <th scope="col">Guru Pembina</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="ek-body">
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="ek-state" hidden></div>

  <div class="pager-bar" id="ek-footer" hidden>
    <span id="ek-count"></span>
    <nav class="pager" id="ek-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>