<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Penempatan Siswa</h1>
    <p>Kelola riwayat kelas dan nomor absen siswa.</p>
  </div>
  <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tempatkan Siswa</button>
</div>

<div class="notice notice--info notice--icon" role="note">
  <?= $ico('info', 'ico--sm') ?>
  <span>Untuk memindahkan kelas, hubungi pengembang.</span>
</div>

<section class="table-card" aria-label="Daftar penempatan siswa">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="rk-search" placeholder="Cari NISN atau nama siswa..." autocomplete="off" aria-label="Cari NISN atau nama siswa">
    </div>
    <div class="toolbar__group">
      <div class="select-wrap">
        <select class="input select" id="rk-tahun" aria-label="Filter tahun ajaran"></select>
        <?= $ico('chevron', 'ico--sm select-wrap__arrow') ?>
      </div>
      <div class="select-wrap">
        <select class="input select" id="rk-kelas" aria-label="Filter kelas"></select>
        <?= $ico('chevron', 'ico--sm select-wrap__arrow') ?>
      </div>
    </div>
  </div>

  <div class="table-wrap">
    <table class="table table--rk" id="rk-table">
      <thead>
        <tr>
          <th scope="col">Tahun</th>
          <th scope="col">Kelas</th>
          <th scope="col">Nomor Absen</th>
          <th scope="col">NISN</th>
          <th scope="col">Nama</th>
          <th scope="col">Status</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="rk-body">
        <tr class="is-skeleton"><td colspan="7"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="7"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="7"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="rk-state" hidden></div>

  <div class="pager-bar" id="rk-footer" hidden>
    <span id="rk-count"></span>
    <nav class="pager" id="rk-pager" aria-label="Halaman daftar"></nav>
  </div>

  <div class="table-note">
    <?= $ico('alert', 'ico--sm') ?>
    <span>Hapus penempatan hanya untuk memperbaiki kesalahan.</span>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>