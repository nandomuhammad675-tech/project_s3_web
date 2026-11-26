<?php $delId = $delId ?? 'dlg-hapus'; ?>
<dialog class="dialog" id="<?= e($delId) ?>" aria-labelledby="<?= e($delId) ?>-title">
  <div class="dialog__body">
    <h2 class="dialog__title" id="<?= e($delId) ?>-title">Hapus data</h2>
    <p class="dialog__text">Yakin ingin menghapus? Tindakan ini tidak dapat dibatalkan.</p>
    <p class="dialog__target" id="<?= e($delId) ?>-target"></p>
    <div class="notice notice--info" id="<?= e($delId) ?>-msg" role="alert" hidden></div>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="<?= e($delId) ?>-cancel">Batal</button>
      <button type="button" class="btn btn--danger" id="<?= e($delId) ?>-ok">Hapus</button>
    </div>
  </div>
</dialog>