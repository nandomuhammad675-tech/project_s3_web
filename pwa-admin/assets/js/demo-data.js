/* SI-KESA PWA Admin | DATA DEMO (hanya untuk pratinjau tanpa server).
 * Aktif bila membuka  /dashboard?demo=1 . Hapus berkas ini di produksi. */
(function () {
  'use strict';
  var state = { semester: 'ganjil' };

  var kelas = [
    ['1A', 'Siti Aminah, S.Pd.', true,  '07.42'],
    ['1B', 'Nur Hidayati, S.Pd.', true, '07.48'],
    ['2A', 'Rina Wulandari, S.Pd.', true, '07.51'],
    ['3A', 'Dwi Lestari, S.Pd.', true,  null],
    ['4A', 'Hendra Wijaya, S.Pd.', false, '08.03'],
    ['5A', 'Bambang Susilo, S.Pd.', true, '07.55'],
    ['6A', 'Eko Prasetyo, S.Pd.', true, '07.39'],
    ['6B', 'Ahmad Fauzi, S.Pd.', true,  null]
  ];

  function res(data) { return new Promise(function (r) { setTimeout(function () { r(data); }, 450); }); }
  function rej(status, message) { return new Promise(function (_, r) { setTimeout(function () { r(new window.SikesaApi.ApiError(status, message)); }, 450); }); }

  window.SikesaDemo = {
    handle: function (method, path, q, body) {
      if (path === '/api/admin/tahun-ajaran' && method === 'GET') {
        return res({ data: [{ id: 1, nama_tahun: '2026/2027', semester_berjalan: state.semester, status: 1 }] });
      }
      if (/^\/api\/admin\/tahun-ajaran\/\d+$/.test(path) && method === 'PUT') {
        if (body && body.konfirmasi_semester === true && body.semester_aktif === 'genap') {
          state.semester = 'genap';
          return res({ message: 'ok' });
        }
        return rej(422, 'Konfirmasi ganti semester diperlukan');
      }
      if (path === '/api/admin/mata-pelajaran') return res({ data: [] });
      if (path === '/api/admin/guru') return res({ data: kelas.filter(function (k) { return k[1]; }).map(function (k, i) { return { id: i + 1, nama_guru: k[1] }; }) });
      if (path === '/api/admin/kelas') {
        return res({ data: kelas.map(function (k, i) {
          return { id: i + 1, id_tahun_ajaran: 1, nama_kelas: k[0], nama_wali: k[1], wali_aktif: k[2] ? 1 : 0 };
        }) });
      }
      if (path === '/api/admin/dasbor/absensi') {
        var d = q.date ? q.date.split('-').map(Number) : null;
        var minggu = d && new Date(d[0], d[1] - 1, d[2]).getDay() === 0;
        return res({ data: kelas.map(function (k) {
          var status = minggu ? 'libur' : (k[0] === '6B' ? 'libur' : (k[3] ? 'sudah' : 'belum'));
          return {
            nama_kelas: k[0], wali_kelas: k[2] ? k[1] : null, wali_aktif: k[2],
            status: status,
            terakhir_diperbarui: status === 'sudah' ? k[3] : null,
            keterangan: minggu ? 'Hari Minggu' : (status === 'libur' ? 'Libur khusus kelas' : null)
          };
        }) });
      }
      return rej(404, 'Data tidak ditemukan');
    }
  };
})();