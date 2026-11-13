<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Mading Siswa</h1>
    <p>Kelola karya pilihan siswa untuk website.</p>
  </div>
  <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Karya</button>
</div>

<section class="table-card" aria-label="Daftar karya mading">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="md-search" placeholder="Cari judul atau penulis..." autocomplete="off" aria-label="Cari judul atau penulis">
    </div>
    <div class="toolbar__group">
      <div class="select-wrap">
        <select class="input select" id="md-kategori" aria-label="Filter kategori">
          <option value="">Semua kategori</option>
        </select>
        <?= $ico('chevron', 'ico--xs select-wrap__arrow') ?>
      </div>
      <button type="button" class="btn btn--soft" id="md-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="table table--mading" id="md-table">
      <thead>
        <tr>
          <th scope="col">Karya</th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="judul">Judul <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Kategori</th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="penulis">Penulis <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="tanggal">Tanggal <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="md-body">
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="md-state" hidden></div>

  <div class="pager-bar" id="md-footer" hidden>
    <span id="md-count"></span>
    <nav class="pager" id="md-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>