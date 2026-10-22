/*
 * SI-KESA PWA Guru: absensi harian + status absensi kelas (TANPA DOM). Bergantung pada apiClient.js.
 * Klien TIDAK mengirim id_kelas/id_tahun_ajaran/semester/id_guru: server mengisinya.
 */
(function (global) {
  'use strict';

  var api = global.SIKESA.api;
  var STATUS = ['hadir', 'sakit', 'izin', 'alpa'];

  function dua(n) { return (n < 10 ? '0' : '') + n; }

  /** Tanggal hari ini di perangkat, format YYYY-MM-DD. */
  function hariIni() {
    var d = new Date();
    return d.getFullYear() + '-' + dua(d.getMonth() + 1) + '-' + dua(d.getDate());
  }

  /** Hari Minggu (non-efektif otomatis). Sabtu efektif. Server tetap menolak 422 bila dilanggar. */
  function adalahMinggu(tanggal) {
    var p = String(tanggal).split('-');
    return new Date(Date.UTC(+p[0], +p[1] - 1, +p[2])).getUTCDay() === 0;
  }

  /**
   * GET /api/guru/absensi?date= => { tanggal, status_kelas: sudah|belum|libur,
   *   items: [{ id_siswa, nama_siswa, nomor_absen, id_absensi|null, status|null }] }
   * Bila status_kelas === 'libur', tampilan menonaktifkan tombol simpan.
   */
  function lihat(tanggal) {
    return api.get('/api/guru/absensi', { date: tanggal });
  }

  /** GET /api/guru/dasbor/absensi => { tanggal, id_kelas, nama_kelas, status }. */
  function statusKelas(tanggal) {
    return api.get('/api/guru/dasbor/absensi', { date: tanggal });
  }

  /** Hitung ringkasan "Hadir 28, Sakit 1, Izin 1, Alpa 0" dari pilihan { id_siswa: status } atau daftar baris. */
  function ringkasan(pilihan, jumlahSiswa) {
    var hasil = { hadir: 0, sakit: 0, izin: 0, alpa: 0, belumDiisi: 0 };
    var nilai = Array.isArray(pilihan)
      ? pilihan.map(function (r) { return r.status; })
      : Object.keys(pilihan || {}).map(function (k) { return pilihan[k]; });
    nilai.forEach(function (s) { if (STATUS.indexOf(s) !== -1) hasil[s]++; });
    var terisi = hasil.hadir + hasil.sakit + hasil.izin + hasil.alpa;
    hasil.belumDiisi = typeof jumlahSiswa === 'number' ? Math.max(0, jumlahSiswa - terisi) : 0;
    return hasil;
  }

  /** Tombol "Semua Hadir": pilihan { id_siswa: 'hadir' } untuk seluruh baris. */
  function semuaHadir(baris) {
    var p = {};
    baris.forEach(function (r) { p[r.id_siswa] = 'hadir'; });
    return p;
  }

  /**
   * Simpan absensi satu tanggal, baru maupun koreksi, sehingga bagi pengguna menyimpan dua kali tetap berhasil.
   *   awal    = items dari lihat(tanggal)         (membawa id_absensi dan status yang tersimpan)
   *   pilihan = { id_siswa: 'hadir'|'sakit'|'izin'|'alpa' } hasil pilihan Guru
   * Server tidak menimpa lewat POST, jadi: siswa tanpa catatan => satu POST (batch); siswa bercatatan yang
   * statusnya berubah => PUT per catatan; yang tidak berubah dilewati.
   * Mengembalikan { baru, diperbarui, tanpaPerubahan, gagal: [{ id_siswa, pesan }] }.
   * POST yang ditolak (mis. 422 hari libur) melempar ApiError; kegagalan PUT dikumpulkan di `gagal`.
   */
  function simpan(tanggal, awal, pilihan) {
    var baru = [], ubah = [], sama = 0;
    var peta = {};
    awal.forEach(function (r) { peta[r.id_siswa] = r; });

    for (var k in pilihan) {
      var id = Number(k), status = pilihan[k], r = peta[id];
      if (!r) return Promise.reject(api.ApiError(422, 'Siswa ' + id + ' tidak ada di daftar kelas.', { id_siswa: String(id) }));
      if (STATUS.indexOf(status) === -1) {
        return Promise.reject(api.ApiError(422, 'Status kehadiran tidak valid.', { status: 'Pilih hadir, sakit, izin, atau alpa.' }));
      }
      if (r.id_absensi === null || r.id_absensi === undefined) baru.push({ id_siswa: id, status: status });
      else if (r.status !== status) ubah.push({ id_siswa: id, id_absensi: r.id_absensi, status: status });
      else sama++;
    }

    var hasil = { baru: 0, diperbarui: 0, tanpaPerubahan: sama, gagal: [] };
    var langkah = Promise.resolve();

    if (baru.length) {
      langkah = langkah.then(function () {
        return api.post('/api/guru/absensi', { tanggal: tanggal, items: baru }).then(function () { hasil.baru = baru.length; });
      });
    }
    ubah.forEach(function (u) {
      langkah = langkah.then(function () {
        return api.put('/api/guru/absensi/' + u.id_absensi, { status: u.status }).then(
          function () { hasil.diperbarui++; },
          function (e) { hasil.gagal.push({ id_siswa: u.id_siswa, pesan: e.message }); }
        );
      });
    });
    return langkah.then(function () { return hasil; });
  }

  global.SIKESA.absensi = {
    STATUS: STATUS, hariIni: hariIni, adalahMinggu: adalahMinggu, lihat: lihat, statusKelas: statusKelas,
    ringkasan: ringkasan, semuaHadir: semuaHadir, simpan: simpan
  };
})(typeof window !== 'undefined' ? window : globalThis);
