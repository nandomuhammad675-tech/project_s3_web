/*
 * SI-KESA PWA Guru: catatan kedisiplinan (TANPA DOM). Bergantung pada apiClient.js.
 * SENGAJA tidak ada fungsi hapus: catatan kedisiplinan tidak dapat dihapus (server membalas 405); perbaiki lewat ubah().
 * Tidak ada kolom tingkat pelanggaran maupun tindak lanjut.
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;

  function periksa(d, baru) {
    var e = {};
    if (baru && !d.id_siswa) e.id_siswa = 'Pilih siswa.';
    if (!d.tanggal || !/^\d{4}-\d{2}-\d{2}$/.test(d.tanggal)) e.tanggal = 'Tanggal wajib diisi.';
    if (!d.jenis_pelanggaran || !String(d.jenis_pelanggaran).trim()) e.jenis_pelanggaran = 'Jenis pelanggaran wajib diisi.';
    else if (String(d.jenis_pelanggaran).length > 100) e.jenis_pelanggaran = 'Jenis pelanggaran maksimal 100 karakter.';
    if (!d.catatan || !String(d.catatan).trim()) e.catatan = 'Catatan wajib diisi.';
    return e;
  }

  function tolak(e) {
    return Promise.reject(api.ApiError(422, e[Object.keys(e)[0]], e));
  }

  /** Satu halaman: { items: [{ id_catatan, id_siswa, nama_siswa, tanggal, jenis_pelanggaran, catatan }], page, per_page, total }. */
  function daftar(filter) {
    return api.get('/api/guru/disiplin', filter);
  }

  /** d: { id_siswa, tanggal, jenis_pelanggaran, catatan }. */
  function buat(d) {
    var e = periksa(d, true);
    return Object.keys(e).length ? tolak(e) : api.post('/api/guru/disiplin', d);
  }

  /** d: { tanggal, jenis_pelanggaran, catatan } (id_siswa tidak diubah dari sini). */
  function ubah(id, d) {
    var e = periksa(d, false);
    return Object.keys(e).length ? tolak(e) : api.put('/api/guru/disiplin/' + id, d);
  }

  global.SIKESA.disiplin = { daftar: daftar, buat: buat, ubah: ubah };
})(typeof window !== 'undefined' ? window : globalThis);
