<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Data Kelas</h1>
    <p>Kelola data kelas <?= e($config['schoolName']) ?>.</p>
  </div>
  <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Kelas</button>
</div>

<div class="kelas-layout">
  <section class="table-card" aria-label="Daftar kelas">
    <div class="toolbar">
      <div class="toolbar__search" role="search">
        <?= $ico('search', 'ico--sm') ?>
        <input type="search" class="input" id="kl-search" placeholder="Cari data kelas..." autocomplete="off" aria-label="Cari data kelas">
      </div>
      <div class="toolbar__group">
        <div class="select-wrap">
          <select class="input select" id="kl-tahun" aria-label="Filter tahun ajaran"></select>
          <?= $ico('chevron', 'ico--sm select-wrap__arrow') ?>
        </div>
        <button type="button" class="btn btn--soft" id="kl-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table table--kelas" id="kl-table">
        <thead>
          <tr>
            <th scope="col">Tahun Ajaran</th>
            <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nama">Nama Kelas <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
            <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="tingkat">Tingkat <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
            <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="wali">Wali Kelas <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
            <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="jumlah">Jumlah Siswa <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody id="kl-body">
          <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
          <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
          <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="state-box" id="kl-state" hidden></div>

    <div class="pager-bar" id="kl-footer" hidden>
      <span id="kl-count"></span>
      <nav class="pager" id="kl-pager" aria-label="Halaman daftar"></nav>
    </div>
  </section>

  <aside class="rombel" id="kl-rombel" aria-label="Kelengkapan rombel" hidden>
    <div class="rombel__head">
      <div>
        <h2>Kelengkapan Rombel</h2>
        <p id="kl-rombel-year"></p>
      </div>
      <span class="badge" id="kl-rombel-badge"></span>
    </div>
    <ul class="rombel__list" id="kl-rombel-list"></ul>
  </aside>
</div>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>