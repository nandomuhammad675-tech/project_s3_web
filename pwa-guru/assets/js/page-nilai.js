/*
 * SI-KESA PWA Guru | halaman Nilai (input per mapel/semester/jenis + rekap).
 * Memakai SIKESA.nilai, SIKESA.referensi, SIKESA.ui.
 */
(function () {
  'use strict';
  var S = window.SIKESA, N = S.nilai, U = S.ui;
  var $ = function (id) { return document.getElementById(id); };

  var st = { semester: 'ganjil', idMapel: null, jenis: 'UH', nomorUh: 1, siswa: [], mapel: [], items: [], tab: 'input', memuat: false, nomorManual: false };

  function info(teks) { var b = $('infoNilai'); b.hidden = !teks; b.textContent = teks || ''; }

  function setTab(t) {
    st.tab = t;
    var aktif = 'pb-3 border-b-2 border-[#eba048] text-[#eba048] font-bold text-sm';
    var pasif = 'pb-3 border-b-2 border-transparent text-gray-500 hover:text-gray-800 font-semibold text-sm transition';
    $('tabInput').className = t === 'input' ? aktif : pasif;
    $('tabRekap').className = t === 'rekap' ? aktif : pasif;
    $('panelInput').hidden = t !== 'input';
    $('panelRekap').hidden = t !== 'rekap';
    $('grupJenis').hidden = t !== 'input';
    $('grupNomorUh').hidden = t !== 'input' || st.jenis !== 'UH';
  }

  function tombolJenis() {
    document.querySelectorAll('#grupJenis [data-jenis]').forEach(function (b) {
      var on = b.getAttribute('data-jenis') === st.jenis;
      b.className = 'flex-1 h-full rounded-md text-xs font-bold transition ' +
        (on ? 'bg-[#eba048] text-white shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/50');
    });
    $('grupNomorUh').hidden = st.tab !== 'input' || st.jenis !== 'UH';
  }

  function cari(idSiswa) {
    for (var i = 0; i < st.items.length; i++) {
      var n = st.items[i];
      if (n.id_siswa === idSiswa && n.jenis === st.jenis && (st.jenis !== 'UH' || n.nomor_uh === st.nomorUh)) return n;
    }
    return null;
  }

  function renderInput() {
    var el = $('daftarNilai');
    if (!st.mapel.length) { el.innerHTML = '<div class="bg-white rounded-xl p-10 text-center text-sm text-gray-500 border border-gray-100">Belum ada mata pelajaran. Hubungi Admin untuk menambahkannya.</div>'; $('btnSimpanNilai').disabled = true; return; }
    if (!st.siswa.length) { el.innerHTML = '<div class="bg-white rounded-xl p-10 text-center text-sm text-gray-500 border border-gray-100">Belum ada siswa aktif pada kelas ini.</div>'; $('btnSimpanNilai').disabled = true; return; }
    $('btnSimpanNilai').disabled = false;
    el.innerHTML = st.siswa.map(function (r, i) {
      var ada = cari(r.id_siswa);
      var no = r.nomor_absen != null ? U.dua(r.nomor_absen) : String(i + 1);
      var hapus = ada && N.bisaDihapus(ada)
        ? '<button type="button" data-hapus="' + ada.id_nilai + '" class="w-10 h-10 rounded-lg bg-[#fef2f2] text-red-500 flex items-center justify-center hover:bg-red-100 transition" title="Hapus nilai UH"><i class="ph ph-trash text-lg"></i></button>'
        : '<span class="w-10 h-10"></span>';
      return '<div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 flex items-center justify-between">' +
        '<div class="flex items-center gap-4"><div class="w-8 h-8 rounded-lg bg-[#f7f6f0] flex items-center justify-center text-xs font-bold text-[#455a2d]">' + U.esc(no) + '</div>' +
        '<p class="font-bold text-gray-800 text-sm">' + U.esc(r.nama_siswa) + '</p></div>' +
        '<div class="flex items-center gap-3">' +
        '<input type="number" inputmode="numeric" min="0" max="100" step="1" data-siswa="' + r.id_siswa + '" data-awal="' + (ada ? ada.nilai : '') + '" value="' + (ada ? ada.nilai : '') + '" placeholder="-" aria-label="Nilai ' + U.esc(r.nama_siswa) + '" class="w-20 h-10 border border-gray-200 rounded-lg text-center font-bold text-gray-800 focus:outline-none focus:border-[#eba048] focus:ring-1 focus:ring-[#eba048] transition">' +
        hapus + '</div></div>';
    }).join('');
  }

  function renderRekap() {
    var el = $('rekapNilai');
    if (!st.siswa.length) { el.innerHTML = '<p class="p-8 text-center text-sm text-gray-500">Belum ada siswa aktif.</p>'; return; }
    var rk = N.rekapPerSiswa(st.items);
    var peta = {}; rk.baris.forEach(function (b) { peta[b.id_siswa] = b; });
    var th = 'px-3 py-3 text-xs font-bold text-gray-500 uppercase text-center';
    var h = '<table class="w-full text-sm"><thead class="bg-gray-50 border-b border-gray-100"><tr><th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase text-left">Siswa</th>';
    rk.kolomUh.forEach(function (k) { h += '<th class="' + th + '">UH ' + k + '</th>'; });
    h += '<th class="' + th + '">ASTS</th><th class="' + th + '">ASAS</th></tr></thead><tbody>';
    var td = function (c) { return '<td class="px-3 py-3 text-center font-semibold text-gray-800">' + (c ? c.nilai : '<span class="text-gray-300">-</span>') + '</td>'; };
    st.siswa.forEach(function (r) {
      var b = peta[r.id_siswa] || { uh: {}, asts: null, asas: null };
      h += '<tr class="border-b border-gray-50"><td class="px-4 py-3 font-semibold text-gray-800">' + U.esc(r.nama_siswa) + '</td>';
      rk.kolomUh.forEach(function (k) { h += td(b.uh[k]); });
      h += td(b.asts) + td(b.asas) + '</tr>';
    });
    el.innerHTML = h + '</tbody></table>' + (rk.kolomUh.length ? '' : '<p class="px-4 pb-4 pt-2 text-xs text-gray-400">Belum ada UH untuk mata pelajaran ini.</p>');
  }

  function render() { renderInput(); renderRekap(); }

  function saranNomor() {
    if (st.jenis !== 'UH' || st.nomorManual) return;
    st.nomorUh = N.nomorUhBerikutnya(st.items);
    $('inpNomorUh').value = st.nomorUh;
  }

  function muatNilai() {
    if (!st.idMapel) { st.items = []; render(); return Promise.resolve(); }
    st.memuat = true; info('');
    $('daftarNilai').innerHTML = '<div class="skel h-14"></div><div class="skel h-14"></div><div class="skel h-14"></div>';
    return N.semua({ id_mapel: st.idMapel, semester: st.semester }).then(function (items) {
      st.items = items; st.memuat = false; saranNomor(); render();
    }, function (err) {
      st.memuat = false; st.items = []; info(U.pesanGalat(err)); render();
    });
  }

  function simpan() {
    var inputs = Array.prototype.slice.call(document.querySelectorAll('#daftarNilai input[data-siswa]'));
    var kerja = [];
    inputs.forEach(function (inp) {
      var v = inp.value.trim();
      if (v === '' || v === inp.getAttribute('data-awal')) return;
      kerja.push({ id: Number(inp.getAttribute('data-siswa')), nilai: Number(v), mentah: v, nama: inp.getAttribute('aria-label').replace('Nilai ', '') });
    });
    if (!kerja.length) { U.toast('Tidak ada perubahan untuk disimpan.'); return Promise.resolve(); }
    var ok = 0, gagal = [];
    var rantai = Promise.resolve();
    kerja.forEach(function (k) {
      rantai = rantai.then(function () {
        var ada = cari(k.id);
        var p = ada ? N.perbaiki(ada.id_nilai, k.nilai) : N.buat({ id_siswa: k.id, id_mapel: st.idMapel, semester: st.semester, jenis: st.jenis, nomor_uh: st.jenis === 'UH' ? st.nomorUh : undefined, nilai: k.nilai });
        return p.then(function () { ok++; }, function (e) { if (e && e.status === 401) throw e; gagal.push(k.nama + ': ' + U.pesanGalat(e)); });
      });
    });
    return rantai.then(function () {
      if (gagal.length) U.toast(ok + ' tersimpan, ' + gagal.length + ' gagal. ' + gagal[0], 'error');
      else U.toast(ok + ' nilai disimpan.');
    }).then(muatNilai);
  }

  /* ---------- peristiwa ---------- */
  $('tabInput').addEventListener('click', function () { setTab('input'); });
  $('tabRekap').addEventListener('click', function () { setTab('rekap'); });
  $('selSemester').addEventListener('change', function (e) { st.semester = e.target.value; st.nomorManual = false; muatNilai(); });
  $('selMapel').addEventListener('change', function (e) { st.idMapel = Number(e.target.value) || null; st.nomorManual = false; muatNilai(); });
  $('grupJenis').addEventListener('click', function (e) {
    var b = e.target.closest('[data-jenis]'); if (!b) return;
    st.jenis = b.getAttribute('data-jenis'); tombolJenis(); saranNomor(); render();
  });
  $('inpNomorUh').addEventListener('change', function (e) {
    var n = parseInt(e.target.value, 10);
    st.nomorUh = n >= 1 ? n : 1; st.nomorManual = true; e.target.value = st.nomorUh; render();
  });
  $('daftarNilai').addEventListener('click', function (e) {
    var b = e.target.closest('[data-hapus]'); if (!b) return;
    if (!window.confirm('Hapus nilai UH ini?')) return;
    b.disabled = true;
    N.hapus(Number(b.getAttribute('data-hapus'))).then(function () { U.toast('Nilai UH dihapus.'); return muatNilai(); },
      function (err) { b.disabled = false; U.toast(U.pesanGalat(err), 'error'); });
  });
  $('btnSimpanNilai').addEventListener('click', function () {
    var b = this; b.disabled = true;
    simpan().then(function () { b.disabled = false; }, function (err) { b.disabled = false; U.toast(U.pesanGalat(err), 'error'); });
  });

  /* ---------- mulai ---------- */
  setTab('input'); tombolJenis();
  S.me.then(function (me) {
    if (!me || !me.kelas) return;
    var ta = me.tahun_ajaran; if (ta && ta.semester_aktif) { st.semester = ta.semester_aktif; $('selSemester').value = st.semester; }
    return Promise.all([S.referensi.siswa(), S.referensi.mapel()]).then(function (r) {
      st.siswa = r[0].items || []; st.mapel = r[1] || [];
      var sel = $('selMapel');
      sel.innerHTML = st.mapel.length ? st.mapel.map(function (m) { return '<option value="' + m.id_mapel + '">' + U.esc(m.nama_mapel) + '</option>'; }).join('') : '<option value="">(belum ada mapel)</option>';
      st.idMapel = st.mapel.length ? st.mapel[0].id_mapel : null;
      return muatNilai();
    });
  }).catch(function (err) { if (err && err.status !== 401) info(U.pesanGalat(err)); });
})();
