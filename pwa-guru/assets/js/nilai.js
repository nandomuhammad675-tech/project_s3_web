/*
 * SI-KESA PWA Guru: nilai UH/ASTS/ASAS (TANPA DOM). Bergantung pada apiClient.js.
 * Aturan server: nilai bulat 0-100; UH wajib nomor_uh; ASTS/ASAS tanpa nomor_uh; hanya UH yang boleh dihapus.
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;
  var JENIS = ['UH', 'ASTS', 'ASAS'];

  function galat(pesan, kolom) {
    var e = {}; e[kolom] = pesan;
    return api.ApiError(422, pesan, e);
  }

  /** Satu halaman: { items, page, per_page, total }. filter: { id_mapel, semester, id_siswa, page, per_page }. */
  function daftar(filter) {
    return api.get('/api/guru/nilai', filter);
  }

  /** Semua baris (menelusuri halaman) untuk filter tertentu; server membatasi 200 per halaman. */
  function semua(filter) {
    var hasil = [];
    function ambil(page) {
      var f = {};
      for (var k in (filter || {})) f[k] = filter[k];
      f.page = page; f.per_page = 200;
      return daftar(f).then(function (d) {
        hasil = hasil.concat(d.items);
        return hasil.length < d.total && d.items.length > 0 ? ambil(page + 1) : hasil;
      });
    }
    return ambil(1);
  }

  /** data: { id_siswa, id_mapel, semester, jenis, nomor_uh?, nilai }. Pemeriksaan ringan di klien; server yang memutuskan. */
  function buat(data) {
    var d = { id_siswa: data.id_siswa, id_mapel: data.id_mapel, semester: data.semester, jenis: data.jenis, nilai: data.nilai };
    if (JENIS.indexOf(d.jenis) === -1) return Promise.reject(galat('Pilih jenis penilaian: UH, ASTS, atau ASAS.', 'jenis'));
    if (!Number.isInteger(d.nilai) || d.nilai < 0 || d.nilai > 100) return Promise.reject(galat('Nilai harus antara 0 dan 100.', 'nilai'));
    if (d.jenis === 'UH') {
      if (!Number.isInteger(data.nomor_uh) || data.nomor_uh < 1) return Promise.reject(galat('Nomor UH wajib diisi.', 'nomor_uh'));
      d.nomor_uh = data.nomor_uh;
    }
    return api.post('/api/guru/nilai', d);
  }

  /** Perbaiki angka (hanya angkanya yang dapat diubah). */
  function perbaiki(id, nilai) {
    if (!Number.isInteger(nilai) || nilai < 0 || nilai > 100) return Promise.reject(galat('Nilai harus antara 0 dan 100.', 'nilai'));
    return api.put('/api/guru/nilai/' + id, { nilai: nilai });
  }

  /** Tombol hapus hanya untuk baris UH; ASTS/ASAS tidak ada tombolnya. */
  function bisaDihapus(item) {
    return item.jenis === 'UH';
  }

  function hapus(id) {
    return api.del('/api/guru/nilai/' + id);
  }

  /** Nomor UH berikutnya untuk saran isi otomatis (maksimum pada daftar + 1). */
  function nomorUhBerikutnya(items) {
    var maks = 0;
    items.forEach(function (n) { if (n.jenis === 'UH' && n.nomor_uh > maks) maks = n.nomor_uh; });
    return maks + 1;
  }

  /**
   * Pivot rekap untuk SATU mapel dan semester: { kolomUh: [1..n], baris: [{ id_siswa, nama_siswa,
   *   uh: { nomor: {id_nilai, nilai} }, asts: {id_nilai, nilai}|null, asas: ... }] }.
   */
  function rekapPerSiswa(items) {
    var peta = {}, urut = [], maks = 0;
    items.forEach(function (n) {
      var b = peta[n.id_siswa];
      if (!b) { b = peta[n.id_siswa] = { id_siswa: n.id_siswa, nama_siswa: n.nama_siswa, uh: {}, asts: null, asas: null }; urut.push(b); }
      var sel = { id_nilai: n.id_nilai, nilai: n.nilai };
      if (n.jenis === 'UH') { b.uh[n.nomor_uh] = sel; if (n.nomor_uh > maks) maks = n.nomor_uh; }
      else b[n.jenis.toLowerCase()] = sel;
    });
    var kolom = [];
    for (var i = 1; i <= maks; i++) kolom.push(i);
    return { kolomUh: kolom, baris: urut };
  }

  global.SIKESA.nilai = {
    JENIS: JENIS, daftar: daftar, semua: semua, buat: buat, perbaiki: perbaiki, hapus: hapus,
    bisaDihapus: bisaDihapus, nomorUhBerikutnya: nomorUhBerikutnya, rekapPerSiswa: rekapPerSiswa
  };
})(typeof window !== 'undefined' ? window : globalThis);
