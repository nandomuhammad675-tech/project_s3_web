<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Data Siswa</h1>
    <p>Kelola data siswa <?= e($config['schoolName']) ?>.</p>
  </div>
  <div class="page__actions">
    <button type="button" class="btn btn--outline" id="btn-impor"><?= $ico('upload', 'ico--sm') ?> Impor dari Excel</button>
    <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Siswa</button>
  </div>
</div>

<section class="table-card" aria-label="Daftar siswa">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="sw-search" placeholder="Cari data siswa..." autocomplete="off" aria-label="Cari data siswa">
    </div>
    <div class="toolbar__group">
      <div class="select-wrap">
        <select class="input select" id="sw-status" aria-label="Filter status siswa">
          <option value="">Semua status</option>
          <option value="baru">Baru</option>
          <option value="aktif">Aktif</option>
          <option value="lulus">Lulus</option>
          <option value="pindah">Pindah</option>
        </select>
        <?= $ico('chevron', 'ico--sm select-wrap__arrow') ?>
      </div>
      <div class="select-wrap">
        <select class="input select" id="sw-kelas" aria-label="Filter kelas">
          <option value="">Semua kelas</option>
        </select>
        <?= $ico('chevron', 'ico--sm select-wrap__arrow') ?>
      </div>
      <button type="button" class="btn btn--soft" id="sw-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="table table--siswa" id="sw-table">
      <thead>
        <tr>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nisn">NISN <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nama">Nama <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Status</th>
          <th scope="col">Kelas Tahun Aktif</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="sw-body">
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="sw-state" hidden></div>

  <div class="pager-bar" id="sw-footer" hidden>
    <span id="sw-count"></span>
    <nav class="pager" id="sw-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>