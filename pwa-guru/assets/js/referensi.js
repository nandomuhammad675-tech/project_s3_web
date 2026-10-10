/*
 * SI-KESA PWA Guru: data rujukan untuk pilihan di form (TANPA DOM). Bergantung pada apiClient.js.
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;

  /** Siswa aktif kelas sendiri: { kelas, total, items: [{id_siswa, nama_siswa, nisn_resmi, nomor_absen}] }. */
  function siswa() {
    return api.get('/api/guru/siswa');
  }

  /** Mata pelajaran (read-only): [{id_mapel, kode_mapel, nama_mapel}]. */
  function mapel() {
    return api.get('/api/guru/mapel', { per_page: 200 }).then(function (d) { return d.items; });
  }

  global.SIKESA.referensi = { siswa: siswa, mapel: mapel };
})(typeof window !== 'undefined' ? window : globalThis);
