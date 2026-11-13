/* SI-KESA PWA Admin | Akun (dokumen 01 bagian 3.10)
 * Tab 1 Kelola Akun : tambah akun Admin, nonaktifkan/aktifkan (dengan konfirmasi), reset password.
 * Tab 2 Ganti Password Saya: sukses -> token baru dari server menggantikan token lokal.
 * Akun sendiri: tombol nonaktifkan dan reset disembunyikan (server tetap menolak dengan 422). */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var Auth = window.SikesaAuth;
  var E = window.SIKESA.endpoints;
  if (!Api || !Auth || !Auth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_user', 'id_akun', 'id'], username: ['username'], role: ['role'],
    nama: ['nama', 'nama_guru', 'nama_lengkap'], aktif: ['status_akun', 'is_active', 'aktif'],
    terakhir: ['terakhir_aktif', 'last_login_at', 'last_active_at'], self: ['akun_sendiri', 'is_self']
  };
  var SEND = { username: 'username', password: 'password', role: 'role', status: 'status_akun', resetPass: 'password_baru', pwLama: 'password_lama', pwBaru: 'password_baru' };
  var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var PER_PAGE = 15, USER_MAX = 50, PASS_MIN = 8;

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
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function isOn(v) {
    if (v === undefined || v === null) return true;
    if (typeof v === 'string') return /^(aktif|active|1|true)$/i.test(v);
    return v === true || Number(v) === 1;
  }
  function itemUrl(id) { return E.akun + '/' + encodeURIComponent(id); }
  function fetchAll(url) {
    return Api.get(url).then(function (res) {
      var rows = toList(res), last = Number(res && res.meta && res.meta.last_page) || 0;
      if (last <= 1) return rows;
      var jobs = [];
      for (var p = 2; p <= last; p++) jobs.push(Api.get(url, { page: p }));
      return Promise.all(jobs).then(function (rs) { rs.forEach(function (r) { rows = rows.concat(toList(r)); }); return rows; });
    });
  }

  /* Waktu "Terakhir aktif": zona WIB. Tanpa zona pada data dianggap sudah WIB. */
  function parseTs(raw) {
    raw = txt(raw);
    var m = /^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/.exec(raw);
    if (!m) return null;
    if (/(Z|[+-]\d{2}:?\d{2})$/.test(raw)) { var t = Date.parse(raw); return isNaN(t) ? null : t; }
    return Date.UTC(+m[1], +m[2] - 1, +m[3], +m[4] - 7, +m[5]);
  }
  function wib(ts) {
    var d = new Date(ts + 7 * 3600000);
    return { y: d.getUTCFullYear(), mo: d.getUTCMonth(), d: d.getUTCDate(), jam: pad(d.getUTCHours()) + '.' + pad(d.getUTCMinutes()), day: Math.floor((ts + 7 * 3600000) / 86400000) };
  }
  function fmtLast(raw) {
    var ts = parseTs(raw);
    if (ts === null) return '';
    var now = Date.now(), diff = now - ts;
    if (diff >= 0 && diff < 5 * 60000) return 'Baru saja';
    var p = wib(ts), gap = wib(now).day - p.day;
    if (gap === 0) return 'Hari ini, ' + p.jam;
    if (gap === 1) return 'Kemarin, ' + p.jam;
    return p.d + ' ' + BULAN[p.mo] + ' ' + p.y + ', ' + p.jam;
  }

  function norm(r) {
    var role = txt(pick(r, KEYS.role, '')).toLowerCase();
    return {
      id: pick(r, KEYS.id, null), username: txt(pick(r, KEYS.username, '')), role: role,
      nama: txt(pick(r, KEYS.nama, '')), aktif: isOn(pick(r, KEYS.aktif, 1)),
      terakhir: pick(r, KEYS.terakhir, ''), self: isOn(pick(r, KEYS.self, 0)) && pick(r, KEYS.self, null) !== null
    };
  }
  function isSelf(r) {
    if (r.self) return true;
    var u = Auth.user();
    return !!(u && u.username && r.username && String(u.username).toLowerCase() === r.username.toLowerCase());
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }

  /* ---------- elemen & keadaan ---------- */
  var elBody = $('ac-body'), elState = $('ac-state'), elFooter = $('ac-footer'), elCount = $('ac-count'), elPager = $('ac-pager');
  var all = [], page = 1, loadState = 'loading';
  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }

  /* ---------- tab ---------- */
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.ac-tab'));
  var panels = { akun: $('panel-akun'), password: $('panel-password') };
  var btnAdd = $('ac-btn-add');
  function setTab(name, focus) {
    if (!panels[name]) name = 'akun';
    tabs.forEach(function (b) {
      var on = b.getAttribute('data-tab') === name;
      b.setAttribute('aria-selected', on ? 'true' : 'false'); b.tabIndex = on ? 0 : -1;
      if (on && focus) b.focus();
    });
    panels.akun.hidden = name !== 'akun';
    panels.password.hidden = name !== 'password';
    btnAdd.hidden = name !== 'akun';
    if (window.location.hash !== '#' + name) history.replaceState(null, '', '#' + name);
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
  window.addEventListener('hashchange', function () { setTab(window.location.hash.replace('#', '')); });

  /* ---------- muat & tampil daftar ---------- */
  function load(silent) {
    if (!silent) { loadState = 'loading'; render(); }
    return fetchAll(E.akun).then(function (rows) {
      all = rows.map(norm).filter(function (r) { return r.id !== null; });
      all.sort(function (a, b) { return (isSelf(b) - isSelf(a)) || (a.role === b.role ? 0 : a.role === 'admin' ? -1 : 1) || a.username.localeCompare(b.username, 'id', { sensitivity: 'base' }); });
      loadState = 'ok'; render();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      loadState = 'error'; render(errText(err));
    });
  }

  function render(errMsg) {
    var i;
    if (loadState === 'loading') {
      var sk = '';
      for (i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>';
      elBody.innerHTML = sk; showState(''); elFooter.hidden = true; return;
    }
    if (loadState === 'error') {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Data gagal dimuat</strong>' + esc(errMsg || 'Terjadi gangguan, coba lagi') +
        '<br><button type="button" class="btn btn--outline" data-act="retry">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>');
      return;
    }
    if (!all.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Belum ada akun</strong>Klik Tambah Admin untuk membuat akun Admin baru.');
      return;
    }
    showState('');
    var pages = Math.max(1, Math.ceil(all.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE, slice = all.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      var self = isSelf(r);
      var rolePill = '<span class="pill pill--' + (r.role === 'guru' ? 'guru' : 'admin') + '">' + (r.role === 'guru' ? 'Guru' : 'Admin') + '</span>';
      var nama = self
        ? '<span class="ac-nama">' + esc(r.nama || r.username) + '</span><span class="ac-self">Akun Anda · Gunakan <button type="button" data-act="to-pw">“Ganti Password Saya”</button></span>'
        : (r.nama ? esc(r.nama) : '<span class="is-muted">-</span>');
      var badge = '<span class="badge ' + (r.aktif ? 'badge--aktif' : 'badge--nonaktif') + '">' + (r.aktif ? 'Aktif' : 'Nonaktif') + '</span>';
      var last = fmtLast(r.terakhir) || '<span class="is-muted">Belum pernah masuk</span>';
      var aksi;
      if (self) {
        aksi = '<span class="ac-none" aria-label="Tidak ada aksi untuk akun Anda">—</span>';
      } else {
        aksi = '<div class="row-actions">' +
          '<button type="button" class="icon-act" data-act="reset" data-id="' + esc(r.id) + '" aria-label="Reset password ' + esc(r.username) + '" title="Reset password">' + icon('key', 'ico--sm') + '</button>' +
          (r.aktif
            ? '<button type="button" class="icon-act icon-act--danger" data-act="status" data-id="' + esc(r.id) + '" aria-label="Nonaktifkan ' + esc(r.username) + '" title="Nonaktifkan">' + icon('ban', 'ico--sm') + '</button>'
            : '<button type="button" class="icon-act icon-act--ok" data-act="status" data-id="' + esc(r.id) + '" aria-label="Aktifkan ' + esc(r.username) + '" title="Aktifkan">' + icon('check', 'ico--sm') + '</button>') +
          '</div>';
      }
      return '<tr><td>' + esc(r.username) + '</td><td>' + rolePill + '</td><td>' + nama + '</td><td>' + badge + '</td><td>' + last + '</td><td>' + aksi + '</td></tr>';
    }).join('');

    elFooter.hidden = pages <= 1;
    if (pages > 1) {
      elCount.textContent = 'Menampilkan ' + (from + 1) + '–' + (from + slice.length) + ' dari ' + all.length + ' akun';
      var start = Math.max(1, Math.min(page - 2, pages - 4)), end = Math.min(pages, start + 4);
      var h = '<button type="button" class="pager__prev" data-page="' + (page - 1) + '" aria-label="Halaman sebelumnya"' + (page <= 1 ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
      for (var p = start; p <= end; p++) h += '<button type="button" data-page="' + p + '"' + (p === page ? ' class="is-current" aria-current="page"' : '') + ' aria-label="Halaman ' + p + '">' + p + '</button>';
      h += '<button type="button" data-page="' + (page + 1) + '" aria-label="Halaman berikutnya"' + (page >= pages ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
      elPager.innerHTML = h;
    }
  }

  elPager.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-page]');
    if (!b || b.disabled) return;
    page = Number(b.getAttribute('data-page')); render();
  });
  elState.addEventListener('click', function (ev) { if (ev.target.closest('[data-act="retry"]')) load(); });
  elBody.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-act]');
    if (!b) return;
    var act = b.getAttribute('data-act');
    if (act === 'to-pw') { setTab('password', true); $('ac-pw-old').focus(); return; }
    var item = byId(b.getAttribute('data-id'));
    if (!item) return;
    if (act === 'reset') openReset(item); else if (act === 'status') openStatus(item);
  });

  /* ---------- tampilkan / sembunyikan password ---------- */
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-toggle-pass]');
    if (!b) return;
    var inp = $(b.getAttribute('data-toggle-pass')), show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    b.textContent = show ? 'Sembunyikan' : 'Tampilkan';
    b.setAttribute('aria-pressed', String(show));
  });
  function hidePass(id) {
    var inp = $(id); inp.type = 'password'; inp.value = '';
    var b = document.querySelector('[data-toggle-pass="' + id + '"]');
    if (b) { b.textContent = 'Tampilkan'; b.setAttribute('aria-pressed', 'false'); }
  }
  function fieldError(id, text) {
    var box = $(id + '-err'), inp = $(id);
    box.textContent = text || ''; box.hidden = !text;
    inp.classList.toggle('is-invalid', !!text);
    inp.setAttribute('aria-invalid', text ? 'true' : 'false');
  }
  function note(box, text, info) { box.className = 'notice ' + (info ? 'notice--info' : 'notice--warn'); box.textContent = text || ''; box.hidden = !text; }

  /* ---------- Tambah Admin ---------- */
  var dAdd = $('ac-dlg-add'), fAdd = $('ac-form-add'), bAdd = $('ac-add-save'), mAdd = $('ac-add-msg'), addBusy = false;
  function setAddBusy(on) { addBusy = on; bAdd.disabled = on; bAdd.textContent = on ? 'Menyimpan...' : 'Simpan'; }
  btnAdd.addEventListener('click', function () {
    fieldError('ac-add-user', ''); fieldError('ac-add-pass', ''); note(mAdd, '');
    $('ac-add-user').value = ''; hidePass('ac-add-pass'); setAddBusy(false);
    dAdd.showModal(); $('ac-add-user').focus();
  });
  $('ac-add-cancel').addEventListener('click', function () { if (!addBusy) dAdd.close(); });
  dAdd.addEventListener('cancel', function (ev) { if (addBusy) ev.preventDefault(); });

  fAdd.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (addBusy) return;
    fieldError('ac-add-user', ''); fieldError('ac-add-pass', ''); note(mAdd, '');
    var user = $('ac-add-user').value.trim(), pass = $('ac-add-pass').value;
    if (!user) { fieldError('ac-add-user', 'Username wajib diisi'); $('ac-add-user').focus(); return; }
    if (user.length > USER_MAX) { fieldError('ac-add-user', 'Username maksimal 50 karakter'); $('ac-add-user').focus(); return; }
    if (pass.length < PASS_MIN) { fieldError('ac-add-pass', 'Password minimal 8 karakter'); $('ac-add-pass').focus(); return; }

    var body = {};
    body[SEND.username] = user; body[SEND.password] = pass; body[SEND.role] = 'admin'; // role tetap Admin
    setAddBusy(true);
    Api.post(E.akun, body).then(function () {
      dAdd.close(); hidePass('ac-add-pass');
      toast('Akun Admin ditambahkan');
      page = 1; return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      if (err && err.status === 409) fieldError('ac-add-user', err.message || 'Username sudah dipakai');
      else if (err && err.status === 422 && /password/i.test(err.message || '')) fieldError('ac-add-pass', err.message);
      else note(mAdd, errText(err));
    }).then(function () { setAddBusy(false); });
  });

  /* ---------- Reset password ---------- */
  var dRs = $('ac-dlg-reset'), fRs = $('ac-form-reset'), bRs = $('ac-rs-save'), mRs = $('ac-rs-msg'), rsBusy = false, rsItem = null;
  function setRsBusy(on) { rsBusy = on; bRs.disabled = on; bRs.textContent = on ? 'Menyimpan...' : 'Reset Password'; }
  function openReset(item) {
    rsItem = item; fieldError('ac-rs-pass', ''); note(mRs, ''); hidePass('ac-rs-pass'); setRsBusy(false);
    $('ac-rs-target').innerHTML = '<strong>' + esc(item.username) + '</strong>' + (item.nama ? ' · ' + esc(item.nama) : '');
    dRs.showModal(); $('ac-rs-pass').focus();
  }
  $('ac-rs-cancel').addEventListener('click', function () { if (!rsBusy) dRs.close(); });
  dRs.addEventListener('cancel', function (ev) { if (rsBusy) ev.preventDefault(); });

  fRs.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (rsBusy || !rsItem) return;
    fieldError('ac-rs-pass', ''); note(mRs, '');
    var pass = $('ac-rs-pass').value;
    if (pass.length < PASS_MIN) { fieldError('ac-rs-pass', 'Password minimal 8 karakter'); $('ac-rs-pass').focus(); return; }
    var body = {}; body[SEND.resetPass] = pass;
    setRsBusy(true);
    Api.put(itemUrl(rsItem.id) + '/reset-password', body).then(function () {
      dRs.close(); hidePass('ac-rs-pass');
      toast('Password diganti. Sesi lama akun ini berakhir.');
    }).catch(function (err) {
      if (err && err.status === 401) return;
      if (err && err.status === 422 && isSelf(rsItem)) note(mRs, 'Untuk akun Anda sendiri gunakan menu Ganti Password Saya');
      else note(mRs, errText(err));
    }).then(function () { setRsBusy(false); });
  });

  /* ---------- Nonaktifkan / Aktifkan ---------- */
  var dSt = $('ac-dlg-status'), mSt = $('ac-st-msg'), bOk = $('ac-st-ok'), bCancel = $('ac-st-cancel'), stBusy = false, stItem = null, stNext = false;
  function setStBusy(on) { stBusy = on; bOk.disabled = on; bOk.textContent = on ? 'Memproses...' : (stNext ? 'Aktifkan' : 'Nonaktifkan'); }
  function openStatus(item) {
    stItem = item; stNext = !item.aktif;
    $('ac-st-title').textContent = stNext ? 'Aktifkan Akun' : 'Nonaktifkan Akun';
    $('ac-st-text').textContent = stNext
      ? 'Akun ini akan dapat masuk kembali. Lanjutkan?'
      : (item.role === 'guru'
        ? 'Guru tidak dapat masuk sampai akun diaktifkan kembali. Jika Guru ini masih menjadi wali kelas, tetapkan wali pengganti sebelum kenaikan kelas. Lanjutkan?'
        : 'Akun ini tidak dapat masuk sampai diaktifkan kembali. Lanjutkan?');
    $('ac-st-target').innerHTML = '<strong>' + esc(item.username) + '</strong>' + (item.nama ? ' · ' + esc(item.nama) : '');
    bOk.className = 'btn ' + (stNext ? 'btn--primary' : 'btn--danger');
    bOk.hidden = false; bCancel.textContent = 'Batal'; note(mSt, ''); setStBusy(false);
    dSt.showModal(); bCancel.focus();
  }
  bCancel.addEventListener('click', function () { if (!stBusy) dSt.close(); });
  dSt.addEventListener('cancel', function (ev) { if (stBusy) ev.preventDefault(); });
  bOk.addEventListener('click', function () {
    if (stBusy || !stItem) return;
    var body = {}; body[SEND.status] = stNext ? 1 : 0;
    setStBusy(true);
    Api.put(itemUrl(stItem.id) + '/status', body).then(function () {
      dSt.close(); toast(stNext ? 'Akun diaktifkan' : 'Akun dinonaktifkan'); return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      // 422 (akun sendiri / Admin aktif terakhir): tampilkan pesan server
      note(mSt, errText(err), err && (err.status === 409 || err.status === 422));
      bOk.hidden = true; bCancel.textContent = 'Tutup';
    }).then(function () { setStBusy(false); });
  });

  /* ---------- Ganti Password Saya ---------- */
  var fPw = $('ac-form-pw'), bPw = $('ac-pw-save'), mPw = $('ac-pw-msg'), pwBusy = false;
  var PW_IDS = ['ac-pw-old', 'ac-pw-new', 'ac-pw-new2'];
  function setPwBusy(on) { pwBusy = on; bPw.disabled = on; bPw.textContent = on ? 'Menyimpan...' : 'Simpan Password'; }
  PW_IDS.forEach(function (id) { $(id).addEventListener('input', function () { fieldError(id, ''); }); });

  fPw.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (pwBusy) return;
    PW_IDS.forEach(function (id) { fieldError(id, ''); }); note(mPw, '');
    var oldP = $('ac-pw-old').value, newP = $('ac-pw-new').value, new2 = $('ac-pw-new2').value;
    if (!oldP) { fieldError('ac-pw-old', 'Password lama wajib diisi'); $('ac-pw-old').focus(); return; }
    if (newP.length < PASS_MIN) { fieldError('ac-pw-new', 'Password minimal 8 karakter'); $('ac-pw-new').focus(); return; }
    if (new2 !== newP) { fieldError('ac-pw-new2', 'Ulangi password harus sama dengan password baru'); $('ac-pw-new2').focus(); return; }

    var body = {}; body[SEND.pwLama] = oldP; body[SEND.pwBaru] = newP;
    setPwBusy(true);
    Api.post(E.ubahPassword, body).then(function (res) {
      // Server memberi token baru: ganti token lokal agar tidak terlempar ke login.
      var d = (res && res.data) || res || {};
      var token = d.token || d.access_token;
      if (token) Auth.save(token, Auth.user());
      PW_IDS.forEach(hidePass);
      toast('Password berhasil diganti');
    }).catch(function (err) {
      if (err && err.status === 401) return;
      note(mPw, errText(err));
      if (err && err.status === 422) { hidePass('ac-pw-old'); $('ac-pw-old').focus(); }
    }).then(function () { setPwBusy(false); });
  });

  setTab(window.location.hash.replace('#', ''));
  load();
})();