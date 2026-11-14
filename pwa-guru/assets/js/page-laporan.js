/* SI-KESA PWA Guru | halaman Laporan (pratinjau JSON + unduh Excel; hanya tahun ajaran aktif). */
(function () {
  'use strict';
  var S = window.SIKESA, L = S.laporan, U = S.ui;
  var $ = function (id) { return document.getElementById(id); };
  var st = { semester: 'ganjil', tahunAjaran: null };

  function pesan(t, jenis) {
    var p = $('lpPesan'); p.hidden = !t; p.textContent = t || '';
    p.className = 'mb-4 rounded-xl px-3 py-2 text-xs font-medium ' + (jenis === 'ok' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700');
  }
  function toggle() {
    document.querySelectorAll('[data-sem]').forEach(function (b) {
      b.className = 'px-5 py-1 rounded-full text-xs font-bold transition ' +
        (b.getAttribute('data-sem') === st.semester ? 'bg-[#eba048] text-white shadow-sm' : 'text-gray-500 hover:text-[#1f2937]');
    });
  }
  document.querySelectorAll('[data-sem]').forEach(function (b) {
    b.addEventListener('click', function () { st.semester = b.getAttribute('data-sem'); toggle(); $('lpHasil').hidden = true; });
  });

  function tabel(judul, kolom, baris) {
    var th = kolom.map(function (k) { return '<th class="px-3 py-2 text-[10px] font-bold text-gray-500 uppercase text-left">' + U.esc(k[0]) + '</th>'; }).join('');
    var tr = baris.map(function (r) {
      return '<tr class="border-b border-gray-50">' + kolom.map(function (k) { return '<td class="px-3 py-2 text-xs text-gray-700">' + U.esc(k[1](r)) + '</td>'; }).join('') + '</tr>';
    }).join('') || '<tr><td class="px-3 py-3 text-xs text-gray-400" colspan="' + kolom.length + '">Tidak ada data.</td></tr>';
    return '<h4 class="text-sm font-bold text-gray-800 mb-2 mt-1">' + judul + '</h4><table class="w-full mb-5"><thead class="bg-gray-50"><tr>' + th + '</tr></thead><tbody>' + tr + '</tbody></table>';
  }

  function tampil(d) {
    var k = (d.kelas && d.kelas[0]) || { absensi: [], nilai: [] };
    var jenis = $('lpJenis').value;
    var h = '<p class="text-xs text-gray-500 mb-4">' + U.esc((d.meta || {}).sekolah) + ' \u00b7 ' + U.esc((d.meta || {}).tahun_ajaran) + ' \u00b7 Semester ' + U.esc(U.cap((d.meta || {}).semester)) + '</p>';
    if (jenis !== 'nilai') {
      h += tabel('Rekap Absensi', [['No', function (r) { return r.nomor_absen == null ? '-' : r.nomor_absen; }], ['Nama', function (r) { return r.nama_siswa; }],
        ['Hadir', function (r) { return r.hadir; }], ['Sakit', function (r) { return r.sakit; }], ['Izin', function (r) { return r.izin; }], ['Alpa', function (r) { return r.alpa; }]], k.absensi || []);
    }
    if (jenis !== 'absensi') {
      h += tabel('Rekap Nilai', [['Nama', function (r) { return r.nama_siswa; }], ['Mapel', function (r) { return r.nama_mapel; }],
        ['UH', function (r) { var u = r.uh || {}; var ks = Object.keys(u); return ks.length ? ks.map(function (x) { return u[x]; }).join(', ') : '-'; }],
        ['ASTS', function (r) { return r.asts == null ? '-' : r.asts; }], ['ASAS', function (r) { return r.asas == null ? '-' : r.asas; }]], k.nilai || []);
    }
    var el = $('lpHasil'); el.innerHTML = h; el.hidden = false;
  }

  function jalankan(btn, fn) {
    if (!st.tahunAjaran) { pesan('Tahun ajaran aktif belum tersedia.'); return; }
    pesan(''); btn.disabled = true;
    fn().then(null, function (err) { pesan(U.pesanGalat(err)); }).then(function () { btn.disabled = false; });
  }

  $('lpPratinjau').addEventListener('click', function () {
    jalankan(this, function () { return L.preview({ semester: st.semester, tahunAjaran: st.tahunAjaran, idSiswa: $('lpSiswa').value, idMapel: $('lpMapel').value }).then(tampil); });
  });
  $('lpUnduh').addEventListener('click', function () {
    jalankan(this, function () {
      return L.unduhDanSimpan({ semester: st.semester, tahunAjaran: st.tahunAjaran, jenis: $('lpJenis').value, idSiswa: $('lpSiswa').value, idMapel: $('lpMapel').value }).then(function (nama) { pesan('Berkas ' + nama + ' diunduh.', 'ok'); });
    });
  });

  toggle();
  S.me.then(function (me) {
    if (!me) return;
    var ta = me.tahun_ajaran;
    if (ta) { st.tahunAjaran = ta.id_tahun_ajaran; st.semester = ta.semester_aktif || 'ganjil'; $('lpTahun').textContent = ta.nama_tahun + ' \u00b7 Tahun aktif'; toggle(); }
    else $('lpTahun').textContent = 'Belum ada tahun ajaran aktif';
    $('lpKelas').textContent = me.kelas ? 'Kelas ' + me.kelas.nama_kelas : '-';
    if (me.kelas) {
      S.referensi.siswa().then(function (r) {
        $('lpSiswa').innerHTML = '<option value="">Semua siswa (satu kelas)</option>' + (r.items || []).map(function (x) { return '<option value="' + x.id_siswa + '">' + U.esc(x.nama_siswa) + '</option>'; }).join('');
      }).catch(function () {});
      S.referensi.mapel().then(function (m) {
        $('lpMapel').innerHTML = '<option value="">Semua mata pelajaran</option>' + (m || []).map(function (x) { return '<option value="' + x.id_mapel + '">' + U.esc(x.nama_mapel) + '</option>'; }).join('');
      }).catch(function () {});
    }
  }).catch(function () { /* ditampilkan app.js */ });
})();
