<?php
/** Partial: keadaan gagal memuat. Variabel: $errorId, $errorPesan. Tombol "Coba lagi" memakai data-retry. */
?>
<div class="state state--error" id="<?= e($errorId ?? 'errorState') ?>" role="alert" hidden>
  <?= icon('circle-alert', 'i--lg') ?>
  <h2>Data belum bisa dimuat</h2>
  <p data-error-message><?= e($errorPesan ?? 'Periksa koneksi internet Anda, lalu coba lagi.') ?></p>
  <button type="button" class="btn btn--gold" data-retry>Coba lagi</button>
</div>