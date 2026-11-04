/* SI-KESA PWA Guru | halaman Piket kebersihan (per hari). Memakai SIKESA.piket, referensi, ui. */
(function () {
  'use strict';
  var S = window.SIKESA, P = S.piket, U = S.ui;
  var $ = function (id) { return document.getElementById(id); };
  var NAMA = { senin: 'Senin', selasa: 'Selasa', rabu: 'Rabu', kamis: 'Kamis', jumat: 'Jumat', sabtu: 'Sabtu' };

  var st = { items: [], siswa: [], hari: null, edit: null, pilih: {} };

  function render() {
    var per = P.perHari(st.items);
    $('gridPiket').innerHTML = P.HARI.map(function (h) {
      var p = per[h];
      if (p && p.siswa && p.siswa.length) {
        return '<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col w-full">' +
          '<div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">' +
          '<div class="flex items-center gap-2 text-[#1f2937] font-bold text-[13px] uppercase tracking-wider"><i class="ph ph-broom text-[#455a2d] text-sm bg-[#f7f6f0] p-1.5 rounded-md"></i> ' + NAMA[h] + '</div>' +
          '<button type="button" data-ubah="' + h + '" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 px-3.5 py-1.5 rounded-lg text-[11px] font-bold transition">Ubah</button></div>' +
          '<div class="space-y-2 mb-3.5">' + p.siswa.map(function (x) {
            return '<div class="flex items-center gap-2.5"><div class="w-6 h-6 rounded-full bg-[#f7f6f0] flex items-center justify-center text-[9px] font-bold text-[#455a2d] shrink-0">' + U.esc(U.inisial(x.nama_siswa)) + '</div>' +
              '<p class="text-[13px] font-semibold text-gray-700">' + U.esc(x.nama_siswa) + '</p></div>';
          }).join('') + '</div>' +
          '<div class="pt-2.5 border-t border-gray-100 text-[10px] text-gray-400 font-medium">' + U.esc(p.keterangan || 'Pastikan kelas bersih sebelum pulang.') + '</div></div>';
      }
      return '<div class="bg-gray-50/50 rounded-xl p-4 border border-dashed border-gray-300 flex flex-col w-full">' +
        '<div class="flex items-center gap-2 text-gray-500 font-bold text-[13px] uppercase tracking-wider pb-3 mb-3 border-b border-gray-200"><i class="ph ph-broom text-gray-400 text-sm"></i> ' + NAMA[h] + '</div>' +
        '<p class="text-[11px] text-gray-400 font-medium mb-3">Belum ada petugas</p>' +
        '<button type="button" data-tambah="' + h + '" class="w-full bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 py-2 rounded-xl text-[11px] font-bold flex items-center justify-center gap-1.5 transition"><i class="ph ph-plus text-sm"></i> Tambah</button></div>';
    }).join('');
  }

  function muat() {
    return P.daftar().then(function (items) { st.items = items; render(); }, function (err) {
      $('gridPiket').innerHTML = '<div class="col-span-full bg-red-50 border border-red-200 text-red-700 rounded-xl p-5 text-sm">' + U.esc(U.pesanGalat(err)) + '</div>';
    });
  }

  /* ---------- modal ---------- */
  function galat(peta) {
    document.querySelectorAll('#formPiket [data-err]').forEach(function (p) {
      var m = peta && peta[p.getAttribute('data-err')]; p.hidden = !m; p.textContent = m || '';
    });
  }
  function alertForm(t) { var a = $('pkAlert'); a.hidden = !t; a.textContent = t || ''; }
  function hitungPilih() { $('pkJumlah').textContent = Object.keys(st.pilih).length; }

  function daftarSiswa() {
    var q = $('pkCari').value.trim().toLowerCase();
    $('pkDaftar').innerHTML = st.siswa.filter(function (s) { return !q || s.nama_siswa.toLowerCase().indexOf(q) > -1; }).map(function (s) {
      return '<label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm"><input type="checkbox" data-id="' + s.id_siswa + '"' + (st.pilih[s.id_siswa] ? ' checked' : '') + ' class="accent-[#455a2d] w-4 h-4"> ' + U.esc(s.nama_siswa) + '</label>';
    }).join('') || '<p class="p-4 text-sm text-gray-400 text-center">Tidak ada siswa.</p>';
  }

  function buka(hari, item) {
    st.hari = hari; st.edit = item || null; st.pilih = {};
    if (item) item.siswa.forEach(function (x) { st.pilih[x.id_siswa] = true; });
    $('modalTitlePiket').textContent = (item ? 'Ubah' : 'Tambah') + ' Piket \u2014 ' + NAMA[hari];
    $('inputHariPiket').value = NAMA[hari];
    $('pkKet').value = item && item.keterangan ? item.keterangan : '';
    $('pkCari').value = '';
    $('pkHapus').hidden = !item;
    galat(null); alertForm(''); daftarSiswa(); hitungPilih();
    var m = $('modalPiket'), o = $('modalOverlayPiket'), c = $('modalContentPiket');
    m.classList.remove('hidden');
    setTimeout(function () { o.classList.remove('opacity-0'); o.classList.add('opacity-100'); c.classList.remove('scale-95', 'opacity-0'); c.classList.add('scale-100', 'opacity-100'); }, 10);
  }
  function tutup() {
    var m = $('modalPiket'), o = $('modalOverlayPiket'), c = $('modalContentPiket');
    o.classList.remove('opacity-100'); o.classList.add('opacity-0');
    c.classList.remove('scale-100', 'opacity-100'); c.classList.add('scale-95', 'opacity-0');
    setTimeout(function () { m.classList.add('hidden'); }, 300);
  }

  $('modalPiket').addEventListener('click', function (e) { if (e.target.closest('[data-tutup]')) tutup(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !$('modalPiket').classList.contains('hidden')) tutup(); });
  $('pkCari').addEventListener('input', daftarSiswa);
  $('pkDaftar').addEventListener('change', function (e) {
    var id = e.target.getAttribute('data-id'); if (!id) return;
    if (e.target.checked) st.pilih[id] = true; else delete st.pilih[id];
    hitungPilih();
  });

  $('formPiket').addEventListener('submit', function (e) {
    e.preventDefault();
    var ids = Object.keys(st.pilih).map(Number);
    if (!ids.length) { galat({ id_siswa: 'Pilih minimal satu siswa.' }); return; }
    var d = { hari: st.hari, keterangan: $('pkKet').value.trim() || null, id_siswa: ids };
    galat(null); alertForm('');
    var edit = st.edit, btn = $('pkSimpan'); btn.disabled = true;
    (edit ? P.ubah(edit.id_piket, d) : P.buat(d)).then(function () {
      U.toast('Piket ' + NAMA[st.hari] + ' disimpan.'); tutup(); return muat();
    }, function (err) {
      var peta = (err && err.errors) || {}; galat(peta);
      alertForm(err && err.status === 422 && Object.keys(peta).length ? '' : U.pesanGalat(err));
    }).then(function () { btn.disabled = false; });
  });

  $('pkHapus').addEventListener('click', function () {
    if (!st.edit || !window.confirm('Hapus piket hari ' + NAMA[st.hari] + '?')) return;
    var b = this; b.disabled = true;
    P.hapus(st.edit.id_piket).then(function () { U.toast('Piket dihapus.'); tutup(); return muat(); },
      function (err) { U.toast(U.pesanGalat(err), 'error'); }).then(function () { b.disabled = false; });
  });

  $('gridPiket').addEventListener('click', function (e) {
    var t = e.target.closest('[data-tambah]'), u = e.target.closest('[data-ubah]');
    if (t) return buka(t.getAttribute('data-tambah'), null);
    if (u) { var h = u.getAttribute('data-ubah'); return buka(h, P.perHari(st.items)[h]); }
  });

  S.me.then(function (me) {
    if (!me || !me.kelas) return;
    return S.referensi.siswa().then(function (r) { st.siswa = r.items || []; return muat(); });
  }).catch(function (err) { if (err && err.status !== 401) U.toast(U.pesanGalat(err), 'error'); });
})();
