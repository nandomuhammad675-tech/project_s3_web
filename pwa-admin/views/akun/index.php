<?php
$ico = fn(string $n, string $cls = '') => '<svg class="ico ' . $cls . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
$pass = function (string $id, string $label, string $auto, string $hint = '') {
    return '<div class="field"><label for="' . $id . '">' . $label . '</label>' .
        '<div class="input-group"><input class="input" id="' . $id . '" type="password" autocomplete="' . $auto . '" required' .
        ($hint ? ' aria-describedby="' . $id . '-hint ' . $id . '-err"' : ' aria-describedby="' . $id . '-err"') . '>' .
        '<button type="button" class="input-group__btn" data-toggle-pass="' . $id . '" aria-pressed="false">Tampilkan</button></div>' .
        ($hint ? '<div class="field__hint" id="' . $id . '-hint">' . $hint . '</div>' : '') .
        '<div class="field__error" id="' . $id . '-err" hidden></div></div>';
};
?>
<div class="page__head page__head--row">
  <div>
    <h1>Akun</h1>
    <p>Kelola akses admin dan keamanan akun Anda.</p>
  </div>
  <button type="button" class="btn btn--amber" id="ac-btn-add"><?= $ico('plus', 'ico--sm') ?> Tambah Admin</button>
</div>

<div class="ac-tabs" role="tablist" aria-label="Pengelolaan akun">
  <button type="button" class="ac-tab" role="tab" id="tab-akun" data-tab="akun" aria-controls="panel-akun" aria-selected="true">Kelola Akun</button>
  <button type="button" class="ac-tab" role="tab" id="tab-password" data-tab="password" aria-controls="panel-password" aria-selected="false" tabindex="-1">Ganti Password Saya</button>
</div>

<!-- Tab 1: Kelola Akun -->
<section class="table-card" id="panel-akun" role="tabpanel" aria-labelledby="tab-akun">
  <div class="table-wrap">
    <table class="table table--akun" id="ac-table">
      <thead>
        <tr>
          <th scope="col">Username</th>
          <th scope="col">Role</th>
          <th scope="col">Nama</th>
          <th scope="col">Status</th>
          <th scope="col">Terakhir Aktif</th>
          <th scope="col">Aksi</th>
        </tr>
      </thead>
      <tbody id="ac-body">
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
        <tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>
      </tbody>
    </table>
  </div>

  <div class="state-box" id="ac-state" hidden></div>

  <div class="pager-bar" id="ac-footer" hidden>
    <span id="ac-count"></span>
    <nav class="pager" id="ac-pager" aria-label="Halaman daftar"></nav>
  </div>

  <div class="table-note"><?= $ico('lock', 'ico--sm') ?> Saat password direset: “Password diganti. Sesi lama akun ini berakhir.”</div>
</section>

<!-- Tab 2: Ganti Password Saya -->
<section class="card ac-pw" id="panel-password" role="tabpanel" aria-labelledby="tab-password" hidden>
  <h2>Ganti Password Saya</h2>
  <p class="ac-pw__lead">Gunakan password yang sulit ditebak. Setelah berhasil, Anda tetap masuk di perangkat ini.</p>
  <form id="ac-form-pw" novalidate>
    <?= $pass('ac-pw-old', 'Password Lama', 'current-password') ?>
    <?= $pass('ac-pw-new', 'Password Baru', 'new-password', 'Minimal 8 karakter.') ?>
    <?= $pass('ac-pw-new2', 'Ulangi Password Baru', 'new-password') ?>
    <div class="notice notice--warn" id="ac-pw-msg" role="alert" hidden></div>
    <div class="ac-pw__actions">
      <button type="submit" class="btn btn--primary" id="ac-pw-save">Simpan Password</button>
    </div>
  </form>
</section>

<?php require __DIR__ . '/form.php'; ?>