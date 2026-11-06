/* SI-KESA PWA Guru | halaman Kedisiplinan (tambah + perbaiki; TIDAK ada hapus). Memakai SIKESA.disiplin, referensi, ui. */
(function () {
  'use strict';
  var S = window.SIKESA, D = S.disiplin, U = S.ui;
  var $ = function (id) { return document.getElementById(id); };
  var st = { items: [], siswa: [], edit: null, total: 0 };

  function filter() {
    var q = {};
    if ($('fltSiswa').value) q.id_siswa = $('fltSiswa').value;
    return q;
  }

  function render() {
    var bulan = $('fltBulan').value;
    var rows = st.items.filter(function (c) { return !bulan || String(c.tanggal).slice(0, 7) === bulan; });
    var el = $('gridDisiplin');
    if (!rows.length) { el.innerHTML = '<div class="col-span-full bg-white rounded-xl p-10 text-center text-sm text-gray-500 border border-gray-100">Belum ada catatan kedisiplinan.</div>'; return; }
    el.innerHTML = rows.map(function (c) {
      return '<div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col h-full">' +
        '<div class="flex justify-between items-center mb-1.5"><h4 class="font-bold text-[#1f2937] text-[15px] leading-tight">' + U.esc(c.nama_siswa) + '</h4>' +
        '<span class="text-[10px] font-semibold text-gray-400">' + U.esc(U.tanggalPendek(c.tanggal)) + '</span></div>' +
        '<div class="mb-2"><span class="bg-[#fef2f2] text-red-500 px-2 py-0.5 rounded text-[10px] font-bold inline-block">' + U.esc(c.jenis_pelanggaran) + '</span></div>' +
        '<div class="flex justify-between items-end gap-4 mt-auto"><p class="text-[13px] text-gray-500 leading-snug flex-1 whitespace-pre-line">' + U.esc(c.catatan) + '</p>' +
        '<button type="button" data-ubah="' + c.id_catatan + '" class="shrink-0 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 px-3.5 py-1.5 rounded-lg text-[11px] font-bold flex items-center gap-1.5 transition"><i class="ph ph-pencil-simple text-sm"></i> Ubah</button></div></div>';
    }).join('');
  }

  function muat() {
    $('gridDisiplin').innerHTML = '<div class="skel h-28"></div><div class="skel h-28"></div>';
    var f = filter(); f.per_page = 100;
    return D.daftar(f).then(function (d) { st.items = d.items || []; st.total = d.total; render(); }, function (err) {
      $('gridDisiplin').innerHTML = '<div class="col-span-full bg-red-50 border border-red-200 text-red-700 rounded-xl p-5 text-sm">' + U.esc(U.pesanGalat(err)) + '</div>';
    });
  }

  function galat(peta) {
    document.querySelectorAll('#formDisiplin [data-err]').forEach(function (p) {
      var m = peta && peta[p.getAttribute('data-err')]; p.hidden = !m; p.textContent = m || '';
    });
  }
  function alertForm(t) { var a = $('dsAlert'); a.hidden = !t; a.textContent = t || ''; }

  function buka(item) {
    st.edit = item || null;
    $('modalTitleDisiplin').textContent = (item ? 'Ubah' : 'Tambah') + ' Catatan Kedisiplinan';
    var sel = $('inputSiswaDisiplin');
    sel.value = item ? String(item.id_siswa) : '';
    sel.disabled = !!item;       // siswa tidak dapat diganti saat memperbaiki
    $('dsTanggal').value = item ? item.tanggal : U.isoDari(new Date());
    $('dsJenis').value = item ? item.jenis_pelanggaran : '';
    $('dsCatatan').value = item ? item.catatan : '';
    galat(null); alertForm('');
    var m = $('modalDisiplin'), o = $('modalOverlayDisiplin'), c = $('modalContentDisiplin');
    m.classList.remove('hidden');
    setTimeout(function () { o.classList.remove('opacity-0'); o.classList.add('opacity-100'); c.classList.remove('scale-95', 'opacity-0'); c.classList.add('scale-100', 'opacity-100'); }, 10);
  }
  function tutup() {
    var m = $('modalDisiplin'), o = $('modalOverlayDisiplin'), c = $('modalContentDisiplin');
    o.classList.remove('opacity-100'); o.classList.add('opacity-0');
    c.classList.remove('scale-100', 'opacity-100'); c.classList.add('scale-95', 'opacity-0');
    setTimeout(function () { m.classList.add('hidden'); }, 300);
  }

  $('modalDisiplin').addEventListener('click', function (e) { if (e.target.closest('[data-tutup]')) tutup(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !$('modalDisiplin').classList.contains('hidden')) tutup(); });
  $('btnTambahDisiplin').addEventListener('click', function () { buka(null); });
  $('gridDisiplin').addEventListener('click', function (e) {
    var b = e.target.closest('[data-ubah]'); if (!b) return;
    var id = Number(b.getAttribute('data-ubah'));
    var it = st.items.filter(function (x) { return x.id_catatan === id; })[0]; if (it) buka(it);
  });
  $('fltSiswa').addEventListener('change', muat);
  $('fltBulan').addEventListener('change', render);
  $('fltReset').addEventListener('click', function () { $('fltSiswa').value = ''; $('fltBulan').value = ''; muat(); });

  $('formDisiplin').addEventListener('submit', function (e) {
    e.preventDefault();
    var edit = st.edit;
    var d = { tanggal: $('dsTanggal').value, jenis_pelanggaran: $('dsJenis').value.trim(), catatan: $('dsCatatan').value.trim() };
    if (!edit) d.id_siswa = Number($('inputSiswaDisiplin').value) || null;
    galat(null); alertForm('');
    var btn = $('dsSimpan'); btn.disabled = true;
    (edit ? D.ubah(edit.id_catatan, d) : D.buat(d)).then(function () {
      U.toast(edit ? 'Catatan diperbarui.' : 'Catatan ditambahkan.'); tutup(); return muat();
    }, function (err) {
      var peta = (err && err.errors) || {}; galat(peta);
      alertForm(err && err.status === 422 && Object.keys(peta).length ? '' : U.pesanGalat(err));
    }).then(function () { btn.disabled = false; });
  });

  S.me.then(function (me) {
    if (!me || !me.kelas) return;
    return S.referensi.siswa().then(function (r) {
      st.siswa = r.items || [];
      var opt = st.siswa.map(function (s) { return '<option value="' + s.id_siswa + '">' + U.esc(s.nama_siswa) + '</option>'; }).join('');
      $('fltSiswa').innerHTML = '<option value="">Semua Siswa</option>' + opt;
      $('inputSiswaDisiplin').innerHTML = '<option value="">Pilih siswa</option>' + opt;
      return muat();
    });
  }).catch(function (err) { if (err && err.status !== 401) U.toast(U.pesanGalat(err), 'error'); });
})();
