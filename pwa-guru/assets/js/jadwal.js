/*
 * SI-KESA PWA Guru: jadwal pelajaran kelas sendiri (TANPA DOM). Bergantung pada apiClient.js.
 * Tidak ada guru pengampu. Jadwal tidak disalin ke tahun ajaran baru (kelas baru mulai kosong).
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;
  var HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

  /** Pemeriksaan ringan; mengembalikan objek kesalahan per kolom ({} = lolos). Server tetap yang memutuskan. */
  function periksa(d) {
    var e = {};
    if (['ganjil', 'genap'].indexOf(d.semester) === -1) e.semester = 'Pilih semester.';
    if (HARI.indexOf(d.hari) === -1) e.hari = 'Pilih hari Senin sampai Sabtu.';
    if (!d.id_mapel) e.id_mapel = 'Pilih mata pelajaran.';
    var jam = /^([01]\d|2[0-3]):[0-5]\d$/;
    if (!jam.test(d.jam_mulai || '')) e.jam_mulai = 'Jam mulai harus berformat HH:MM.';
    if (!jam.test(d.jam_selesai || '')) e.jam_selesai = 'Jam selesai harus berformat HH:MM.';
    if (!e.jam_mulai && !e.jam_selesai && d.jam_selesai <= d.jam_mulai) e.jam_selesai = 'Jam selesai harus setelah jam mulai.';
    if (d.keterangan && String(d.keterangan).length > 100) e.keterangan = 'Keterangan maksimal 100 karakter.';
    return e;
  }

  function tolak(e) {
    var pesan = e[Object.keys(e)[0]];
    return Promise.reject(api.ApiError(422, pesan, e));
  }

  /** filter: { semester, hari }. Satu kelas memuat jauh di bawah 200 slot, jadi cukup satu permintaan. */
  function daftar(filter) {
    var f = { per_page: 200 };
    for (var k in (filter || {})) f[k] = filter[k];
    return api.get('/api/guru/jadwal', f).then(function (d) { return d.items; });
  }

  function detail(id) { return api.get('/api/guru/jadwal/' + id); }

  /** d: { semester, id_mapel, hari, jam_mulai, jam_selesai, keterangan? }. Slot kembar => ApiError 409. */
  function buat(d) {
    var e = periksa(d);
    return Object.keys(e).length ? tolak(e) : api.post('/api/guru/jadwal', d);
  }

  function ubah(id, d) {
    var e = periksa(d);
    return Object.keys(e).length ? tolak(e) : api.put('/api/guru/jadwal/' + id, d);
  }

  function hapus(id) { return api.del('/api/guru/jadwal/' + id); }

  /** Kelompokkan untuk tabel mingguan: { senin: [...urut jam_mulai], ..., sabtu: [...] } (hari kosong = []). */
  function perHari(items) {
    var h = {};
    HARI.forEach(function (x) { h[x] = []; });
    items.forEach(function (j) { if (h[j.hari]) h[j.hari].push(j); });
    HARI.forEach(function (x) { h[x].sort(function (a, b) { return a.jam_mulai < b.jam_mulai ? -1 : a.jam_mulai > b.jam_mulai ? 1 : 0; }); });
    return h;
  }

  global.SIKESA.jadwal = { HARI: HARI, periksa: periksa, daftar: daftar, detail: detail, buat: buat, ubah: ubah, hapus: hapus, perHari: perHari };
})(typeof window !== 'undefined' ? window : globalThis);
