/* SI-KESA PWA Guru | halaman Jadwal Pelajaran (CRUD kelas sendiri). Memakai SIKESA.jadwal, referensi, ui. */
(function () {
  'use strict';
  var S = window.SIKESA, J = S.jadwal, U = S.ui;
  var $ = function (id) { return document.getElementById(id); };
  var NAMA_HARI = { senin: 'Senin', selasa: 'Selasa', rabu: 'Rabu', kamis: 'Kamis', jumat: 'Jumat', sabtu: 'Sabtu' };

  var st = { semester: 'ganjil', items: [], mapel: [], edit: null, memuat: false };

  function tab() {
    ['ganjil', 'genap'].forEach(function (s) {
      var b = $('tab' + U.cap(s));
      b.className = 'pb-2.5 text-[13px] transition-colors border-b-2 ' +
        (s === st.semester ? 'border-[#eba048] text-[#eba048] font-bold' : 'border-transparent text-gray-500 hover:text-gray-800 font-semibold');
    });
  }

  function render() {
    var grid = $('gridJadwal');
    var per = J.perHari(st.items);
    grid.innerHTML = J.HARI.map(function (h) {
      var list = per[h];
      var isi = list.length ? '<div class="divide-y divide-gray-50">' + list.map(function (j) {
        return '<div class="flex items-center gap-4 px-5 py-3">' +
          '<div class="flex flex-col text-xs font-bold text-[#455a2d] w-12 shrink-0"><span>' + U.esc(j.jam_mulai.slice(0, 5)) + '</span><span class="text-gray-400">' + U.esc(j.jam_selesai.slice(0, 5)) + '</span></div>' +
          '<div class="flex-1 min-w-0"><h4 class="font-bold text-gray-800 text-sm truncate">' + U.esc(j.nama_mapel) + '</h4>' +
          (j.keterangan ? '<p class="text-xs text-gray-500 truncate">' + U.esc(j.keterangan) + '</p>' : '') + '</div>' +
          '<div class="flex gap-1 shrink-0">' +
          '<button type="button" data-ubah="' + j.id_jadwal + '" class="w-8 h-8 rounded-lg hover:bg-gray-100 text-gray-500 flex items-center justify-center" title="Ubah"><i class="ph ph-pencil-simple text-lg"></i></button>' +
          '<button type="button" data-hapus="' + j.id_jadwal + '" class="w-8 h-8 rounded-lg hover:bg-red-50 text-red-500 flex items-center justify-center" title="Hapus"><i class="ph ph-trash text-lg"></i></button>' +
          '</div></div>';
      }).join('') + '</div>'
        : '<div class="px-5 py-4 flex items-center justify-between text-sm text-gray-400"><span class="flex items-center gap-2"><i class="ph ph-calendar-x text-lg"></i> Belum ada jadwal</span>' +
          '<button type="button" data-tambah="' + h + '" class="text-[#eba048] font-bold hover:underline flex items-center gap-1"><i class="ph ph-plus"></i> Tambah</button></div>';
      return '<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">' +
        '<div class="flex items-center justify-between px-5 py-3 bg-[#f7f6f0] border-b border-gray-100">' +
        '<div class="flex items-center gap-2 font-bold text-[#455a2d] text-sm"><i class="ph ph-calendar-blank"></i> ' + NAMA_HARI[h] + '</div>' +
        '<span class="text-xs text-gray-500">' + list.length + ' pelajaran</span></div>' + isi + '</div>';
    }).join('');
  }

  function muat() {
    tab();
    $('gridJadwal').innerHTML = '<div class="skel h-32"></div><div class="skel h-32"></div>';
    return J.daftar({ semester: st.semester }).then(function (items) { st.items = items; render(); },
      function (err) {
        $('gridJadwal').innerHTML = '<div class="col-span-full bg-red-50 border border-red-200 text-red-700 rounded-xl p-5 text-sm">' + U.esc(U.pesanGalat(err)) + '</div>';
      });
  }

  /* ---------- modal ---------- */
  function galat(map) {
    document.querySelectorAll('#formJadwal [data-err]').forEach(function (p) {
      var m = map && map[p.getAttribute('data-err')];
      p.hidden = !m; p.textContent = m || '';
    });
  }
  function alertForm(t) { var a = $('jdAlert'); a.hidden = !t; a.textContent = t || ''; }

  function buka(item, hariAwal) {
    st.edit = item || null;
    $('modalJudul').textContent = item ? 'Ubah Jadwal' : 'Tambah Jadwal';
    $('jdSemester').value = item ? item.semester : st.semester;
    $('jdHari').value = item ? item.hari : (hariAwal || 'senin');
    $('jdMapel').value = item ? String(item.id_mapel) : (st.mapel[0] ? String(st.mapel[0].id_mapel) : '');
    $('jdMulai').value = item ? item.jam_mulai.slice(0, 5) : '07:00';
    $('jdSelesai').value = item ? item.jam_selesai.slice(0, 5) : '07:35';
    $('jdKet').value = item && item.keterangan ? item.keterangan : '';
    galat(null); alertForm('');
    var m = $('modalJadwal'), o = $('modalOverlay'), c = $('modalContent');
    m.classList.remove('hidden');
    setTimeout(function () { o.classList.remove('opacity-0'); o.classList.add('opacity-100'); c.classList.remove('scale-95', 'opacity-0'); c.classList.add('scale-100', 'opacity-100'); }, 10);
  }
  function tutup() {
    var m = $('modalJadwal'), o = $('modalOverlay'), c = $('modalContent');
    o.classList.remove('opacity-100'); o.classList.add('opacity-0');
    c.classList.remove('scale-100', 'opacity-100'); c.classList.add('scale-95', 'opacity-0');
    setTimeout(function () { m.classList.add('hidden'); }, 300);
  }

  $('modalJadwal').addEventListener('click', function (e) { if (e.target.closest('[data-tutup]')) tutup(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !$('modalJadwal').classList.contains('hidden')) tutup(); });

  $('formJadwal').addEventListener('submit', function (e) {
    e.preventDefault();
    var d = {
      semester: $('jdSemester').value, hari: $('jdHari').value, id_mapel: Number($('jdMapel').value) || null,
      jam_mulai: $('jdMulai').value, jam_selesai: $('jdSelesai').value, keterangan: $('jdKet').value.trim() || null
    };
    galat(null); alertForm('');
    var edit = st.edit;
    var btn = $('jdSimpan'); btn.disabled = true;
    (edit ? J.ubah(edit.id_jadwal, d) : J.buat(d)).then(function () {
      U.toast(edit ? 'Jadwal diperbarui.' : 'Jadwal ditambahkan.');
      tutup();
      st.semester = d.semester;
      return muat();
    }, function (err) {
      var peta = (err && err.errors) || {};
      galat(peta);
      alertForm(Object.keys(peta).length && err.status === 422 ? '' : U.pesanGalat(err));
    }).then(function () { btn.disabled = false; });
  });

  /* ---------- peristiwa ---------- */
  $('tabGanjil').addEventListener('click', function () { st.semester = 'ganjil'; muat(); });
  $('tabGenap').addEventListener('click', function () { st.semester = 'genap'; muat(); });
  $('fabJadwal').addEventListener('click', function () { buka(null); });
  $('gridJadwal').addEventListener('click', function (e) {
    var t = e.target.closest('[data-tambah]'), u = e.target.closest('[data-ubah]'), h = e.target.closest('[data-hapus]');
    if (t) return buka(null, t.getAttribute('data-tambah'));
    if (u) { var id = Number(u.getAttribute('data-ubah')); var it = st.items.filter(function (x) { return x.id_jadwal === id; })[0]; if (it) buka(it); return; }
    if (h) {
      if (!window.confirm('Hapus jadwal ini?')) return;
      h.disabled = true;
      J.hapus(Number(h.getAttribute('data-hapus'))).then(function () { U.toast('Jadwal dihapus.'); return muat(); },
        function (err) { h.disabled = false; U.toast(U.pesanGalat(err), 'error'); });
    }
  });

  S.me.then(function (me) {
    if (!me || !me.kelas) return;
    if (me.tahun_ajaran && me.tahun_ajaran.semester_aktif) st.semester = me.tahun_ajaran.semester_aktif;
    return S.referensi.mapel().then(function (m) {
      st.mapel = m || [];
      $('jdMapel').innerHTML = st.mapel.map(function (x) { return '<option value="' + x.id_mapel + '">' + U.esc(x.nama_mapel) + '</option>'; }).join('');
      return muat();
    });
  }).catch(function (err) { if (err && err.status !== 401) U.toast(U.pesanGalat(err), 'error'); });
})();
