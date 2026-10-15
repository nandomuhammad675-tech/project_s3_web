<?php
/**
 * Partial: kerangka pemuatan (skeleton)
 * Variabel: $loadingBaris (jumlah baris, default 3)
 */
$n = max(1, (int) ($loadingBaris ?? 3));
?>
<div class="skeleton" aria-hidden="true">
<?php for ($i = 0; $i < $n; $i++): ?>
  <span class="sk-line<?= $i === 0 ? ' sk-line--short' : '' ?>"></span>
<?php endfor; ?>
</div>
