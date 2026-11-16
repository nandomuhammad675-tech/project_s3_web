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
    $('jdBaris').innerHTML = '';
    if (item) { tambahBaris({ id_mapel: item.id_mapel, mulai: item.jam_mulai.slice(0, 5), selesai: item.jam_selesai.slice(0, 5), ket: item.keterangan || '' }); }
    else { tambahBaris(); }
    $('jdTambahBaris').hidden = !!item;
    $('jdHint').hidden = !!item;
    $('jdSemester').disabled = !!item;
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

  /* ---------- baris mapel + jam ---------- */
  function ke(menit) { var m = Math.max(0, Math.min(23 * 60 + 59, menit)); return U.dua(Math.floor(m / 60)) + ':' + U.dua(m % 60); }
  function dariJam(j) { var p = String(j || '').split(':'); return (+p[0] || 0) * 60 + (+p[1] || 0); }

  function tambahBaris(nilai) {
    var wadah = $('jdBaris');
    var terakhir = wadah.lastElementChild;
    var mulai = nilai ? nilai.mulai : (terakhir ? terakhir.querySelector('[data-f=selesai]').value : '07:00');
    var selesai = nilai ? nilai.selesai : ke(dariJam(mulai) + 35);
    var opsi = st.mapel.map(function (m) { return '<option value="' + m.id_mapel + '"' + (nilai && nilai.id_mapel === m.id_mapel ? ' selected' : '') + '>' + U.esc(m.nama_mapel) + '</option>'; }).join('');
    var d = document.createElement('div');
    d.className = 'border border-gray-200 rounded-xl p-3 bg-gray-50/60';
    d.innerHTML =
      '<div class="flex items-center gap-2 mb-2">' +
        '<select data-f="mapel" aria-label="Mata pelajaran" class="flex-1 border border-gray-200 rounded-lg p-2.5 text-sm font-medium bg-white focus:outline-none focus:border-[#eba048]">' + opsi + '</select>' +
        '<button type="button" data-hapus-baris class="w-9 h-9 rounded-lg text-red-500 hover:bg-red-50 flex items-center justify-center shrink-0" aria-label="Hapus baris"><i class="ph ph-trash text-lg"></i></button>' +
      '</div>' +
      '<div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">' +
        '<input type="time" step="300" data-f="mulai" value="' + mulai + '" aria-label="Jam mulai" class="border border-gray-200 rounded-lg p-2.5 text-sm font-bold bg-white focus:outline-none focus:border-[#eba048]">' +
        '<span class="text-gray-400 text-sm">sampai</span>' +
        '<input type="time" step="300" data-f="selesai" value="' + selesai + '" aria-label="Jam selesai" class="border border-gray-200 rounded-lg p-2.5 text-sm font-bold bg-white focus:outline-none focus:border-[#eba048]">' +
      '</div>' +
      '<input type="text" data-f="ket" maxlength="100" value="' + U.esc(nilai ? nilai.ket : '') + '" placeholder="Keterangan (opsional)" class="mt-2 w-full border border-gray-200 rounded-lg p-2 text-xs bg-white focus:outline-none focus:border-[#eba048]">';
    wadah.appendChild(d);
    var mu = d.querySelector('[data-f=mulai]'), se = d.querySelector('[data-f=selesai]');
    mu.addEventListener('change', function () { if (!se.dataset.manual) se.value = ke(dariJam(mu.value) + 35); });
    se.addEventListener('change', function () { se.dataset.manual = '1'; });
    pasangTombolHapus();
  }
  function pasangTombolHapus() {
    var semua = $('jdBaris').children;
    for (var i = 0; i < semua.length; i++) semua[i].querySelector('[data-hapus-baris]').hidden = semua.length === 1;
  }
  $('jdTambahBaris').addEventListener('click', function () { tambahBaris(); });
  $('jdBaris').addEventListener('click', function (e) {
    var b = e.target.closest('[data-hapus-baris]'); if (!b) return;
    b.closest('.border').remove(); pasangTombolHapus();
  });

  function bacaBaris() {
    return Array.prototype.map.call($('jdBaris').children, function (d) {
      return {
        id_mapel: Number(d.querySelector('[data-f=mapel]').value) || null,
        jam_mulai: d.querySelector('[data-f=mulai]').value,
        jam_selesai: d.querySelector('[data-f=selesai]').value,
        keterangan: d.querySelector('[data-f=ket]').value.trim() || null
      };
    });
  }

  $('formJadwal').addEventListener('submit', function (e) {
    e.preventDefault();
    galat(null); alertForm('');
    var semester = st.edit ? st.edit.semester : $('jdSemester').value, hari = $('jdHari').value;
    var baris = bacaBaris();
    for (var i = 0; i < baris.length; i++) {
      var b = baris[i];
      if (!b.id_mapel || !b.jam_mulai || !b.jam_selesai) { alertForm('Lengkapi mata pelajaran dan jam pada baris ' + (i + 1) + '.'); return; }
      if (b.jam_selesai <= b.jam_mulai) { alertForm('Baris ' + (i + 1) + ': jam selesai harus setelah jam mulai.'); return; }
      for (var j = 0; j < i; j++) { if (b.jam_mulai < baris[j].jam_selesai && baris[j].jam_mulai < b.jam_selesai) { alertForm('Jam baris ' + (i + 1) + ' bertabrakan dengan baris ' + (j + 1) + '.'); return; } }
    }
    var edit = st.edit, btn = $('jdSimpan'); btn.disabled = true;
    var ok = 0, gagal = [];
    var rantai = Promise.resolve();
    baris.forEach(function (b, idx) {
      rantai = rantai.then(function () {
        var data = { semester: semester, hari: hari, id_mapel: b.id_mapel, jam_mulai: b.jam_mulai, jam_selesai: b.jam_selesai, keterangan: b.keterangan };
        return (edit ? J.ubah(edit.id_jadwal, data) : J.buat(data)).then(function () { ok++; }, function (err) {
          if (err && err.status === 401) throw err;
          gagal.push('Baris ' + (idx + 1) + ': ' + U.pesanGalat(err));
        });
      });
    });
    rantai.then(function () {
      if (gagal.length) { alertForm((ok ? ok + ' tersimpan. ' : '') + gagal.join(' ')); }
      else { U.toast(edit ? 'Jadwal diperbarui.' : ok + ' jadwal ditambahkan.'); tutup(); }
      st.semester = semester;
      return muat();
    }, function (err) { alertForm(U.pesanGalat(err)); }).then(function () { btn.disabled = false; });
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
      return muat();
    });
  }).catch(function (err) { if (err && err.status !== 401) U.toast(U.pesanGalat(err), 'error'); });
})();
