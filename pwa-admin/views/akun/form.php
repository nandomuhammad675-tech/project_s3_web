<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
$pass = function (string $id, string $label, string $auto, string $hint = '') {
    return '<div class="field"><label for="' . $id . '">' . $label . '</label>' .
        '<div class="input-group"><input class="input" id="' . $id . '" type="password" autocomplete="' . $auto . '" required aria-describedby="' . $id . '-err">' .
        '<button type="button" class="input-group__btn" data-toggle-pass="' . $id . '" aria-pressed="false">Tampilkan</button></div>' .
        ($hint ? '<div class="field__hint">' . $hint . '</div>' : '') .
        '<div class="field__error" id="' . $id . '-err" hidden></div></div>';
};
?>
<!-- Tambah Admin -->
<dialog class="dialog" id="ac-dlg-add" aria-labelledby="ac-add-title">
  <form id="ac-form-add" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="ac-add-title">Tambah Admin</h2>
    <p class="dialog__lead">Akun baru akan memiliki role Admin. Akun Guru dibuat lewat menu Data Master &rsaquo; Guru.</p>

    <div class="field">
      <label for="ac-add-user">Username</label>
      <input class="input" type="text" id="ac-add-user" maxlength="50" required autocomplete="off" aria-describedby="ac-add-user-err">
      <div class="field__error" id="ac-add-user-err" hidden></div>
    </div>
    <?= $pass('ac-add-pass', 'Password', 'new-password', 'Minimal 8 karakter.') ?>
    <div class="field">
      <label>Role</label>
      <div class="ac-role-fixed"><span class="pill pill--admin">Admin</span> <span class="field__hint">Role tetap, tidak dapat diubah.</span></div>
    </div>

    <div class="notice notice--warn" id="ac-add-msg" role="alert" hidden></div>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="ac-add-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="ac-add-save">Simpan</button>
    </div>
  </form>
</dialog>

<!-- Reset password -->
<dialog class="dialog" id="ac-dlg-reset" aria-labelledby="ac-rs-title">
  <form id="ac-form-reset" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="ac-rs-title">Reset Password</h2>
    <p class="dialog__lead">Masukkan password baru untuk akun berikut. Sesi lama akun ini akan berakhir.</p>
    <p class="dialog__target" id="ac-rs-target"></p>
    <div style="height:14px"></div>
    <?= $pass('ac-rs-pass', 'Password Baru', 'new-password', 'Minimal 8 karakter.') ?>
    <div class="notice notice--warn" id="ac-rs-msg" role="alert" hidden></div>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="ac-rs-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="ac-rs-save">Reset Password</button>
    </div>
  </form>
</dialog>

<!-- Nonaktifkan / Aktifkan -->
<dialog class="dialog" id="ac-dlg-status" aria-labelledby="ac-st-title">
  <div class="dialog__body">
    <h2 class="dialog__title" id="ac-st-title">Nonaktifkan Akun</h2>
    <p class="dialog__text" id="ac-st-text"></p>
    <p class="dialog__target" id="ac-st-target"></p>
    <div class="notice notice--info" id="ac-st-msg" role="alert" hidden></div>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="ac-st-cancel">Batal</button>
      <button type="button" class="btn btn--danger" id="ac-st-ok">Nonaktifkan</button>
    </div>
  </div>
</dialog>