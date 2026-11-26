(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = { id: ['id_mapel', 'id'], kode: ['kode_mapel', 'kode'], nama: ['nama_mapel', 'nama'] };
  var SEND = { kode: 'kode_mapel', nama: 'nama_mapel' };
  var PER_PAGE = 10;

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
  function norm(r) {
    return {
      id: pick(r, KEYS.id, null),
      kode: String(pick(r, KEYS.kode, '') || '').trim(),
      nama: String(pick(r, KEYS.nama, '') || '').trim()
    };
  }
  function itemUrl(id) { return E.mapel + '/' + encodeURIComponent(id); }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }

  var elBody = $('mp-body'), elState = $('mp-state'), elFooter = $('mp-footer'),
      elCount = $('mp-count'), elPager = $('mp-pager'), elSearch = $('mp-search'),
      elTable = $('mp-table');

  var all = [], query = '', sort = null, page = 1, loadState = 'loading';

  /* ---------- muat data ---------- */
  function fetchAll() {
    return Api.get(E.mapel).then(function (res) {
      var rows = toList(res);
      var last = Number(res && res.meta && res.meta.last_page) || 0;
      if (last <= 1) return rows;
      var jobs = [];
      for (var p = 2; p <= last; p++) jobs.push(Api.get(E.mapel, { page: p }));
      return Promise.all(jobs).then(function (rs) {
        rs.forEach(function (r) { rows = rows.concat(toList(r)); });
        return rows;
      });
    });
  }

  function load(silent) {
    if (!silent) { loadState = 'loading'; render(); }
    return fetchAll().then(function (rows) {
      all = rows.map(norm);
      loadState = 'ok';
      render();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      loadState = 'error';
      render(err && err.message ? err.message : 'Terjadi gangguan, coba lagi');
    });
  }

  /* ---------- tampilan daftar ---------- */
  function filtered() {
    var q = query.trim().toLowerCase();
    var rows = all.filter(function (r) {
      return !q || (r.kode + ' ' + r.nama).toLowerCase().indexOf(q) > -1;
    });
    if (sort) {
      var k = sort.key, m = sort.dir === 'desc' ? -1 : 1;
      rows = rows.slice().sort(function (a, b) { return m * a[k].localeCompare(b[k], 'id', { sensitivity: 'base' }); });
    }
    return rows;
  }

  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }

  function render(errMsg) {
    var i;
    Array.prototype.forEach.call(elTable.querySelectorAll('.th-sort'), function (b) {
      var on = sort && sort.key === b.getAttribute('data-sort');
      b.classList.toggle('is-sorted', !!on);
      b.querySelector('.th-sort__arrow').textContent = on ? (sort.dir === 'asc' ? '▲' : '▼') : '';
      b.parentNode.setAttribute('aria-sort', on ? (sort.dir === 'asc' ? 'ascending' : 'descending') : 'none');
    });

    if (loadState === 'loading') {
      var sk = '';
      for (i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="3"><span class="skeleton skeleton--row"></span></td></tr>';
      elBody.innerHTML = sk; showState(''); elFooter.hidden = true;
      return;
    }

    if (loadState === 'error') {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Data gagal dimuat</strong>' + esc(errMsg || 'Terjadi gangguan, coba lagi') +
        '<br><button type="button" class="btn btn--outline" data-act="retry">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>');
      return;
    }

    if (!all.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Belum ada mata pelajaran</strong>Klik Tambah Mata Pelajaran untuk mulai mengisi.');
      return;
    }

    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Tidak ada hasil</strong>Tidak ada mata pelajaran yang cocok dengan “' + esc(query.trim()) + '”.' +
        '<br><button type="button" class="btn btn--outline" data-act="reset">' + icon('refresh', 'ico--sm') + ' Reset pencarian</button>');
      return;
    }
    showState('');

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE;
    var slice = rows.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      var kode = r.kode ? esc(r.kode) : '<span class="is-muted">-</span>';
      return '<tr>' +
        '<td>' + kode + '</td>' +
        '<td>' + esc(r.nama) + '</td>' +
        '<td><div class="row-actions">' +
          '<button type="button" class="icon-act" data-act="edit" data-id="' + esc(r.id) + '" aria-label="Ubah ' + esc(r.nama) + '" title="Ubah">' + icon('pencil', 'ico--sm') + '</button>' +
          '<button type="button" class="icon-act icon-act--danger" data-act="del" data-id="' + esc(r.id) + '" aria-label="Hapus ' + esc(r.nama) + '" title="Hapus">' + icon('trash', 'ico--sm') + '</button>' +
        '</div></td></tr>';
    }).join('');

    elFooter.hidden = false;
    elCount.textContent = 'Menampilkan ' + (from + 1) + '–' + (from + slice.length) + ' dari ' + rows.length + ' data';
    renderPager(pages);
  }

  function renderPager(pages) {
    var start = Math.max(1, Math.min(page - 2, pages - 4));
    var end = Math.min(pages, start + 4);
    var h = '<button type="button" class="pager__prev" data-page="' + (page - 1) + '" aria-label="Halaman sebelumnya"' + (page <= 1 ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
    for (var p = start; p <= end; p++) {
      h += '<button type="button" data-page="' + p + '"' + (p === page ? ' class="is-current" aria-current="page"' : '') + ' aria-label="Halaman ' + p + '">' + p + '</button>';
    }
    h += '<button type="button" data-page="' + (page + 1) + '" aria-label="Halaman berikutnya"' + (page >= pages ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
    elPager.innerHTML = h;
  }

  /* ---------- cari, urut, reset, halaman ---------- */
  var timer = null;
  elSearch.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { query = elSearch.value; page = 1; render(); }, 150);
  });

  function resetView() { query = ''; sort = null; page = 1; elSearch.value = ''; render(); }
  $('mp-reset').addEventListener('click', resetView);

  elTable.querySelector('thead').addEventListener('click', function (ev) {
    var b = ev.target.closest('.th-sort');
    if (!b) return;
    var key = b.getAttribute('data-sort');
    if (!sort || sort.key !== key) sort = { key: key, dir: 'asc' };
    else if (sort.dir === 'asc') sort.dir = 'desc';
    else sort = null;
    page = 1; render();
  });

  elPager.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-page]');
    if (!b || b.disabled) return;
    page = Number(b.getAttribute('data-page')); render();
  });

  elState.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-act]');
    if (!b) return;
    if (b.getAttribute('data-act') === 'retry') load();
    if (b.getAttribute('data-act') === 'reset') resetView();
  });

  elBody.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-act]');
    if (!b) return;
    var item = byId(b.getAttribute('data-id'));
    if (!item) return;
    if (b.getAttribute('data-act') === 'edit') openForm(item);
    else openDelete(item);
  });

  /* ---------- formulir tambah / ubah ---------- */
  var dlg = $('dlg-mapel'), form = $('form-mapel');
  var elKode = $('mp-kode'), elNama = $('mp-nama'), btnSave = $('mp-save');
  var elKodeErr = $('mp-kode-err'), elNamaErr = $('mp-nama-err'), elFormMsg = $('mp-form-msg');
  var editing = null, saving = false;

  function fieldError(input, box, text) {
    box.textContent = text || ''; box.hidden = !text;
    input.classList.toggle('is-invalid', !!text);
    input.setAttribute('aria-invalid', text ? 'true' : 'false');
  }
  function clearErrors() {
    fieldError(elKode, elKodeErr, ''); fieldError(elNama, elNamaErr, '');
    elFormMsg.hidden = true; elFormMsg.textContent = '';
  }
  function setSaving(on) {
    saving = on; btnSave.disabled = on; btnSave.textContent = on ? 'Menyimpan...' : 'Simpan';
  }

  function openForm(item) {
    editing = item || null;
    clearErrors();
    $('mp-form-title').textContent = editing ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran';
    $('mp-form-lead').textContent = editing ? 'Perbarui data mata pelajaran, lalu simpan.' : 'Isi data mata pelajaran, lalu simpan.';
    elKode.value = editing ? editing.kode : '';
    elNama.value = editing ? editing.nama : '';
    setSaving(false);
    dlg.showModal();
    elNama.focus();
  }

  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('mp-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    clearErrors();

    var kode = elKode.value.trim(), nama = elNama.value.trim(), ok = true;
    if (!nama) { fieldError(elNama, elNamaErr, 'Nama wajib diisi'); ok = false; }
    else if (nama.length > 50) { fieldError(elNama, elNamaErr, 'Nama maksimal 50 karakter'); ok = false; }
    if (kode.length > 10) { fieldError(elKode, elKodeErr, 'Kode maksimal 10 karakter'); ok = false; }
    if (!ok) { (elNamaErr.hidden ? elKode : elNama).focus(); return; }

    var body = {};
    body[SEND.kode] = kode || null;
    body[SEND.nama] = nama;

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.mapel, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Mata pelajaran diperbarui' : 'Mata pelajaran ditambahkan');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      elFormMsg.textContent = err && err.message ? err.message : 'Terjadi gangguan, coba lagi';
      elFormMsg.hidden = false;
    }).then(function () { setSaving(false); });
  });

  /* ---------- hapus ---------- */
  var dDlg = $('dlg-hapus'), dTarget = $('dlg-hapus-target'), dMsg = $('dlg-hapus-msg'),
      dOk = $('dlg-hapus-ok'), dCancel = $('dlg-hapus-cancel');
  var deleting = null, delBusy = false;

  function setDelBusy(on) { delBusy = on; dOk.disabled = on; dOk.textContent = on ? 'Menghapus...' : 'Hapus'; }

  function openDelete(item) {
    deleting = item;
    $('dlg-hapus-title').textContent = 'Hapus Mata Pelajaran';
    dTarget.innerHTML = (item.kode ? esc(item.kode) + ' · ' : '') + '<strong>' + esc(item.nama) + '</strong>';
    dMsg.hidden = true; dMsg.textContent = '';
    dOk.hidden = false; dCancel.textContent = 'Batal';
    setDelBusy(false);
    dDlg.showModal();
    dCancel.focus();
  }

  dCancel.addEventListener('click', function () { if (!delBusy) dDlg.close(); });
  dDlg.addEventListener('cancel', function (ev) { if (delBusy) ev.preventDefault(); });

  dOk.addEventListener('click', function () {
    if (delBusy || !deleting) return;
    setDelBusy(true);
    Api.del(itemUrl(deleting.id)).then(function () {
      dDlg.close();
      toast('Mata pelajaran dihapus');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = err && err.message ? err.message : 'Terjadi gangguan, coba lagi';
      if (err && err.status === 409) {
        // Kotak informasi (bukan error merah) + saran jalan keluar
        dMsg.className = 'notice notice--info';
        dMsg.innerHTML = esc(msg) + '<span class="notice__hint">Mata pelajaran yang masih dipakai pada jadwal atau nilai tidak dapat dihapus.</span>';
        dOk.hidden = true; dCancel.textContent = 'Tutup';
      } else {
        dMsg.className = 'notice notice--warn';
        dMsg.textContent = msg;
      }
      dMsg.hidden = false;
    }).then(function () { setDelBusy(false); });
  });

  load();
})();