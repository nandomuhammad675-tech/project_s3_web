<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head">
  <h1>Pengaturan Website</h1>
  <p>Kelola tautan penting yang tampil di website sekolah.</p>
</div>

<section class="card set-card" aria-labelledby="set-title">
  <div class="set-head">
    <span class="set-head__ico"><?= $ico('layout') ?></span>
    <div>
      <h2 id="set-title">Tautan Publik</h2>
      <p>Pastikan tautan menggunakan format URL yang benar.</p>
    </div>
  </div>

  <div class="state-box" id="set-state" hidden></div>

  <form id="form-pengaturan" novalidate>
    <div class="field">
      <label for="set-ppdb">URL PPDB</label>
      <input class="input" type="url" id="set-ppdb" inputmode="url" autocomplete="off" spellcheck="false"
             placeholder="https://ppdb.sdnkedungsari1.sch.id" aria-describedby="set-ppdb-hint set-ppdb-err" disabled>
      <div class="field__hint" id="set-ppdb-hint">Alamat formulir pendaftaran (mis. Google Form). Kosongkan bila belum tersedia.</div>
      <div class="field__error" id="set-ppdb-err" hidden></div>
    </div>

    <div class="field">
      <label for="set-video">URL Video Profil</label>
      <input class="input" type="url" id="set-video" inputmode="url" autocomplete="off" spellcheck="false"
             placeholder="https://youtube.com/watch?v=..." aria-describedby="set-video-hint set-video-err" disabled>
      <div class="field__hint" id="set-video-hint">Video tidak diunggah ke sistem, hanya alamat (URL) dari YouTube.</div>
      <div class="field__hint field__hint--ok" id="set-video-conv" hidden></div>
      <div class="field__error" id="set-video-err" hidden></div>
    </div>

    <div class="set-meta" id="set-meta" aria-live="polite">
      <?= $ico('info', 'ico--sm') ?>
      <span id="set-meta-text"><span class="skeleton"></span></span>
    </div>

    <div class="notice notice--warn" id="set-msg" role="alert" hidden></div>

    <div class="set-actions">
      <button type="submit" class="btn btn--amber" id="set-save" disabled>Simpan Pengaturan</button>
    </div>
  </form>
</section>