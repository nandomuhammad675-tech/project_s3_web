<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head">
  <h1>Laporan Excel</h1>
  <p>Susun dan unduh rekap data akademik sekolah.</p>
</div>

<div class="lap-grid">
  <section class="card lap-card" aria-labelledby="lap-title">
    <div class="lap-head">
      <span class="lap-head__ico"><?= $ico('file') ?></span>
      <div>
        <h2 id="lap-title">Parameter Laporan</h2>
        <p>Pilih cakupan data yang ingin disertakan.</p>
      </div>
    </div>

    <div class="state-box" id="lap-state" hidden></div>

    <form id="form-laporan" novalidate>
      <div class="lap-params">
        <div class="field">
          <label for="lap-ta">Tahun Ajaran</label>
          <div class="select-wrap lap-select">
            <select class="input select" id="lap-ta" disabled></select>
            <?= $ico('chevron', 'ico--xs select-wrap__arrow') ?>
          </div>
        </div>
        <div class="field">
          <label for="lap-sem">Semester</label>
          <div class="select-wrap lap-select">
            <select class="input select" id="lap-sem" disabled>
              <option value="ganjil">Ganjil</option>
              <option value="genap">Genap</option>
            </select>
            <?= $ico('chevron', 'ico--xs select-wrap__arrow') ?>
          </div>
        </div>
        <div class="field">
          <label for="lap-kelas">Kelas <span class="opt">(opsional)</span></label>
          <div class="select-wrap lap-select">
            <select class="input select" id="lap-kelas" aria-describedby="lap-kelas-hint" disabled></select>
            <?= $ico('chevron', 'ico--xs select-wrap__arrow') ?>
          </div>
        </div>
      </div>
      <div class="field__hint" id="lap-kelas-hint" hidden>Pilih satu tahun ajaran untuk memilih kelas tertentu.</div>

      <div class="notice" style="background:#f4f6ea;border:1px solid #dfe5c8;margin-bottom:12px">
        <b>Isi file Excel:</b> satu sheet <b>Informasi</b>, lalu <b>Rekap Absensi</b> satu sheet per kelas (ABS-4A, ...) dan
        <b>Rekap Nilai</b> satu sheet per mata pelajaran per kelas (MTK-4A, ...) dengan kolom UH 1, UH 2, ..., ASTS, ASAS.
        Kelas dan mata pelajaran tidak pernah digabung dalam satu sheet.
      </div>
      <div class="notice notice--warn" id="lap-msg" role="alert" hidden></div>

      <div class="lap-actions">
        <button type="button" class="btn btn--outline" id="lap-preview" disabled><?= $ico('eye', 'ico--sm') ?> <span>Lihat Pratinjau</span></button>
        <button type="submit" class="btn btn--amber" id="lap-download" disabled><?= $ico('download', 'ico--sm') ?> <span>Unduh Excel</span></button>
      </div>
    </form>
  </section>

  <aside class="lap-aside" aria-label="Isi laporan">
    <?= $ico('info', 'ico--sm') ?>
    <h2>Isi laporan</h2>
    <ul>
      <li><strong>Kepala laporan</strong><span>Nama sekolah, semester, tahun ajaran, kelas, tanggal ekspor</span></li>
      <li><strong>Lembar Rekap Absensi</strong><span>Hadir, sakit, izin, alpa per siswa</span></li>
      <li><strong>Lembar Rekap Nilai</strong><span>UH-1 sampai UH-n, ASTS, ASAS per mata pelajaran</span></li>
    </ul>
    <p>File dibuat saat diminta dan tidak disimpan di sistem.</p>
  </aside>
</div>

<section class="table-card lap-preview" id="lap-pv" aria-label="Pratinjau laporan" hidden>
  <div class="lap-pv__head" id="lap-pv-head"></div>
  <div class="lap-tabs" role="tablist" aria-label="Lembar laporan">
    <button type="button" class="lap-tab" role="tab" id="lap-tab-absensi" data-sheet="absensi" aria-selected="true">Rekap Absensi</button>
    <button type="button" class="lap-tab" role="tab" id="lap-tab-nilai" data-sheet="nilai" aria-selected="false" tabindex="-1">Rekap Nilai</button>
  </div>
  <div class="table-wrap" role="tabpanel" aria-live="polite">
    <table class="table table--laporan">
      <thead id="lap-pv-thead"></thead>
      <tbody id="lap-pv-body"></tbody>
    </table>
  </div>
  <div class="state-box" id="lap-pv-state" hidden></div>
  <div class="pager-bar" id="lap-pv-footer" hidden>
    <span id="lap-pv-count"></span>
    <nav class="pager" id="lap-pv-pager" aria-label="Halaman pratinjau"></nav>
  </div>
</section>