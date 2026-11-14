/* SI-KESA PWA Guru | halaman Profil: data dari GET /api/guru/me (hanya baca). */
(function () {
  'use strict';
  var S = window.SIKESA, U = S.ui;
  var $ = function (id) { return document.getElementById(id); };
  S.me.then(function (me) {
    if (!me) return;
    var g = me.guru || {}, k = me.kelas, ta = me.tahun_ajaran;
    $('akNama').textContent = g.nama_guru || '-';
    $('akNamaIn').value = g.nama_guru || '';
    $('akInisial').textContent = U.inisial(g.nama_guru);
    $('akUser').value = g.username || '';
    $('akPeran').textContent = k ? 'Wali Kelas ' + k.nama_kelas : 'Belum menjadi wali kelas';
    $('akKelas').value = k ? k.nama_kelas : 'Belum ditetapkan';
    $('akTa').value = ta ? ta.nama_tahun + ' \u2022 ' + U.cap(ta.semester_aktif) : 'Belum ada tahun ajaran aktif';
  }).catch(function () { /* galat ditampilkan app.js */ });
})();
