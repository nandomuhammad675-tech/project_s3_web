/* SI-KESA PWA Admin | Data Master: Ekstrakurikuler (dokumen 01 bagian 3.4.7)
 * Isian: Nama (wajib, maks 50), Deskripsi, Ikon (gambar), Guru pembina (pilih beberapa Guru).
 * Hapus: konfirmasi dulu; 409 ditampilkan di kotak informasi. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_ekskul', 'id_ekstrakurikuler', 'id'],
    nama: ['nama_ekskul', 'nama_ekstrakurikuler', 'nama'],
    deskripsi: ['deskripsi'],
    ikon: ['ikon', 'ikon_url'],
    pembina: ['guru_pembina', 'pembina', 'guru']
  };
  var SEND = { nama: 'nama_ekskul', deskripsi: 'deskripsi', ikon: 'ikon', guru: 'id_guru' };
  var PER_PAGE = 10;
  var NAMA_MAX = 50;
  var IKON_MAX_BYTES = 2 * 1024 * 1024;
  var IKON_MAX_SIDE = 256;

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
  function itemUrl(id) { return E.ekskul + '/' + encodeURIComponent(id); }

  /* Hanya terima gambar dari server (alamat atau data URL gambar). */
  function safeSrc(s) {
    s = String(s || '');
    return /^(https?:\/\/|\/|data:image\/(png|jpe?g|webp);base64,)/i.test(s) ? s : '';
  }

  function normGuru(g) {
    if (g && typeof g === 'object') {
      return { id: pick(g, ['id_guru', 'id'], null), nama: String(pick(g, ['nama_guru', 'nama'], '') || '').trim() };
    }
    return { id: null, nama: String(g == null ? '' : g).trim() };
  }
  function norm(r) {
    var p = pick(r, KEYS.pembina, []);
    if (typeof p === 'string') p = p ? p.split(',') : [];
    if (!Array.isArray(p)) p = [];
    return {
      id: pick(r, KEYS.id, null),
      nama: String(pick(r, KEYS.nama, '') || '').trim(),
      deskripsi: String(pick(r, KEYS.deskripsi, '') || '').trim(),
      ikon: safeSrc(pick(r, KEYS.ikon, '')),
      pembina: p.map(normGuru).filter(function (g) { return g.nama || g.id !== null; })
    };
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }
  function ikonHtml(src) {
    return '<span class="ikon-box">' + (src ? '<img src="' + esc(src) + '" alt="" loading="lazy">' : icon('book-open')) + '</span>';
  }

  var elBody = $('ek-body'), elState = $('ek-state'), elFooter = $('ek-footer'),
      elCount = $('ek-count'), elPager = $('ek-pager'), elSearch = $('ek-search'),
      elTable = $('ek-table');

  var all = [], query = '', sort = null, page = 1, loadState = 'loading';

  /* ---------- muat data ---------- */
  function fetchAll(url) {
    return Api.get(url).then(function (res) {
      var rows = toList(res);
      var last = Number(res && res.meta && res.meta.last_page) || 0;
      if (last <= 1) return rows;
      var jobs = [];
      for (var p = 2; p <= last; p++) jobs.push(Api.get(url, { page: p }));
      return Promise.all(jobs).then(function (rs) {
        rs.forEach(function (r) { rows = rows.concat(toList(r)); });
        return rows;
      });
    });
  }

  function load(silent) {
    if (!silent) { loadState = 'loading'; render(); }
    return fetchAll(E.ekskul).then(function (rows) {
      all = rows.map(norm);
      loadState = 'ok';
      render();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      loadState = 'error';
      render(errText(err));
    });
  }

  /* ---------- tampilan daftar ---------- */
  function filtered() {
    var q = query.trim().toLowerCase();
    var rows = all.filter(function (r) {
      if (!q) return true;
      var names = r.pembina.map(function (g) { return g.nama; }).join(' ');
      return (r.nama + ' ' + r.deskripsi + ' ' + names).toLowerCase().indexOf(q) > -1;
    });
    if (sort) {
      var m = sort.dir === 'desc' ? -1 : 1;
      rows = rows.slice().sort(function (a, b) { return m * a.nama.localeCompare(b.nama, 'id', { sensitivity: 'base' }); });
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
      for (i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>';
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
      showState('<strong>Belum ada ekstrakurikuler</strong>Klik Tambah Ekstrakurikuler untuk mulai mengisi.');
      return;
    }

    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Tidak ada hasil</strong>Tidak ada ekstrakurikuler yang cocok dengan “' + esc(query.trim()) + '”.' +
        '<br><button type="button" class="btn btn--outline" data-act="reset">' + icon('refresh', 'ico--sm') + ' Reset pencarian</button>');
      return;
    }
    showState('');

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE;
    var slice = rows.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      var desk = r.deskripsi ? '<span class="cell-clamp">' + esc(r.deskripsi) + '</span>' : '<span class="is-muted">-</span>';
      var names = r.pembina.map(function (g) { return g.nama; }).filter(Boolean);
      var guru = names.length ? names.map(esc).join('<br>') : '<span class="is-muted">-</span>';
      return '<tr>' +
        '<td>' + ikonHtml(r.ikon) + '</td>' +
        '<td>' + esc(r.nama) + '</td>' +
        '<td>' + desk + '</td>' +
        '<td>' + guru + '</td>' +
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
  $('ek-reset').addEventListener('click', resetView);

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

  /* ---------- daftar Guru untuk pilihan pembina ---------- */
  var guruList = null, guruState = 'idle'; // idle | loading | ok | error
  var elGuru = $('ek-guru-list');

  function loadGuru() {
    guruState = 'loading'; renderGuru();
    return fetchAll(E.guru).then(function (rows) {
      guruList = rows.map(function (r) {
        return { id: pick(r, ['id_guru', 'id'], null), nama: String(pick(r, ['nama_guru', 'nama'], '') || '').trim() };
      }).filter(function (g) { return g.id !== null && g.nama; });
      guruList.sort(function (a, b) { return a.nama.localeCompare(b.nama, 'id', { sensitivity: 'base' }); });
      guruState = 'ok'; renderGuru();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      guruState = 'error'; renderGuru(errText(err));
    });
  }

  function checkedIds() {
    return Array.prototype.map.call(elGuru.querySelectorAll('input[type="checkbox"]:checked'), function (c) { return c.value; });
  }

  function renderGuru(errMsg, selected) {
    var keep = selected || (guruState === 'ok' ? checkedIds() : []);
    if (guruState === 'loading' || guruState === 'idle') {
      elGuru.innerHTML = '<span class="skeleton skeleton--row"></span>'; return;
    }
    if (guruState === 'error') {
      elGuru.innerHTML = '<div class="pembina__state">' + esc(errMsg || 'Terjadi gangguan, coba lagi') +
        ' <button type="button" class="btn btn--soft btn--sm" data-act="retry-guru">' + icon('refresh', 'ico--sm') + ' Coba lagi</button></div>';
      return;
    }
    if (!guruList.length) {
      elGuru.innerHTML = '<div class="pembina__state">Belum ada data Guru. Tambahkan Guru di menu Data Master lebih dulu.</div>';
      return;
    }
    elGuru.innerHTML = guruList.map(function (g) {
      var on = keep.indexOf(String(g.id)) > -1;
      return '<label class="check pembina__item"><input type="checkbox" value="' + esc(g.id) + '"' + (on ? ' checked' : '') + '> <span>' + esc(g.nama) + '</span></label>';
    }).join('');
  }

  elGuru.addEventListener('click', function (ev) {
    if (ev.target.closest('[data-act="retry-guru"]')) loadGuru();
  });

  /* ---------- formulir tambah / ubah ---------- */
  var dlg = $('dlg-ekskul'), form = $('form-ekskul');
  var F = { nama: $('ek-nama'), deskripsi: $('ek-deskripsi'), ikon: $('ek-ikon') };
  var errBox = { nama: $('ek-nama-err'), deskripsi: $('ek-deskripsi-err'), ikon: $('ek-ikon-err') };
  var btnSave = $('ek-save'), elFormMsg = $('ek-form-msg');
  var elIkonPrev = $('ek-ikon-preview'), btnIkonClear = $('ek-ikon-clear');
  var editing = null, saving = false;
  /* ikonState: 'keep' (tidak diubah), 'new' (data URL terpilih), 'clear' (dihapus) */
  var ikonState = 'keep', ikonData = null;

  function fieldError(key, text) {
    var box = errBox[key], input = F[key];
    box.textContent = text || ''; box.hidden = !text;
    input.classList.toggle('is-invalid', !!text);
    input.setAttribute('aria-invalid', text ? 'true' : 'false');
  }
  function clearErrors() {
    Object.keys(errBox).forEach(function (k) { fieldError(k, ''); });
    elFormMsg.hidden = true; elFormMsg.textContent = '';
  }
  function setSaving(on) {
    saving = on; btnSave.disabled = on; btnSave.textContent = on ? 'Menyimpan...' : 'Simpan';
  }
  function showIkon(src) {
    elIkonPrev.innerHTML = src ? '<img alt="" src="' + esc(src) + '">' : icon('book-open');
    btnIkonClear.hidden = !src;
  }

  function openForm(item) {
    editing = item || null;
    clearErrors();
    $('ek-form-title').textContent = editing ? 'Ubah Ekstrakurikuler' : 'Tambah Ekstrakurikuler';
    $('ek-form-lead').textContent = editing ? 'Perbarui data ekstrakurikuler, lalu simpan.' : 'Isi data ekstrakurikuler, lalu simpan.';
    F.nama.value = editing ? editing.nama : '';
    F.deskripsi.value = editing ? editing.deskripsi : '';
    F.ikon.value = ''; ikonState = 'keep'; ikonData = null;
    showIkon(editing ? editing.ikon : '');

    var sel = editing ? editing.pembina.map(function (g) { return String(g.id); }) : [];
    if (guruState === 'ok') renderGuru('', sel);
    else loadGuru().then(function () { if (guruState === 'ok') renderGuru('', sel); });

    setSaving(false);
    dlg.showModal();
    F.nama.focus();
  }

  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('ek-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  /* Ikon: periksa tipe dan ukuran, kecilkan di browser lalu kirim sebagai data URL. */
  F.ikon.addEventListener('change', function () {
    fieldError('ikon', '');
    var file = F.ikon.files && F.ikon.files[0];
    if (!file) return;
    if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
      F.ikon.value = ''; fieldError('ikon', 'Format gambar tidak didukung'); return;
    }
    if (file.size > IKON_MAX_BYTES) {
      F.ikon.value = ''; fieldError('ikon', 'Ukuran gambar maksimal 2 MB'); return;
    }
    compress(file).then(function (dataUrl) {
      ikonState = 'new'; ikonData = dataUrl; showIkon(dataUrl);
    }).catch(function () {
      F.ikon.value = ''; fieldError('ikon', 'Format gambar tidak didukung');
    });
  });
  btnIkonClear.addEventListener('click', function () {
    F.ikon.value = ''; ikonState = 'clear'; ikonData = null; fieldError('ikon', ''); showIkon('');
  });

  function compress(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () {
        var w = img.naturalWidth, h = img.naturalHeight;
        if (!w || !h) { URL.revokeObjectURL(url); reject(); return; }
        var s = Math.min(1, IKON_MAX_SIDE / Math.max(w, h));
        var c = document.createElement('canvas');
        c.width = Math.round(w * s); c.height = Math.round(h * s);
        var ctx = c.getContext('2d');
        var keepAlpha = file.type !== 'image/jpeg';
        if (!keepAlpha) { ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height); }
        ctx.drawImage(img, 0, 0, c.width, c.height);
        URL.revokeObjectURL(url);
        resolve(keepAlpha ? c.toDataURL('image/png') : c.toDataURL('image/jpeg', 0.85));
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(); };
      img.src = url;
    });
  }

  /* Validasi di layar hanya pelengkap; server yang menentukan. */
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    clearErrors();

    var nama = F.nama.value.trim(), deskripsi = F.deskripsi.value.trim();
    if (!nama) { fieldError('nama', 'Nama wajib diisi'); F.nama.focus(); return; }
    if (nama.length > NAMA_MAX) { fieldError('nama', 'Nama maksimal 50 karakter'); F.nama.focus(); return; }
    if (guruState === 'error') {
      elFormMsg.textContent = 'Daftar Guru belum termuat. Coba lagi dulu agar guru pembina tidak terhapus.';
      elFormMsg.hidden = false; return;
    }

    var body = {};
    body[SEND.nama] = nama;
    body[SEND.deskripsi] = deskripsi || null;
    if (ikonState === 'new') body[SEND.ikon] = ikonData;
    else if (ikonState === 'clear') body[SEND.ikon] = null;
    if (guruState === 'ok') {
      body[SEND.guru] = checkedIds().map(function (v) { return /^\d+$/.test(v) ? Number(v) : v; });
    }

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.ekskul, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Ekstrakurikuler diperbarui' : 'Ekstrakurikuler ditambahkan');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      elFormMsg.textContent = errText(err);
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
    $('dlg-hapus-title').textContent = 'Hapus Ekstrakurikuler';
    dTarget.innerHTML = '<strong>' + esc(item.nama) + '</strong>';
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
      toast('Ekstrakurikuler dihapus');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = errText(err);
      if (err && err.status === 409) {
        // Kotak informasi (bukan error merah) + saran jalan keluar
        dMsg.className = 'notice notice--info';
        dMsg.innerHTML = esc(msg) + '<span class="notice__hint">Ekstrakurikuler masih terkait data lain, sehingga belum dapat dihapus.</span>';
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