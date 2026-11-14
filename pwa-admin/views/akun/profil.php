<div class="page__head">
  <h1>Profil Akun</h1>
  <p>Informasi akun yang sedang masuk.</p>
</div>

<div class="card" style="max-width:560px">
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
    <div id="pf-avatar" class="avatar" style="width:64px;height:64px;font-size:1.3rem">--</div>
    <div>
      <div id="pf-username" style="font-weight:700;font-size:1.15rem">Memuat...</div>
      <div id="pf-peran" style="color:var(--ink-3);font-size:.9rem">&nbsp;</div>
    </div>
  </div>
  <dl style="display:grid;grid-template-columns:140px 1fr;gap:10px 12px;margin:0 0 20px;font-size:.95rem">
    <dt style="color:var(--ink-3)">Username</dt><dd id="pf-user2" style="margin:0;font-weight:600">-</dd>
    <dt style="color:var(--ink-3)">Peran</dt><dd id="pf-role" style="margin:0;font-weight:600">-</dd>
    <dt style="color:var(--ink-3)">Sekolah</dt><dd style="margin:0;font-weight:600"><?= e($config['schoolName']) ?></dd>
  </dl>
  <div id="pf-msg" class="notice notice--warn" role="alert" hidden></div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn btn--primary" href="<?= e(url('akun') . '#password') ?>">Ganti Password</a>
    <button type="button" class="btn" data-action="logout">Keluar</button>
  </div>
  <p style="margin-top:14px;color:var(--ink-3);font-size:.85rem">Lupa password? Hubungi Admin lain untuk mereset passwordnya.</p>
</div>
