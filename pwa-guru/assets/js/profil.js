/*
 * SI-KESA PWA Guru: profil Guru dan ganti password (TANPA DOM). Bergantung pada apiClient.js.
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;

  /**
   * GET /api/guru/me => { guru, tahun_ajaran, kelas }.
   * kelas === null: Guru belum menjadi wali kelas pada tahun ajaran aktif (tampilkan pesan, nonaktifkan menu lain).
   */
  function me() {
    return api.get('/api/guru/me');
  }

  /** Ganti password sendiri; token baru dari server menggantikan token lama (token lama langsung tidak berlaku). */
  function gantiPassword(lama, baru, ulangi) {
    var err = {};
    if (!lama) err.password_lama = 'Password lama wajib diisi.';
    if (!baru || String(baru).length < 8) err.password_baru = 'Password baru minimal 8 karakter.';
    if (baru !== ulangi) err.ulangi = 'Pengulangan password tidak sama.';
    if (Object.keys(err).length) {
      return Promise.reject(api.ApiError(422, Object.keys(err).map(function (k) { return err[k]; })[0], err));
    }
    return api.put('/api/auth/password', { password_lama: lama, password_baru: baru }).then(function (data) {
      api.simpanToken(data.token);
      return true;
    });
  }

  global.SIKESA.profil = { me: me, gantiPassword: gantiPassword };
})(typeof window !== 'undefined' ? window : globalThis);
