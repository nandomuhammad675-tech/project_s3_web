/*
 * SI-KESA PWA Guru: laporan semester kelas sendiri (TANPA DOM). Bergantung pada apiClient.js.
 * Hanya tahun ajaran AKTIF (id dari profil.me().tahun_ajaran.id_tahun_ajaran); selain itu server membalas 403.
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;

  function periksa(p) {
    if (['ganjil', 'genap'].indexOf(p.semester) === -1) return api.ApiError(422, 'Pilih semester.', { semester: 'Pilih semester.' });
    if (!p.tahunAjaran) return api.ApiError(422, 'Tahun ajaran aktif belum tersedia.', { tahun_ajaran: 'Wajib.' });
    return null;
  }

  /** Pratinjau JSON: { meta, kelas: [{ absensi: [...], nilai: [...] }] }. p: { semester, tahunAjaran }. */
  function preview(p) {
    var e = periksa(p);
    if (e) return Promise.reject(e);
    return api.get('/api/guru/laporan', { semester: p.semester, tahun_ajaran: p.tahunAjaran });
  }

  /** Unduh XLSX => { blob, namaFile }. p: { semester, tahunAjaran, jenis?: 'absensi'|'nilai'|'semua' }. */
  function unduh(p) {
    var e = periksa(p);
    if (e) return Promise.reject(e);
    return api.unduh('/api/guru/laporan/excel', { semester: p.semester, tahun_ajaran: p.tahunAjaran, jenis: p.jenis });
  }

  /** Unduh lalu langsung disimpan oleh browser. Mengembalikan nama berkas. */
  function unduhDanSimpan(p) {
    return unduh(p).then(function (r) { api.simpanBlob(r.blob, r.namaFile); return r.namaFile; });
  }

  global.SIKESA.laporan = { preview: preview, unduh: unduh, unduhDanSimpan: unduhDanSimpan };
})(typeof window !== 'undefined' ? window : globalThis);
