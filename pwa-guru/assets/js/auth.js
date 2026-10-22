/*
 * SI-KESA PWA Guru: login/logout dan status sesi (TANPA DOM). Bergantung pada apiClient.js.
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;

  /** Baca waktu kedaluwarsa (detik epoch) dari token JWT tanpa verifikasi; null bila tak terbaca. */
  function expToken(token) {
    try {
      var bagian = String(token).split('.');
      if (bagian.length < 2) return null;
      var b64 = bagian[1].replace(/-/g, '+').replace(/_/g, '/');
      while (b64.length % 4) b64 += '=';
      var payload = JSON.parse(decodeURIComponent(escape(global.atob(b64))));
      return typeof payload.exp === 'number' ? payload.exp : null;
    } catch (e) {
      return null;
    }
  }

  /**
   * Login Guru. Akun ber-role selain "guru" ditolak (dan token TIDAK disimpan): tampilan mengarahkan ke PWA Admin.
   * Mengembalikan { id_user, username, role }.
   */
  function masuk(username, password) {
    username = String(username || '').trim();
    if (!username || !password) {
      return Promise.reject(api.ApiError(422, 'Username dan password wajib diisi.', { username: 'Wajib diisi.' }));
    }
    return api.request('POST', '/api/auth/login', { auth: false, body: { username: username, password: password } })
      .then(function (data) {
        if (!data.user || data.user.role !== 'guru') {
          throw api.ApiError(403, 'Akun ini bukan akun Guru. Gunakan aplikasi Admin.');
        }
        api.simpanToken(data.token);
        return data.user;
      });
  }

  /** Logout: beri tahu server (tidak mencabut token) lalu SELALU hapus token lokal. */
  function keluar() {
    return api.request('POST', '/api/auth/logout').then(null, function () { /* abaikan */ }).then(function () {
      api.hapusToken();
    });
  }

  /** Ada token yang belum kedaluwarsa menurut jam perangkat (server tetap yang memutuskan). */
  function adalahMasuk() {
    var t = api.ambilToken();
    if (!t) return false;
    var exp = expToken(t);
    return exp === null || exp * 1000 > Date.now();
  }

  global.SIKESA.auth = { masuk: masuk, keluar: keluar, adalahMasuk: adalahMasuk, expToken: expToken };
})(typeof window !== 'undefined' ? window : globalThis);
