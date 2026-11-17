/* SI-KESA PWA Admin | Profil Akun: GET /api/auth/me (hanya baca). */
(function () {
  'use strict';
  var Api = window.SikesaApi;
  var $ = function (id) { return document.getElementById(id); };
  function ini(n) { var p = String(n || 'Admin').trim().split(/\s+/); return ((p[0] || '')[0] + ((p[1] || p[0] || '')[1] || '')).toUpperCase().slice(0, 2); }

  Api.get('/api/auth/me').then(function (res) {
    var d = (res && res.data) || {};
    var u = d.user || d;
    $('pf-username').textContent = u.username || '-';
    $('pf-user2').textContent = u.username || '-';
    $('pf-avatar').textContent = ini(u.username);
    $('pf-peran').textContent = u.role === 'admin' ? 'Administrator' : (u.role || '');
    $('pf-role').textContent = u.role === 'admin' ? 'Administrator' : (u.role || '-');
  }, function (err) {
    if (err && err.status === 401) return;
    var m = $('pf-msg'); m.hidden = false; m.textContent = (err && err.message) || 'Profil belum bisa dimuat.';
  });
})();
