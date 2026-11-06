<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="page__head">
  <h1>Program Pembiasaan</h1>
  <p>Program rutin sekolah dari Senin hingga Sabtu.</p>
</div>

<section class="pb-grid" id="pb-grid" aria-label="Daftar program pembiasaan" aria-live="polite">
  <?php for ($i = 0; $i < 6; $i++): ?>
  <div class="pb-card pb-card--skeleton"><span class="skeleton skeleton--row"></span></div>
  <?php endfor; ?>
</section>

<div class="state-box" id="pb-state" hidden></div>

<?php require __DIR__ . '/form.php'; ?>