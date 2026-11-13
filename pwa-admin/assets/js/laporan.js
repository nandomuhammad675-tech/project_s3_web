/* SI-KESA PWA Admin | Laporan Excel (dokumen 01 bagian 3.9 dan 5)
 * Parameter: Tahun Ajaran, Semester (ganjil/genap), Kelas (opsional, kosong = semua kelas).
 * Lihat Pratinjau  -> GET /api/admin/laporan
 * Unduh Excel      -> GET /api/admin/laporan/excel (token lewat header, bukan lewat URL) */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari server. Ubah di sini bila kontrak API berbeda. */
  var TA_K = { id: ['id_tahun_ajaran', 'id'], nama: ['nama_tahun', 'nama'], sem: ['semester_berjalan', 'semester'], aktif: ['status'] };
  var KELAS_K = { id: ['id_kelas', 'id'], ta: ['id_tahun_ajaran'], nama: ['nama_kelas', 'nama'], tingkat: ['tingkat'] };
  var PV = { absensi: ['absensi', 'rekap_absensi'], nilai: ['nilai', 'rekap_nilai'], header: ['header', 'kepala', 'kepala_laporan'] };
  var ROW = {
    kelas: ['nama_kelas', 'kelas'], nisn: ['nisn'], nama: ['nama_siswa', 'nama'],
    hadir: ['hadir', 'total_hadir'], sakit: ['sakit', 'total_sakit'], izin: ['izin', 'total_izin'], alpa: ['alpa', 'total_alpa'],
    mapel: ['nama_mapel', 'mapel'], asts: ['asts'], asas: ['asas'], uh: ['uh', 'nilai_uh']
  };
  var PARAM = { ta: 'id_tahun_ajaran', semester: 'semester', kelas: 'id_kelas' };
  var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var PER_PAGE = 15;

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
  function txt(s) { return String(s == null ? '' : s).trim(); }
  function num(v) { return v === null || v === undefined || v === '' ? '-' : String(v).replace('.', ','); }
  function today() { var d = new Date(); return d.getDate() + ' ' + BULAN[d.getMonth()] + ' ' + d.getFullYear(); }
  function fetchAll(url) {
    return Api.get(url).then(function (res) {
      var rows = toList(res), last = Number(res && res.meta && res.meta.last_page) || 0;
      if (last <= 1) return rows;
      var jobs = [];
      for (var p = 2; p <= last; p++) jobs.push(Api.get(url, { page: p }));
      return Promise.all(jobs).then(function (rs) { rs.forEach(function (r) { rows = rows.concat(toList(r)); }); return rows; });
    });
  }
  function serverMsg(b) {
    if (!b || typeof b !== 'object') return '';
    if (typeof b.message === 'string' && b.message) return b.message;
    if (typeof b.error === 'string' && b.error) return b.error;
    if (b.errors && typeof b.errors === 'object') {
      var f = Object.values(b.errors)[0];
      if (Array.isArray(f)) f = f[0];
      if (typeof f === 'string') return f;
    }
    return '';
  }
  function buildUrl(path, q) {
    var base = (CFG.apiBase || '/api').replace(/\/$/, '');
    var u = base + (path.indexOf('/api/') === 0 ? path.slice(4) : path);
    var qs = Object.keys(q || {}).filter(function (k) { return q[k] !== '' && q[k] != null; })
      .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(q[k]); }).join('&');
    return qs ? u + '?' + qs : u;
  }

  /* ---------- elemen & keadaan ---------- */
  var el = {
    ta: $('lap-ta'), sem: $('lap-sem'), kelas: $('lap-kelas'), hint: $('lap-kelas-hint'), msg: $('lap-msg'),
    state: $('lap-state'), form: $('form-laporan'), btnPv: $('lap-preview'), btnDl: $('lap-download'),
    pv: $('lap-pv'), pvHead: $('lap-pv-head'), thead: $('lap-pv-thead'), tbody: $('lap-pv-body'),
    pvState: $('lap-pv-state'), pvFooter: $('lap-pv-footer'), pvCount: $('lap-pv-count'), pvPager: $('lap-pv-pager')
  };
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.lap-tab'));
  var lists = { ta: [], kelas: [] };
  var st = { ta: '', semester: 'ganjil', kelas: '' };
  var busy = '', seq = 0;
  var pv = { sheet: 'absensi', absensi: [], nilai: [], maxUh: 0, page: 1 };
  var btnHtml = { pv: el.btnPv.innerHTML, dl: el.btnDl.innerHTML };

  function opt(v, label, sel) { return '<option value="' + esc(v) + '"' + (sel ? ' selected' : '') + '>' + esc(label) + '</option>'; }
  function showMsg(text, info) { el.msg.className = 'notice ' + (info ? 'notice--info' : 'notice--warn'); el.msg.textContent = text || ''; el.msg.hidden = !text; }
  function showState(html) { el.state.innerHTML = html; el.state.hidden = !html; }
  function params() {
    var q = {};
    if (st.ta) q[PARAM.ta] = st.ta;
    q[PARAM.semester] = st.semester;
    if (st.kelas) q[PARAM.kelas] = st.kelas;
    return q;
  }
  function setBusy(which) {
    busy = which;
    var on = !!which;
    el.btnPv.disabled = on; el.btnDl.disabled = on;
    el.ta.disabled = on; el.sem.disabled = on; el.kelas.disabled = on || !st.ta;
    el.btnPv.innerHTML = which === 'pv' ? icon('eye', 'ico--sm') + ' <span>Memuat...</span>' : btnHtml.pv;
    el.btnDl.innerHTML = which === 'dl' ? icon('download', 'ico--sm') + ' <span>Membuat file...</span>' : btnHtml.dl;
    el.form.setAttribute('aria-busy', on ? 'true' : 'false');
  }

  /* ---------- filter ---------- */
  function buildTa() {
    el.ta.innerHTML = opt('', 'Semua Tahun Ajaran', !st.ta) + lists.ta.map(function (t) {
      return opt(t.id, t.nama + (t.aktif ? ' (aktif)' : ''), t.id === st.ta);
    }).join('');
  }
  function buildKelas() {
    var rows = st.ta ? lists.kelas.filter(function (k) { return k.ta === st.ta; })
      .sort(function (a, b) { return (a.tingkat - b.tingkat) || a.nama.localeCompare(b.nama, 'id'); }) : [];
    if (st.kelas && !rows.some(function (k) { return k.id === st.kelas; })) st.kelas = '';
    el.kelas.innerHTML = opt('', 'Semua Kelas', !st.kelas) + rows.map(function (k) { return opt(k.id, k.nama, k.id === st.kelas); }).join('');
    el.kelas.disabled = !st.ta || !!busy;
    el.hint.hidden = !!st.ta;
  }
  function resetPreview() { seq++; el.pv.hidden = true; showMsg(''); }

  el.ta.addEventListener('change', function () {
    st.ta = el.ta.value; st.kelas = '';
    var t = lists.ta.filter(function (x) { return x.id === st.ta; })[0];
    if (t) { st.semester = t.sem === 'genap' ? 'genap' : 'ganjil'; el.sem.value = st.semester; }
    buildKelas(); resetPreview();
  });
  el.sem.addEventListener('change', function () { st.semester = el.sem.value; resetPreview(); });
  el.kelas.addEventListener('change', function () { st.kelas = el.kelas.value; resetPreview(); });

  function init() {
    showState(''); setBusy('');
    el.btnPv.disabled = true; el.btnDl.disabled = true;
    Promise.all([fetchAll(E.tahunAjaran), fetchAll(E.kelas)]).then(function (r) {
      lists.ta = r[0].map(function (x) {
        return { id: String(pick(x, TA_K.id, '')), nama: txt(pick(x, TA_K.nama, '')), sem: String(pick(x, TA_K.sem, 'ganjil')).toLowerCase(), aktif: Number(pick(x, TA_K.aktif, 0)) === 1 };
      }).filter(function (x) { return x.id; }).sort(function (a, b) { return b.nama.localeCompare(a.nama); });
      lists.kelas = r[1].map(function (x) {
        return { id: String(pick(x, KELAS_K.id, '')), ta: String(pick(x, KELAS_K.ta, '')), nama: txt(pick(x, KELAS_K.nama, '')), tingkat: Number(pick(x, KELAS_K.tingkat, 0)) || 0 };
      }).filter(function (x) { return x.id; });
      el.form.hidden = false;
      el.ta.disabled = false; el.sem.disabled = false;
      buildTa(); buildKelas();
      el.btnPv.disabled = false; el.btnDl.disabled = false;
    }).catch(function (err) {
      if (err && err.status === 401) return;
      el.form.hidden = true;
      showState('<strong>Pilihan laporan gagal dimuat</strong>' + esc(errText(err)) +
        '<br><button type="button" class="btn btn--outline" data-act="retry">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>');
    });
  }
  el.state.addEventListener('click', function (ev) { if (ev.target.closest('[data-act="retry"]')) init(); });

  /* ---------- pratinjau ---------- */
  function uhList(r) {
    var u = pick(r, ROW.uh, null), out = [], k;
    if (Array.isArray(u)) return u.map(function (v) { return v && typeof v === 'object' ? pick(v, ['nilai'], null) : v; });
    if (u && typeof u === 'object') {
      for (k in u) { var m = /(\d+)$/.exec(k); if (m) out[Number(m[1]) - 1] = u[k]; }
      return out;
    }
    for (k in r) { var m2 = /^uh[_-]?(\d+)$/i.exec(k); if (m2) out[Number(m2[1]) - 1] = r[k]; }
    return out;
  }
  function normPv(res) {
    var d = res && res.data !== undefined && !Array.isArray(res.data) ? res.data : (res || {});
    var abs = pick(d, PV.absensi, []), nil = pick(d, PV.nilai, []);
    pv.absensi = (Array.isArray(abs) ? abs : []).map(function (r) {
      return { kelas: txt(pick(r, ROW.kelas, '')), nisn: txt(pick(r, ROW.nisn, '')), nama: txt(pick(r, ROW.nama, '')),
        hadir: pick(r, ROW.hadir, null), sakit: pick(r, ROW.sakit, null), izin: pick(r, ROW.izin, null), alpa: pick(r, ROW.alpa, null) };
    });
    pv.maxUh = 0;
    pv.nilai = (Array.isArray(nil) ? nil : []).map(function (r) {
      var uh = uhList(r);
      pv.maxUh = Math.max(pv.maxUh, uh.length);
      return { kelas: txt(pick(r, ROW.kelas, '')), nisn: txt(pick(r, ROW.nisn, '')), nama: txt(pick(r, ROW.nama, '')),
        mapel: txt(pick(r, ROW.mapel, '')), uh: uh, asts: pick(r, ROW.asts, null), asas: pick(r, ROW.asas, null) };
    });
    return pick(d, PV.header, null);
  }

  function headStrip(h) {
    var ta = el.ta.options[el.ta.selectedIndex], kl = el.kelas.options[el.kelas.selectedIndex];
    var school = txt(h && pick(h, ['nama_sekolah'], '')) || CFG.schoolName;
    var taTxt = txt(h && pick(h, ['tahun_ajaran', 'nama_tahun'], '')) || ta.text.replace(' (aktif)', '');
    var sem = txt(h && pick(h, ['semester'], '')) || st.semester;
    var kelas = txt(h && pick(h, ['kelas', 'nama_kelas'], '')) || kl.text;
    var tgl = txt(h && pick(h, ['tanggal_ekspor'], '')) || today();
    el.pvHead.innerHTML = '<strong>' + esc(school) + '</strong> · Semester ' + esc(sem.charAt(0).toUpperCase() + sem.slice(1)) +
      ' · ' + esc(taTxt) + ' · ' + esc(kelas) + ' · Tanggal ekspor ' + esc(tgl);
  }

  function loadPreview() {
    if (busy) return;
    showMsg(''); var my = ++seq;
    setBusy('pv');
    Api.get(E.laporan, params()).then(function (res) {
      if (my !== seq) return;
      var h = normPv(res);
      headStrip(h); pv.page = 1; setSheet(pv.sheet);
      el.pv.hidden = false;
      el.pv.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }).catch(function (err) {
      if (my !== seq || (err && err.status === 401)) return;
      el.pv.hidden = true; showMsg(errText(err), err && err.status === 409);
    }).then(function () { if (my === seq || !busy) setBusy(''); });
  }
  el.btnPv.addEventListener('click', loadPreview);

  function setSheet(name) {
    pv.sheet = name; pv.page = 1;
    tabs.forEach(function (b) {
      var on = b.getAttribute('data-sheet') === name;
      b.setAttribute('aria-selected', on ? 'true' : 'false'); b.tabIndex = on ? 0 : -1;
    });
    renderPv();
  }
  tabs.forEach(function (b) { b.addEventListener('click', function () { setSheet(b.getAttribute('data-sheet')); }); });

  function renderPv() {
    var rows = pv[pv.sheet], head = '', i;
    if (pv.sheet === 'absensi') {
      head = '<th scope="col">Kelas</th><th scope="col">NISN</th><th scope="col">Nama</th>' +
        '<th scope="col" class="num">Hadir</th><th scope="col" class="num">Sakit</th><th scope="col" class="num">Izin</th><th scope="col" class="num">Alpa</th>';
    } else {
      head = '<th scope="col">Kelas</th><th scope="col">NISN</th><th scope="col">Nama</th><th scope="col">Mapel</th>';
      for (i = 1; i <= pv.maxUh; i++) head += '<th scope="col" class="num">UH-' + i + '</th>';
      head += '<th scope="col" class="num">ASTS</th><th scope="col" class="num">ASAS</th>';
    }
    el.thead.innerHTML = '<tr>' + head + '</tr>';

    if (!rows.length) {
      el.tbody.innerHTML = ''; el.pvFooter.hidden = true;
      el.pvState.innerHTML = '<strong>Data belum ada</strong>Tidak ada data ' + (pv.sheet === 'absensi' ? 'absensi' : 'nilai') + ' untuk parameter ini.';
      el.pvState.hidden = false; return;
    }
    el.pvState.hidden = true;

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (pv.page > pages) pv.page = pages;
    var from = (pv.page - 1) * PER_PAGE, slice = rows.slice(from, from + PER_PAGE);
    el.tbody.innerHTML = slice.map(function (r) {
      var base = '<td>' + esc(r.kelas || '-') + '</td><td>' + esc(r.nisn || '-') + '</td><td>' + esc(r.nama) + '</td>';
      if (pv.sheet === 'absensi') {
        return '<tr>' + base + [r.hadir, r.sakit, r.izin, r.alpa].map(function (v) { return '<td class="num">' + esc(num(v)) + '</td>'; }).join('') + '</tr>';
      }
      var cells = '<td>' + esc(r.mapel || '-') + '</td>';
      for (var j = 0; j < pv.maxUh; j++) cells += '<td class="num">' + esc(num(r.uh[j])) + '</td>';
      cells += '<td class="num">' + esc(num(r.asts)) + '</td><td class="num">' + esc(num(r.asas)) + '</td>';
      return '<tr>' + base + cells + '</tr>';
    }).join('');

    el.pvFooter.hidden = false;
    el.pvCount.textContent = 'Menampilkan ' + (from + 1) + '–' + (from + slice.length) + ' dari ' + rows.length + ' baris';
    var start = Math.max(1, Math.min(pv.page - 2, pages - 4)), end = Math.min(pages, start + 4);
    var h = '<button type="button" class="pager__prev" data-page="' + (pv.page - 1) + '" aria-label="Halaman sebelumnya"' + (pv.page <= 1 ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
    for (var p = start; p <= end; p++) h += '<button type="button" data-page="' + p + '"' + (p === pv.page ? ' class="is-current" aria-current="page"' : '') + ' aria-label="Halaman ' + p + '">' + p + '</button>';
    h += '<button type="button" data-page="' + (pv.page + 1) + '" aria-label="Halaman berikutnya"' + (pv.page >= pages ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
    el.pvPager.innerHTML = h;
  }
  el.pvPager.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-page]');
    if (!b || b.disabled) return;
    pv.page = Number(b.getAttribute('data-page')); renderPv();
  });

  /* ---------- unduh Excel ---------- */
  function fail(status, message) { return { status: status, message: message }; }

  function saveBlob(blob, disposition) {
    var name = '';
    var m = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(disposition || '');
    if (m) { try { name = decodeURIComponent(m[1]); } catch (e) { name = m[1]; } }
    if (!name) {
      var ta = el.ta.options[el.ta.selectedIndex].text.replace(/\s*\(aktif\)/, '');
      name = 'laporan-' + st.semester + '-' + (st.ta ? ta : 'semua-tahun') + '.xlsx';
    }
    name = name.replace(/[^\w.\-]+/g, '-');
    if (!/\.xlsx$/i.test(name)) name += '.xlsx';
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = name;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(a.href); }, 1500);
  }

  el.form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (busy) return;
    showMsg('');
    if (window.sessionStorage.getItem(Api.DEMO_KEY)) { showMsg('Mode demo: unduhan Excel tidak tersedia tanpa server.', true); return; }

    var my = ++seq;
    setBusy('dl');
    fetch(buildUrl(E.laporan + '/excel', params()), {
      method: 'GET',
      headers: { Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json', Authorization: 'Bearer ' + (localStorage.getItem(Api.TOKEN_KEY) || '') },
      credentials: 'same-origin'
    }).catch(function () {
      throw fail(0, 'Terjadi gangguan, coba lagi');
    }).then(function (res) {
      if (res.ok) {
        if (/json/i.test(res.headers.get('Content-Type') || '')) {
          return res.json().then(function (b) { throw fail(422, serverMsg(b) || 'Laporan tidak dapat dibuat'); });
        }
        return res.blob().then(function (blob) { return { blob: blob, cd: res.headers.get('Content-Disposition') }; });
      }
      return res.text().then(function (t) {
        var b = null; try { b = t ? JSON.parse(t) : null; } catch (e) { b = null; }
        if (res.status === 401) { if (window.SikesaAuth) window.SikesaAuth.expire(); throw fail(401, 'Sesi berakhir, silakan masuk lagi'); }
        if (res.status === 403) throw fail(403, 'Anda tidak memiliki akses ke halaman ini');
        if (res.status === 409 || res.status === 422) throw fail(res.status, serverMsg(b) || 'Permintaan tidak dapat diproses');
        throw fail(res.status, 'Terjadi gangguan, coba lagi');
      });
    }).then(function (f) {
      saveBlob(f.blob, f.cd);
      toast('Laporan Excel berhasil dibuat');
    }).catch(function (err) {
      if (err && err.status === 401) return;
      showMsg(errText(err), err && err.status === 409);
    }).then(function () { setBusy(''); });
    void my;
  });

  init();
})();