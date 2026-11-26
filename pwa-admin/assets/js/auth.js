/* SI-KESA PWA Admin | sesi & layar login */
(function () {
  'use strict';
  var Api = window.SikesaApi;
  var CFG = window.SIKESA;

  function url(p) { return (CFG.base || '') + '/' + p.replace(/^\//, ''); }

  var Auth = {
    token: function () { return localStorage.getItem(Api.TOKEN_KEY); },
    user: function () {
      try { return JSON.parse(localStorage.getItem(Api.USER_KEY) || 'null'); } catch (e) { return null; }
    },
    save: function (token, user) {
      localStorage.setItem(Api.TOKEN_KEY, token);
      localStorage.setItem(Api.USER_KEY, JSON.stringify(user || {}));
    },
    clear: function () {
      localStorage.removeItem(Api.TOKEN_KEY);
      localStorage.removeItem(Api.USER_KEY);
      sessionStorage.removeItem(Api.DEMO_KEY);
    },
    /** 401: hapus token lokal, pindah ke login dengan pesan. */
    expire: function () {
      Auth.clear();
      sessionStorage.setItem('sikesa_login_msg', 'Sesi berakhir, silakan masuk lagi');
      if (CFG.route !== 'login') window.location.replace(url('login'));
    },
    logout: function () {
      Auth.clear();
      window.location.replace(url('login'));
    }
  };
  window.SikesaAuth = Auth;

  // Mode demo (tanpa server): buka /dashboard?demo=1
  if (new URLSearchParams(window.location.search).get('demo') === '1') {
    sessionStorage.setItem(Api.DEMO_KEY, '1');
    Auth.save('demo-token', { username: 'admin', nama: 'Admin Rahayu', role: 'admin' });
  }

  // Penjaga halaman (kenyamanan saja; server yang menolak sebenarnya)
  if (CFG.requireAuth) {
    var u = Auth.user();
    if (!Auth.token() || (u && u.role && u.role !== 'admin')) {
      Auth.clear();
      window.location.replace(url('login'));
      return;
    }
  } else if (CFG.route === 'login' && Auth.token()) {
    window.location.replace(url('dashboard'));
    return;
  }

  /* ---------- Layar login ---------- */
  var form = document.getElementById('form-login');
  if (!form) return;

  var elUser = document.getElementById('username');
  var elPass = document.getElementById('password');
  var btn = document.getElementById('btn-login');
  var msg = document.getElementById('login-msg');
  var toggle = document.getElementById('toggle-pass');
  var busy = false;

  function showMsg(text) { msg.textContent = text; msg.hidden = !text; }
  function refresh() { btn.disabled = busy || !elUser.value.trim() || !elPass.value; }

  var pending = sessionStorage.getItem('sikesa_login_msg');
  if (pending) { showMsg(pending); sessionStorage.removeItem('sikesa_login_msg'); }

  elUser.addEventListener('input', refresh);
  elPass.addEventListener('input', refresh);
  toggle.addEventListener('click', function () {
    var show = elPass.type === 'password';
    elPass.type = show ? 'text' : 'password';
    toggle.textContent = show ? 'Sembunyikan' : 'Tampilkan';
    toggle.setAttribute('aria-pressed', String(show));
  });

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (busy || btn.disabled) return;
    busy = true; refresh(); btn.textContent = 'Memproses...'; showMsg('');

    Api.post(CFG.endpoints.login, { username: elUser.value.trim(), password: elPass.value }, { noAuthRedirect: true })
      .then(function (res) {
        var d = (res && res.data) || res || {};
        var token = d.token || d.access_token;
        var user = d.user || d;
        var role = String(user.role || d.role || '').toLowerCase();

        if (!token) throw new Api.ApiError(500, 'Terjadi gangguan, coba lagi');
        if (role !== 'admin') {
          // Guru masuk lewat PWA Admin: tolak dan arahkan ke PWA Guru. Token tidak disimpan.
          showMsg('Akun ini bukan Admin. Anda akan diarahkan ke aplikasi Guru...');
          setTimeout(function () { window.location.replace(CFG.guruUrl); }, 1800);
          return;
        }
        Auth.save(token, { username: user.username || elUser.value.trim(), nama: user.nama || user.name || '', role: 'admin' });
        window.location.replace(url('dashboard'));
      })
      .catch(function (err) {
        showMsg(err && err.message ? err.message : 'Terjadi gangguan, coba lagi');
        elPass.value = '';
        elPass.focus();
      })
      .then(function () {
        busy = false; btn.textContent = "Masuk"; refresh();
      });
  });
  refresh();
})();