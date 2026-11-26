<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Data Guru</h1>
    <p>Kelola data guru <?= e($config['schoolName']) ?>.</p>
  </div>
  <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Guru</button>
</div>

<div class="alerts guru-alert">
  <div class="alert" role="note">
    <?= $ico('alert', 'ico--sm') ?>
    <p>Menonaktifkan guru yang masih menjadi wali kelas akan memblokir proses kenaikan kelas.</p>
  </div>
</div>

<section class="table-card" aria-label="Daftar guru">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="gr-search" placeholder="Cari data guru..." autocomplete="off" aria-label="Cari data guru">
    </div>
    <div class="toolbar__group">
      <div class="select-wrap">
        <select class="input select" id="gr-status" aria-label="Filter status akun">
          <option value="">Semua status</option>
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
        <?= $ico('chevron', 'ico--sm select-wrap__arrow') ?>
      </div>
      <button type="button" class="btn btn--soft" id="gr-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="table table--guru" id="gr-table">
      <thead>
        <tr>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nama">Nama <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nip">NIP <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="jabatan">Jabatan <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Status Akun</th>
          <th scope="col">Kelas Tahun Aktif</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="gr-body">
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="gr-state" hidden></div>

  <div class="pager-bar" id="gr-footer" hidden>
    <span id="gr-count"></span>
    <nav class="pager" id="gr-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>