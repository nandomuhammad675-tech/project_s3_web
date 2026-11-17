/* SI-KESA PWA Admin | Konten Website: Mading (dokumen 01 bagian 3.7)
 * Isian: Judul (wajib, maks 100), Kategori (maks 50), Deskripsi, Nama Penulis (maks 100),
 * Gambar (wajib), Tampilkan (ya/tidak). Hapus: konfirmasi dulu; 409 di kotak informasi. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var E = window.SIKESA.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_mading', 'id'],
    judul: ['judul_karya', 'judul'],
    kategori: ['kategori'],
    deskripsi: ['deskripsi'],
    penulis: ['nama_penulis', 'penulis'],
    gambar: ['file_gambar', 'gambar', 'gambar_url'],
    tampil: ['status_tampil']
  };
  var SEND = { judul: 'judul_karya', kategori: 'kategori', deskripsi: 'deskripsi', penulis: 'nama_penulis', gambar: 'file_gambar', tampil: 'status_tampil' };
  var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var PER_PAGE = 10, JUDUL_MAX = 100, KAT_MAX = 50, PENULIS_MAX = 100, MAX_BYTES = 2 * 1024 * 1024, MAX_SIDE = 1280;
  var PILL_KNOWN = { artikel: 'green', puisi: 'blue', cerpen: 'amber', gambar: 'purple', karya: 'amber' };
  var PILL_CYCLE = ['amber', 'purple', 'green', 'blue'];

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
  function itemUrl(id) { return E.mading + '/' + encodeURIComponent(id); }
  function safeSrc(s) {
    if (/^(uploads|aset|media|assets)\//.test(String(s || '')) && window.SikesaApi) return window.SikesaApi.fileUrl(String(s));
    s = String(s || '');
    return /^(https?:\/\/|\/|data:image\/(png|jpe?g|webp);base64,)/i.test(s) ? s : '';
  }
  function fmtDate(iso) {
    var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso || '');
    return m ? Number(m[3]) + ' ' + BULAN[Number(m[2]) - 1] + ' ' + m[1] : '';
  }
  function pillClass(kat) {
    var k = kat.toLowerCase();
    if (PILL_KNOWN[k]) return PILL_KNOWN[k];
    var h = 0;
    for (var i = 0; i < k.length; i++) h = (h * 31 + k.charCodeAt(i)) % 997;
    return PILL_CYCLE[h % PILL_CYCLE.length];
  }

  function norm(r) {
    var tampil = pick(r, KEYS.tampil, 1);
    return {
      id: pick(r, KEYS.id, null),
      judul: String(pick(r, KEYS.judul, '') || '').trim(),
      kategori: String(pick(r, KEYS.kategori, '') || '').trim(),
      deskripsi: String(pick(r, KEYS.deskripsi, '') || '').trim(),
      penulis: String(pick(r, KEYS.penulis, '') || '').trim(),
      gambar: safeSrc(pick(r, KEYS.gambar, '')),
      tampil: tampil === true || Number(tampil) === 1
    };
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }

  var elBody = $('md-body'), elState = $('md-state'), elFooter = $('md-footer'), elCount = $('md-count'),
      elPager = $('md-pager'), elSearch = $('md-search'), elKat = $('md-kategori'), elTable = $('md-table');
  var all = [], query = '', kategori = '', sort = { key: 'status', dir: 'desc' }, page = 1, loadState = 'loading';

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
    return fetchAll(E.mading).then(function (rows) {
      all = rows.map(norm);
      loadState = 'ok';
      buildKategori();
      render();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      loadState = 'error';
      render(errText(err));
    });
  }

  /* Daftar kategori diambil dari data (teks bebas), untuk filter dan saran isian. */
  function kategoriList() {
    var seen = {}, out = [];
    all.forEach(function (r) {
      var k = r.kategori;
      if (k && !seen[k.toLowerCase()]) { seen[k.toLowerCase()] = 1; out.push(k); }
    });
    return out.sort(function (a, b) { return a.localeCompare(b, 'id', { sensitivity: 'base' }); });
  }
  function buildKategori() {
    var list = kategoriList();
    if (kategori && list.map(function (k) { return k.toLowerCase(); }).indexOf(kategori.toLowerCase()) === -1) kategori = '';
    elKat.innerHTML = '<option value="">Semua kategori</option>' + list.map(function (k) {
      return '<option value="' + esc(k) + '"' + (k === kategori ? ' selected' : '') + '>' + esc(k) + '</option>';
    }).join('');
    $('md-kat-list').innerHTML = list.map(function (k) { return '<option value="' + esc(k) + '">'; }).join('');
  }

  /* ---------- tabel ---------- */
  function filtered() {
    var q = query.trim().toLowerCase();
    var rows = all.filter(function (r) {
      if (kategori && r.kategori.toLowerCase() !== kategori.toLowerCase()) return false;
      return !q || (r.judul + ' ' + r.penulis).toLowerCase().indexOf(q) > -1;
    });
    var m = sort.dir === 'desc' ? -1 : 1;
    return rows.slice().sort(function (a, b) {
      if (sort.key === 'status') return m * ((a.tampil ? 1 : 0) - (b.tampil ? 1 : 0));
      return m * String(a[sort.key]).localeCompare(String(b[sort.key]), 'id', { sensitivity: 'base' });
    });
  }
  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }
  function btn(act, label, ico) { return '<br><button type="button" class="btn btn--outline" data-act="' + act + '">' + icon(ico, 'ico--sm') + ' ' + label + '</button>'; }

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
      for (i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>';
      elBody.innerHTML = sk; showState(''); elFooter.hidden = true; return;
    }
    if (loadState === 'error') {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Data gagal dimuat</strong>' + esc(errMsg || 'Terjadi gangguan, coba lagi') + btn('retry', 'Coba lagi', 'refresh'));
      return;
    }
    if (!all.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Belum ada karya</strong>Klik Tambah Karya untuk mulai mengisi mading siswa.');
      return;
    }
    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Tidak ada hasil</strong>Tidak ada karya yang cocok dengan pencarian atau filter.' + btn('reset', 'Reset pencarian', 'refresh'));
      return;
    }
    showState('');

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE, slice = rows.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      var thumb = '<span class="mad-thumb">' + (r.gambar ? '<img src="' + esc(r.gambar) + '" alt="" loading="lazy">' : icon('image')) + '</span>';
      var kat = r.kategori ? '<span class="pill pill--' + pillClass(r.kategori) + '">' + esc(r.kategori) + '</span>' : '<span class="is-muted">-</span>';
      var pen = r.penulis ? esc(r.penulis) : '<span class="is-muted">-</span>';
      var tgl = r.tampil ? '<span class="badge badge--aktif">Ditampilkan</span>' : '<span class="badge badge--draf">Draf</span>';
      return '<tr><td>' + thumb + '</td>' +
        '<td>' + esc(r.judul) + '' + '</td>' +
        '<td>' + kat + '</td><td>' + pen + '</td><td>' + tgl + '</td>' +
        '<td><div class="row-actions">' +
        '<button type="button" class="icon-act" data-act="edit" data-id="' + esc(r.id) + '" aria-label="Ubah ' + esc(r.judul) + '" title="Ubah">' + icon('pencil', 'ico--sm') + '</button>' +
        '<button type="button" class="icon-act icon-act--danger" data-act="del" data-id="' + esc(r.id) + '" aria-label="Hapus ' + esc(r.judul) + '" title="Hapus">' + icon('trash', 'ico--sm') + '</button>' +
        '</div></td></tr>';
    }).join('');

    elFooter.hidden = false;
    elCount.textContent = 'Menampilkan ' + (from + 1) + '–' + (from + slice.length) + ' dari ' + rows.length + ' data';
    var start = Math.max(1, Math.min(page - 2, pages - 4)), end = Math.min(pages, start + 4);
    var h = '<button type="button" class="pager__prev" data-page="' + (page - 1) + '" aria-label="Halaman sebelumnya"' + (page <= 1 ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
    for (var p = start; p <= end; p++) h += '<button type="button" data-page="' + p + '"' + (p === page ? ' class="is-current" aria-current="page"' : '') + ' aria-label="Halaman ' + p + '">' + p + '</button>';
    h += '<button type="button" data-page="' + (page + 1) + '" aria-label="Halaman berikutnya"' + (page >= pages ? ' disabled' : '') + '>' + icon('chevron') + '</button>';
    elPager.innerHTML = h;
  }

  /* ---------- cari, filter, urut, halaman ---------- */
  var timer = null;
  elSearch.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { query = elSearch.value; page = 1; render(); }, 150);
  });
  elKat.addEventListener('change', function () { kategori = elKat.value; page = 1; render(); });
  function resetView() {
    query = ''; kategori = ''; sort = { key: 'status', dir: 'desc' }; page = 1;
    elSearch.value = ''; elKat.value = ''; render();
  }
  $('md-reset').addEventListener('click', resetView);
  elTable.querySelector('thead').addEventListener('click', function (ev) {
    var b = ev.target.closest('.th-sort');
    if (!b) return;
    var key = b.getAttribute('data-sort');
    sort = sort.key === key ? { key: key, dir: sort.dir === 'asc' ? 'desc' : 'asc' } : { key: key, dir: 'asc' };
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
    if (b.getAttribute('data-act') === 'retry') load(); else resetView();
  });
  elBody.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-act]');
    if (!b) return;
    var item = byId(b.getAttribute('data-id'));
    if (!item) return;
    if (b.getAttribute('data-act') === 'edit') openForm(item); else openDelete(item);
  });

  /* ---------- formulir ---------- */
  var dlg = $('dlg-mading'), form = $('form-mading');
  var F = { judul: $('md-judul'), kategori: $('md-kat'), penulis: $('md-penulis'), deskripsi: $('md-deskripsi'), gambar: $('md-gambar') };
  var errBox = { judul: $('md-judul-err'), kategori: $('md-kat-err'), penulis: $('md-penulis-err'), deskripsi: $('md-deskripsi-err'), gambar: $('md-gambar-err') };
  var elTampil = $('md-tampil'), btnSave = $('md-save'), elFormMsg = $('md-form-msg'), elPrev = $('md-gambar-preview');
  var editing = null, saving = false, gambarData = null, gambarAda = false;

  function fieldError(key, text) {
    errBox[key].textContent = text || ''; errBox[key].hidden = !text;
    F[key].classList.toggle('is-invalid', !!text);
    F[key].setAttribute('aria-invalid', text ? 'true' : 'false');
  }
  function clearErrors() {
    Object.keys(errBox).forEach(function (k) { fieldError(k, ''); });
    elFormMsg.hidden = true; elFormMsg.textContent = '';
  }
  function setSaving(on) { saving = on; btnSave.disabled = on; btnSave.textContent = on ? 'Menyimpan...' : 'Simpan'; }
  function showGambar(src) { elPrev.innerHTML = src ? '<img alt="" src="' + esc(src) + '">' : icon('upload'); }

  function openForm(item) {
    editing = item || null;
    clearErrors();
    $('md-form-title').textContent = editing ? 'Ubah Karya' : 'Tambah Karya';
    $('md-form-lead').textContent = editing ? 'Perbarui data karya siswa, lalu simpan.' : 'Isi data karya siswa, lalu simpan.';
    F.judul.value = editing ? editing.judul : '';
    F.kategori.value = editing ? editing.kategori : '';
    F.penulis.value = editing ? editing.penulis : '';
    F.deskripsi.value = editing ? editing.deskripsi : '';
    elTampil.checked = editing ? editing.tampil : true;
    F.gambar.value = ''; gambarData = null; gambarAda = !!(editing && editing.gambar);
    showGambar(editing ? editing.gambar : '');
    setSaving(false);
    dlg.showModal();
    F.judul.focus();
  }
  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('md-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  F.gambar.addEventListener('change', function () {
    fieldError('gambar', '');
    var file = F.gambar.files && F.gambar.files[0];
    if (!file) return;
    if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
      F.gambar.value = ''; fieldError('gambar', 'Format gambar tidak didukung'); return;
    }
    if (file.size > MAX_BYTES) {
      F.gambar.value = ''; fieldError('gambar', 'Ukuran gambar maksimal 2 MB'); return;
    }
    compress(file).then(function (d) {
      gambarData = d; gambarAda = true; showGambar(d);
    }).catch(function () {
      F.gambar.value = ''; fieldError('gambar', 'Format gambar tidak didukung');
    });
  });

  function compress(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file), img = new Image();
      img.onload = function () {
        var w = img.naturalWidth, h = img.naturalHeight;
        if (!w || !h) { URL.revokeObjectURL(url); reject(); return; }
        var s = Math.min(1, MAX_SIDE / Math.max(w, h)), c = document.createElement('canvas');
        c.width = Math.round(w * s); c.height = Math.round(h * s);
        var ctx = c.getContext('2d');
        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
        ctx.drawImage(img, 0, 0, c.width, c.height);
        URL.revokeObjectURL(url);
        resolve(c.toDataURL('image/jpeg', 0.82));
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
    var judul = F.judul.value.trim(), kat = F.kategori.value.trim(), pen = F.penulis.value.trim();
    if (!judul) { fieldError('judul', 'Judul wajib diisi'); F.judul.focus(); return; }
    if (judul.length > JUDUL_MAX) { fieldError('judul', 'Judul maksimal 100 karakter'); F.judul.focus(); return; }
    if (kat.length > KAT_MAX) { fieldError('kategori', 'Kategori maksimal 50 karakter'); F.kategori.focus(); return; }
    if (pen.length > PENULIS_MAX) { fieldError('penulis', 'Nama penulis maksimal 100 karakter'); F.penulis.focus(); return; }
    if (!gambarAda) { fieldError('gambar', 'Gambar wajib diisi'); F.gambar.focus(); return; }

    var body = {};
    body[SEND.judul] = judul;
    body[SEND.kategori] = kat || null;
    body[SEND.deskripsi] = F.deskripsi.value.trim() || null;
    body[SEND.penulis] = pen || null;
    body[SEND.tampil] = elTampil.checked ? 1 : 0;
    if (gambarData) body[SEND.gambar] = gambarData; // saat ubah tanpa ganti gambar, tidak dikirim

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.mading, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Karya diperbarui' : 'Karya ditambahkan');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      elFormMsg.textContent = errText(err); elFormMsg.hidden = false;
    }).then(function () { setSaving(false); });
  });

  /* ---------- hapus ---------- */
  var dDlg = $('dlg-hapus'), dTarget = $('dlg-hapus-target'), dMsg = $('dlg-hapus-msg'),
      dOk = $('dlg-hapus-ok'), dCancel = $('dlg-hapus-cancel');
  var deleting = null, delBusy = false;
  function setDelBusy(on) { delBusy = on; dOk.disabled = on; dOk.textContent = on ? 'Menghapus...' : 'Hapus'; }

  function openDelete(item) {
    deleting = item;
    $('dlg-hapus-title').textContent = 'Hapus Karya';
    dTarget.innerHTML = '<strong>' + esc(item.judul) + '</strong>';
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
      dDlg.close(); toast('Karya dihapus'); return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = errText(err);
      if (err && err.status === 409) {
        dMsg.className = 'notice notice--info';
        dMsg.innerHTML = esc(msg) + '<span class="notice__hint">Karya masih terkait data lain. Anda bisa menonaktifkan “Tampilkan di website” lewat tombol Ubah.</span>';
        dOk.hidden = true; dCancel.textContent = 'Tutup';
      } else {
        dMsg.className = 'notice notice--warn'; dMsg.textContent = msg;
      }
      dMsg.hidden = false;
    }).then(function () { setDelBusy(false); });
  });

  load();
})();