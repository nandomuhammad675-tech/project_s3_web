/* SI-KESA PWA Admin | Data Akademik, HANYA LIHAT (dokumen 01 bagian 3.8)
 * Tiga tab: Absensi, Nilai, Kedisiplinan. Tidak ada tombol tambah, ubah, atau hapus.
 * Filter dikirim ke server (server yang menentukan). Ekspor Tampilan = CSV dari baris yang sedang tampil. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var E = window.SIKESA.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari server. Ubah di sini bila kontrak API berbeda. */
  var K = {
    tanggal: ['tanggal'], kelas: ['nama_kelas', 'kelas'], nama: ['nama_siswa', 'nama'], nisn: ['nisn'],
    status: ['status'], mapel: ['nama_mapel', 'mapel'], jenis: ['jenis'], nomorUh: ['nomor_uh'], nilai: ['nilai'],
    pelanggaran: ['jenis_pelanggaran'], catatan: ['catatan'], oleh: ['dicatat_oleh', 'nama_guru', 'guru']
  };
  var TA_K = { id: ['id_tahun_ajaran', 'id'], nama: ['nama_tahun', 'nama'], sem: ['semester_berjalan', 'semester'], aktif: ['status'] };
  var KELAS_K = { id: ['id_kelas', 'id'], ta: ['id_tahun_ajaran'], nama: ['nama_kelas', 'nama'], tingkat: ['tingkat'] };
  var MAPEL_K = { id: ['id_mapel', 'id'], nama: ['nama_mapel', 'nama'] };
  var PARAM = { ta: 'id_tahun_ajaran', semester: 'semester', kelas: 'id_kelas', mapel: 'id_mapel', tanggal: 'tanggal', dari: 'tanggal_dari', sampai: 'tanggal_sampai', siswa: 'siswa' };
  var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var PER_PAGE = 20;

  /* ---------- util ---------- */
  var $ = function (id) { return document.getElementById(id); };
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function pick(o, keys, fb) {
    for (var i = 0; i < keys.length; i++) if (o && o[keys[i]] !== undefined && o[keys[i]] !== null) return o[keys[i]];
    return fb;
  }
  function toList(res) {
    if (Array.isArray(res)) return res;
    var d = res && res.data;
    if (Array.isArray(d)) return d;
    if (d && Array.isArray(d.items)) return d.items;
    if (res && Array.isArray(res.items)) return res.items;
    return [];
  }
  function icon(n, cls) { return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
  function toast(t, type) { if (window.SikesaUI) window.SikesaUI.toast(t, type); }
  function errText(err) { return err && err.message ? err.message : 'Terjadi gangguan, coba lagi'; }
  function fmtDate(iso) {
    var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso || '');
    return m ? Number(m[3]) + ' ' + BULAN[Number(m[2]) - 1] + ' ' + m[1] : '';
  }
  function cap(s) { s = String(s || ''); return s.charAt(0).toUpperCase() + s.slice(1); }
  function txt(s) { return String(s == null ? '' : s).trim(); }
  function dash(s) { return s ? esc(s) : '<span class="is-muted">-</span>'; }
  function fetchAll(url) {
    return Api.get(url).then(function (res) {
      var rows = toList(res), last = Number(res && res.meta && res.meta.last_page) || 0;
      if (last <= 1) return rows;
      var jobs = [];
      for (var p = 2; p <= last; p++) jobs.push(Api.get(url, { page: p }));
      return Promise.all(jobs).then(function (rs) { rs.forEach(function (r) { rows = rows.concat(toList(r)); }); return rows; });
    });
  }

  /* ---------- konfigurasi tab ---------- */
  var STATUS = { hadir: 'Hadir', sakit: 'Sakit', izin: 'Izin', alpa: 'Alpa' };
  var TABS = {
    absensi: {
      path: E.akademik + '/absensi', semester: true, tanggal: true, empty: 'Belum ada data absensi untuk filter ini.',
      cols: [
        { h: 'Tanggal', html: function (r) { return dash(fmtDate(r.tanggal)); }, text: function (r) { return fmtDate(r.tanggal); } },
        { h: 'Nama Siswa', html: function (r) { return esc(r.nama) + (r.nisn ? '<span class="ak-nisn">NISN ' + esc(r.nisn) + '</span>' : ''); }, text: function (r) { return r.nama; } },
        { h: 'NISN', csvOnly: true, text: function (r) { return r.nisn; } },
        { h: 'Kelas', html: function (r) { return dash(r.kelas); }, text: function (r) { return r.kelas; } },
        { h: 'Status', html: function (r) {
            var k = r.status.toLowerCase();
            return r.status ? '<span class="badge ' + (STATUS[k] ? 'badge--' + k : 'badge--libur') + '">' + esc(STATUS[k] || cap(r.status)) + '</span>' : dash('');
          }, text: function (r) { return STATUS[r.status.toLowerCase()] || r.status; } }
      ]
    },
    nilai: {
      path: E.akademik + '/nilai', semester: true, mapel: true, empty: 'Belum ada data nilai untuk filter ini.',
      cols: [
        { h: 'Kelas', html: function (r) { return dash(r.kelas); }, text: function (r) { return r.kelas; } },
        { h: 'Nama Siswa', html: function (r) { return esc(r.nama) + (r.nisn ? '<span class="ak-nisn">NISN ' + esc(r.nisn) + '</span>' : ''); }, text: function (r) { return r.nama; } },
        { h: 'NISN', csvOnly: true, text: function (r) { return r.nisn; } },
        { h: 'Mata Pelajaran', html: function (r) { return dash(r.mapel); }, text: function (r) { return r.mapel; } },
        { h: 'Jenis', html: function (r) { return r.jenis ? '<span class="pill">' + esc(r.jenis) + '</span>' : dash(''); }, text: function (r) { return r.jenis; } },
        { h: 'Nilai', html: function (r) { return '<span class="ak-num">' + esc(r.nilai) + '</span>'; }, text: function (r) { return r.nilai; } }
      ]
    },
    disiplin: {
      path: E.akademik + '/kedisiplinan', semester: false, rentang: true, empty: 'Belum ada catatan kedisiplinan untuk filter ini.',
      cols: [
        { h: 'Tanggal', html: function (r) { return dash(fmtDate(r.tanggal)); }, text: function (r) { return fmtDate(r.tanggal); } },
        { h: 'Kelas', html: function (r) { return dash(r.kelas); }, text: function (r) { return r.kelas; } },
        { h: 'Nama Siswa', html: function (r) { return esc(r.nama) + (r.nisn ? '<span class="ak-nisn">NISN ' + esc(r.nisn) + '</span>' : ''); }, text: function (r) { return r.nama; } },
        { h: 'NISN', csvOnly: true, text: function (r) { return r.nisn; } },
        { h: 'Jenis Pelanggaran', html: function (r) { return dash(r.pelanggaran); }, text: function (r) { return r.pelanggaran; } },
        { h: 'Catatan', html: function (r) { return r.catatan ? '<span class="ak-clamp" title="' + esc(r.catatan) + '">' + esc(r.catatan) + '</span>' : dash(''); }, text: function (r) { return r.catatan; } },
        { h: 'Dicatat oleh', html: function (r) { return dash(r.oleh); }, text: function (r) { return r.oleh; } }
      ]
    }
  };

  function norm(r) {
    var kelas = pick(r, K.kelas, '');
    if (kelas && typeof kelas === 'object') kelas = kelas.nama_kelas || kelas.nama || '';
    var jenis = txt(pick(r, K.jenis, '')), nomor = pick(r, K.nomorUh, null);
    if (/^uh$/i.test(jenis) && nomor !== null && nomor !== '') jenis = 'UH-' + nomor;
    var nilai = pick(r, K.nilai, '');
    return {
      tanggal: txt(pick(r, K.tanggal, '')).slice(0, 10), kelas: txt(kelas), nama: txt(pick(r, K.nama, '')), nisn: txt(pick(r, K.nisn, '')),
      status: txt(pick(r, K.status, '')), mapel: txt(pick(r, K.mapel, '')), jenis: jenis,
      nilai: String(nilai).replace('.', ','), pelanggaran: txt(pick(r, K.pelanggaran, '')),
      catatan: txt(pick(r, K.catatan, '')), oleh: txt(pick(r, K.oleh, ''))
    };
  }

  /* ---------- elemen & keadaan ---------- */
  var el = {
    ta: $('ak-ta'), sem: $('ak-sem'), kelas: $('ak-kelas'), mapel: $('ak-mapel'), tgl: $('ak-tgl'), dari: $('ak-dari'),
    sampai: $('ak-sampai'), siswa: $('ak-siswa'), head: $('ak-head'), body: $('ak-body'), state: $('ak-state'),
    footer: $('ak-footer'), count: $('ak-count'), pager: $('ak-pager'), warn: $('ak-warn'), exp: $('ak-export'), panel: $('ak-panel')
  };
  var wrap = { sem: $('ak-sem-wrap'), mapel: $('ak-mapel-wrap'), tgl: $('ak-tgl-wrap'), dari: $('ak-dari-wrap'), sampai: $('ak-sampai-wrap') };
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.ak-tab'));

  var lists = { ta: [], kelas: [], mapel: [] };
  var st = { tab: 'absensi', ta: '', semester: 'ganjil', kelas: '', mapel: '', tgl: '', dari: '', sampai: '', q: '', page: 1 };
  var cur = { rows: [], total: 0, pages: 1, from: 0 }, loadState = 'loading', seq = 0, clientAll = null, ready = false;

  function showState(html) { el.state.innerHTML = html; el.state.hidden = !html; }
  function retryBtn(act) { return '<br><button type="button" class="btn btn--outline" data-act="' + act + '">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>'; }

  /* ---------- filter: daftar tahun ajaran, kelas, mapel ---------- */
  function loadLists() {
    return Promise.all([
      fetchAll(E.tahunAjaran),
      fetchAll(E.kelas),
      fetchAll(E.mapel).catch(function () { return []; }) // gagal tidak memblokir tampilan
    ]).then(function (r) {
      lists.ta = r[0].map(function (x) {
        return { id: String(pick(x, TA_K.id, '')), nama: txt(pick(x, TA_K.nama, '')), sem: String(pick(x, TA_K.sem, 'ganjil')).toLowerCase(), aktif: Number(pick(x, TA_K.aktif, 0)) === 1 };
      }).filter(function (x) { return x.id; }).sort(function (a, b) { return b.nama.localeCompare(a.nama); });
      lists.kelas = r[1].map(function (x) {
        return { id: String(pick(x, KELAS_K.id, '')), ta: String(pick(x, KELAS_K.ta, '')), nama: txt(pick(x, KELAS_K.nama, '')), tingkat: Number(pick(x, KELAS_K.tingkat, 0)) || 0 };
      }).filter(function (x) { return x.id; });
      lists.mapel = r[2].map(function (x) {
        return { id: String(pick(x, MAPEL_K.id, '')), nama: txt(pick(x, MAPEL_K.nama, '')) };
      }).filter(function (x) { return x.id && x.nama; }).sort(function (a, b) { return a.nama.localeCompare(b.nama, 'id', { sensitivity: 'base' }); });
    });
  }
  function opt(v, label, sel) { return '<option value="' + esc(v) + '"' + (sel ? ' selected' : '') + '>' + esc(label) + '</option>'; }

  function buildTa() {
    el.ta.innerHTML = lists.ta.map(function (t) { return opt(t.id, t.nama + (t.aktif ? ' (aktif)' : ''), t.id === st.ta); }).join('');
  }
  function buildKelas() {
    var rows = lists.kelas.filter(function (k) { return !st.ta || k.ta === st.ta; })
      .sort(function (a, b) { return (a.tingkat - b.tingkat) || a.nama.localeCompare(b.nama, 'id'); });
    if (st.kelas && !rows.some(function (k) { return k.id === st.kelas; })) st.kelas = '';
    el.kelas.innerHTML = opt('', 'Semua kelas', !st.kelas) + rows.map(function (k) { return opt(k.id, k.nama, k.id === st.kelas); }).join('');
  }
  function buildMapel() {
    el.mapel.innerHTML = opt('', 'Semua mapel', !st.mapel) + lists.mapel.map(function (m) { return opt(m.id, m.nama, m.id === st.mapel); }).join('');
  }
  function buildSem() {
    el.sem.innerHTML = opt('ganjil', 'Semester Ganjil', st.semester === 'ganjil') + opt('genap', 'Semester Genap', st.semester === 'genap');
  }
  function defaults() {
    var aktif = lists.ta.filter(function (t) { return t.aktif; })[0] || lists.ta[0];
    st.ta = aktif ? aktif.id : '';
    st.semester = aktif && aktif.sem === 'genap' ? 'genap' : 'ganjil';
    st.kelas = ''; st.mapel = ''; st.tgl = ''; st.dari = ''; st.sampai = ''; st.q = ''; st.page = 1;
  }
  function syncFilterUi() {
    buildTa(); buildSem(); buildKelas(); buildMapel();
    el.tgl.value = st.tgl; el.dari.value = st.dari; el.sampai.value = st.sampai; el.siswa.value = st.q;
  }

  /* ---------- tab ---------- */
  function setTab(name, focus) {
    if (!TABS[name]) name = 'absensi';
    st.tab = name; st.page = 1;
    var t = TABS[name];
    tabs.forEach(function (b) {
      var on = b.getAttribute('data-tab') === name;
      b.setAttribute('aria-selected', on ? 'true' : 'false');
      b.tabIndex = on ? 0 : -1;
      if (on && focus) b.focus();
    });
    el.panel.setAttribute('aria-labelledby', 'tab-' + name);
    wrap.sem.hidden = !t.semester;
    wrap.mapel.hidden = !t.mapel;
    wrap.tgl.hidden = !t.tanggal;
    wrap.dari.hidden = !t.rentang;
    wrap.sampai.hidden = !t.rentang;
    el.head.innerHTML = '<tr>' + t.cols.filter(function (c) { return !c.csvOnly; }).map(function (c) { return '<th scope="col">' + esc(c.h) + '</th>'; }).join('') + '</tr>';
    if (window.location.hash !== '#' + name) history.replaceState(null, '', '#' + name);
    if (ready) loadData();
  }
  tabs.forEach(function (b, i) {
    b.addEventListener('click', function () { setTab(b.getAttribute('data-tab')); });
    b.addEventListener('keydown', function (ev) {
      var n = ev.key === 'ArrowRight' ? 1 : ev.key === 'ArrowLeft' ? -1 : 0;
      if (!n) return;
      ev.preventDefault();
      setTab(tabs[(i + n + tabs.length) % tabs.length].getAttribute('data-tab'), true);
    });
  });
  window.addEventListener('hashchange', function () {
    var h = window.location.hash.replace('#', '');
    if (TABS[h] && h !== st.tab) setTab(h);
  });

  /* ---------- muat data tab ---------- */
  function query() {
    var t = TABS[st.tab], q = { page: st.page, per_page: PER_PAGE };
    q[PARAM.ta] = st.ta;
    if (t.semester) q[PARAM.semester] = st.semester;
    if (st.kelas) q[PARAM.kelas] = st.kelas;
    if (t.mapel && st.mapel) q[PARAM.mapel] = st.mapel;
    if (t.tanggal && st.tgl) q[PARAM.tanggal] = st.tgl;
    if (t.rentang) { if (st.dari) q[PARAM.dari] = st.dari; if (st.sampai) q[PARAM.sampai] = st.sampai; }
    if (st.q) q[PARAM.siswa] = st.q;
    return q;
  }
  function warn(text) { el.warn.textContent = text || ''; el.warn.hidden = !text; }

  function loadData() {
    var t = TABS[st.tab], my = ++seq;
    if (TABS[st.tab].rentang && st.dari && st.sampai && st.dari > st.sampai) {
      warn('Tanggal awal harus sebelum atau sama dengan tanggal akhir.'); return;
    }
    warn('');
    if (!st.ta) { loadState = 'ok'; cur = { rows: [], total: 0, pages: 1, from: 0 }; render(); return; }
    loadState = 'loading'; render();
    Api.get(t.path, query()).then(function (res) {
      if (my !== seq) return;
      var rows = toList(res).map(norm), meta = res && res.meta;
      if (meta && Number(meta.last_page)) {
        clientAll = null;
        cur = { rows: rows, total: Number(meta.total) || rows.length, pages: Number(meta.last_page) || 1, from: ((Number(meta.current_page) || st.page) - 1) * (Number(meta.per_page) || PER_PAGE) };
      } else { // server tidak membagi halaman: bagi di layar, dan saring siswa di layar
        clientAll = rows;
        applyClient();
      }
      loadState = 'ok'; render();
    }).catch(function (err) {
      if (my !== seq || (err && err.status === 401)) return;
      loadState = 'error'; render(errText(err));
    });
  }
  function applyClient() {
    var q = st.q.toLowerCase();
    var rows = (clientAll || []).filter(function (r) { return !q || (r.nama + ' ' + r.nisn).toLowerCase().indexOf(q) > -1; });
    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (st.page > pages) st.page = pages;
    var from = (st.page - 1) * PER_PAGE;
    cur = { rows: rows.slice(from, from + PER_PAGE), total: rows.length, pages: pages, from: from };
  }

  /* ---------- tampilan ---------- */
  function render(errMsg) {
    var t = TABS[st.tab], cols = t.cols.filter(function (c) { return !c.csvOnly; }), n = cols.length, i;
    el.exp.disabled = !(loadState === 'ok' && cur.rows.length);
    if (loadState === 'loading') {
      var sk = '';
      for (i = 0; i < 4; i++) sk += '<tr class="is-skeleton"><td colspan="' + n + '"><span class="skeleton skeleton--row"></span></td></tr>';
      el.body.innerHTML = sk; showState(''); el.footer.hidden = true; return;
    }
    if (loadState === 'error') {
      el.body.innerHTML = ''; el.footer.hidden = true;
      showState('<strong>Data gagal dimuat</strong>' + esc(errMsg || 'Terjadi gangguan, coba lagi') + retryBtn('retry'));
      return;
    }
    if (!cur.rows.length) {
      el.body.innerHTML = ''; el.footer.hidden = true;
      showState('<strong>Data belum ada</strong>' + esc(t.empty));
      return;
    }
    showState('');
    el.body.innerHTML = cur.rows.map(function (r) {
      return '<tr>' + cols.map(function (c) { return '<td>' + c.html(r) + '</td>'; }).join('') + '</tr>';
    }).join('');
    el.footer.hidden = false;
    el.count.textContent = 'Menampilkan ' + (cur.from + 1) + '–' + (cur.from + cur.rows.length) + ' dari ' + cur.total + ' data';
    var start = Math.max(1, Math.min(st.page - 2, cur.pages - 4)), end = Math.min(cur.pages, start + 4);
    var h = '<button type="button" class="pager__prev" data-page="' + (st.page - 1) + '" aria-label="Halaman sebelumnya"' + (st.page <= 1 ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
    for (var p = start; p <= end; p++) h += '<button type="button" data-page="' + p + '"' + (p === st.page ? ' class="is-current" aria-current="page"' : '') + ' aria-label="Halaman ' + p + '">' + p + '</button>';
    h += '<button type="button" data-page="' + (st.page + 1) + '" aria-label="Halaman berikutnya"' + (st.page >= cur.pages ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
    el.pager.innerHTML = h;
  }

  /* ---------- interaksi filter ---------- */
  function refilter() { st.page = 1; loadData(); }
  el.ta.addEventListener('change', function () {
    st.ta = el.ta.value; st.kelas = '';
    var t = lists.ta.filter(function (x) { return x.id === st.ta; })[0];
    if (t) { st.semester = t.sem === 'genap' ? 'genap' : 'ganjil'; buildSem(); }
    buildKelas(); refilter();
  });
  el.sem.addEventListener('change', function () { st.semester = el.sem.value; refilter(); });
  el.kelas.addEventListener('change', function () { st.kelas = el.kelas.value; refilter(); });
  el.mapel.addEventListener('change', function () { st.mapel = el.mapel.value; refilter(); });
  el.tgl.addEventListener('change', function () { st.tgl = el.tgl.value; refilter(); });
  el.dari.addEventListener('change', function () { st.dari = el.dari.value; refilter(); });
  el.sampai.addEventListener('change', function () { st.sampai = el.sampai.value; refilter(); });
  var timer = null;
  el.siswa.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { st.q = el.siswa.value.trim(); refilter(); }, 350);
  });
  $('ak-reset').addEventListener('click', function () { defaults(); syncFilterUi(); loadData(); });

  el.pager.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-page]');
    if (!b || b.disabled) return;
    st.page = Number(b.getAttribute('data-page'));
    if (clientAll) { applyClient(); render(); } else loadData();
  });
  el.state.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-act]');
    if (!b) return;
    if (b.getAttribute('data-act') === 'retry') loadData();
    if (b.getAttribute('data-act') === 'retry-init') init();
  });

  /* ---------- Ekspor Tampilan (CSV dari baris yang sedang tampil) ---------- */
  function csvCell(s) {
    s = String(s == null ? '' : s);
    if (/^[=+\-@\t\r]/.test(s)) s = "'" + s; // cegah rumus terbaca di Excel
    return '"' + s.replace(/"/g, '""') + '"';
  }
  el.exp.addEventListener('click', function () {
    if (!cur.rows.length) return;
    var t = TABS[st.tab];
    var lines = [t.cols.map(function (c) { return csvCell(c.h); }).join(',')];
    cur.rows.forEach(function (r) { lines.push(t.cols.map(function (c) { return csvCell(c.text(r)); }).join(',')); });
    var ta = lists.ta.filter(function (x) { return x.id === st.ta; })[0];
    var name = 'data-akademik-' + st.tab + (ta ? '-' + ta.nama.replace(/[^\d]+/g, '-') : '') + '.csv';
    var blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = name;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    toast('Tampilan diekspor');
  });

  /* ---------- mulai ---------- */
  function init() {
    loadState = 'loading'; ready = false; render();
    loadLists().then(function () {
      defaults(); syncFilterUi();
      ready = true;
      var h = window.location.hash.replace('#', '');
      setTab(TABS[h] ? h : 'absensi');
      loadData();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      el.body.innerHTML = ''; el.footer.hidden = true;
      showState('<strong>Filter gagal dimuat</strong>' + esc(errText(err)) + retryBtn('retry-init'));
    });
  }
  setTab(TABS[window.location.hash.replace('#', '')] ? window.location.hash.replace('#', '') : 'absensi');
  init();
})();