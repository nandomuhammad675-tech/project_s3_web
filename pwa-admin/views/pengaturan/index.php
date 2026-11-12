<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
$kartu = [
  'ppdb'  => ['judul' => 'Tautan PPDB', 'label' => 'URL Formulir PPDB', 'ph' => 'https://forms.gle/...',
              'hint' => 'Alamat formulir pendaftaran (mis. Google Form). Tombol PPDB di website mengarah ke tautan ini. Hapus tautan setelah pendaftaran PPDB ditutup.',
              'simpan' => 'Simpan Tautan PPDB'],
  'video' => ['judul' => 'Tautan Video Profil', 'label' => 'URL Video Profil', 'ph' => 'https://youtube.com/watch?v=...',
              'hint' => 'Video tidak diunggah ke sistem, hanya alamat (URL) dari YouTube. Alamat YouTube diubah otomatis ke bentuk embed.',
              'simpan' => 'Simpan Tautan Video'],
];
?>
<div class="page__head">
  <h1>Pengaturan Website</h1>
  <p>Kelola tautan penting yang tampil di website sekolah. Setiap tautan disimpan dan dihapus sendiri-sendiri.</p>
</div>

<div class="state-box" id="set-state" hidden></div>

<?php foreach ($kartu as $k => $c): ?>
<section class="card set-card" aria-labelledby="set-<?= $k ?>-title" style="margin-bottom:16px">
  <div class="set-head">
    <span class="set-head__ico"><?= $ico('layout') ?></span>
    <div>
      <h2 id="set-<?= $k ?>-title"><?= e($c['judul']) ?></h2>
      <p id="set-<?= $k ?>-status">Memuat...</p>
    </div>
  </div>
  <form id="form-<?= $k ?>" data-key="<?= $k ?>" novalidate>
    <div class="field">
      <label for="set-<?= $k ?>"><?= e($c['label']) ?></label>
      <input class="input" type="url" id="set-<?= $k ?>" inputmode="url" autocomplete="off" spellcheck="false"
             placeholder="<?= e($c['ph']) ?>" aria-describedby="set-<?= $k ?>-hint set-<?= $k ?>-err" disabled>
      <div class="field__hint" id="set-<?= $k ?>-hint"><?= e($c['hint']) ?></div>
      <div class="field__hint field__hint--ok" id="set-<?= $k ?>-conv" hidden></div>
      <div class="field__error" id="set-<?= $k ?>-err" hidden></div>
    </div>
    <div class="set-actions" style="display:flex;gap:10px;flex-wrap:wrap">
      <button type="submit" class="btn btn--amber" id="set-<?= $k ?>-save" disabled><?= e($c['simpan']) ?></button>
      <button type="button" class="btn btn--outline" id="set-<?= $k ?>-hapus" disabled>Hapus Tautan</button>
    </div>
  </form>
</section>
<?php endforeach; ?>

<div class="set-meta" id="set-meta" aria-live="polite">
  <?= $ico('info', 'ico--sm') ?>
  <span id="set-meta-text"><span class="skeleton"></span></span>
</div>
<div class="notice notice--warn" id="set-msg" role="alert" hidden></div>
