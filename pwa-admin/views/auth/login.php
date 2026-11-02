<main class="login">
  <div class="login__card">
    <div class="login__brand">
      <img src="<?= e(url('assets/icons/icon-192.png')) ?>" alt="" width="48" height="48">
      <div>
        <div class="login__app"><?= e($config['appName']) ?> Admin</div>
        <div class="login__school"><?= e($config['schoolName']) ?></div>
      </div>
    </div>
    <h1 class="login__title">Masuk</h1>
    <p class="login__sub">Gunakan akun Admin untuk membuka panel.</p>

    <div class="notice notice--warn" id="login-msg" role="alert" hidden></div>

    <form id="form-login" novalidate>
      <div class="field">
        <label for="username">Username</label>
        <input class="input" id="username" name="username" type="text" autocomplete="username" maxlength="50" required autofocus>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <div class="input-group">
          <input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
          <button type="button" class="input-group__btn" id="toggle-pass" aria-pressed="false">Tampilkan</button>
        </div>
      </div>
      <button class="btn btn--primary btn--block" id="btn-login" type="submit" disabled>Masuk</button>
    </form>
  </div>
</main>