<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head page__head--row">
  <div>
    <h1>Event &amp; Galeri</h1>
    <p>Publikasikan kegiatan dan dokumentasi sekolah.</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <button type="button" class="btn btn--outline" id="btn-galeri-umum"><?= $ico('image', 'ico--sm') ?> Galeri Umum</button>
    <button type="button" class="btn btn--amber" id="btn-tambah"><?= $ico('plus', 'ico--sm') ?> Tambah Event</button>
  </div>
</div>

<section class="ev-featured" id="ev-featured" aria-label="Event terbaru" hidden></section>

<section class="table-card" aria-label="Daftar event">
  <div class="toolbar">
    <div class="toolbar__search" role="search">
      <?= $ico('search', 'ico--sm') ?>
      <input type="search" class="input" id="ev-search" placeholder="Cari event..." autocomplete="off" aria-label="Cari event">
    </div>
    <div class="toolbar__group">
      <div class="select-wrap">
        <select class="input select" id="ev-kategori" aria-label="Filter kategori">
          <option value="">Semua kategori</option>
          <option value="kegiatan">Kegiatan</option>
          <option value="event">Event</option>
          <option value="prestasi">Prestasi</option>
          <option value="kolaborasi">Kolaborasi</option>
        </select>
        <?= $ico('chevron', 'ico--xs select-wrap__arrow') ?>
      </div>
      <button type="button" class="btn btn--soft" id="ev-reset"><?= $ico('refresh', 'ico--sm') ?> Reset</button>
    </div>
  </div>

  <div class="table-wrap">
    <table class="table table--event" id="ev-table">
      <thead>
        <tr>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="nama">Judul Event <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Kategori</th>
          <th scope="col" aria-sort="none"><button type="button" class="th-sort" data-sort="tanggal">Tanggal <span class="th-sort__arrow" aria-hidden="true"></span></button></th>
          <th scope="col">Status</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="ev-body">
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="ev-state" hidden></div>

  <div class="pager-bar" id="ev-footer" hidden>
    <span id="ev-count"></span>
    <nav class="pager" id="ev-pager" aria-label="Halaman daftar"></nav>
  </div>
</section>

<?php require __DIR__ . '/form.php'; ?>
<?php $delId = 'dlg-hapus'; require __DIR__ . '/../partials/modal-confirm-delete.php'; ?>

<?php require __DIR__ . '/galeri.php'; ?>
<script>document.getElementById('btn-galeri-umum').addEventListener('click', function () { document.dispatchEvent(new CustomEvent('sikesa:galeri-buka', { detail: { idEvent: null, nama: '' } })); });</script>
