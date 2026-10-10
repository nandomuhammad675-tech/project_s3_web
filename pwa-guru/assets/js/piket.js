/*
 * SI-KESA PWA Guru: piket kebersihan kelas sendiri, berulang per HARI (TANPA DOM). Bergantung pada apiClient.js.
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;
  var HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

  /** => [{ id_piket, hari, keterangan, siswa: [{ id_siswa, nama_siswa }] }] (siswa non-aktif sudah disaring server). */
  function daftar() {
    return api.get('/api/guru/piket').then(function (d) { return d.items; });
  }

  /** d: { hari, keterangan?, id_siswa: [ids] }. Hari yang sudah punya piket => ApiError 409 (gunakan ubah). */
  function buat(d) { return api.post('/api/guru/piket', d); }

  function ubah(id, d) { return api.put('/api/guru/piket/' + id, d); }

  /** Menghapus piket ikut menghapus daftar siswanya. */
  function hapus(id) { return api.del('/api/guru/piket/' + id); }

  /** Enam kartu hari: { senin: piket|null, ..., sabtu: piket|null }. */
  function perHari(items) {
    var h = {};
    HARI.forEach(function (x) { h[x] = null; });
    items.forEach(function (p) { if (p.hari in h) h[p.hari] = p; });
    return h;
  }

  global.SIKESA.piket = { HARI: HARI, daftar: daftar, buat: buat, ubah: ubah, hapus: hapus, perHari: perHari };
})(typeof window !== 'undefined' ? window : globalThis);
