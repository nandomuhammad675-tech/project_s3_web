<?php
/** Partial: keadaan kosong. Variabel: $emptyJudul (wajib), $emptyPesan (opsional) */
?>
<div class="container">
  <div class="state state--empty">
    <?= icon('inbox', 'i--lg') ?>
    <h2><?= e($emptyJudul ?? 'Belum ada data') ?></h2>
    <p><?= e($emptyPesan ?? '') ?></p>
  </div>
</div>