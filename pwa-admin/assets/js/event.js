/* SI-KESA PWA Admin | Konten Website: Event & Galeri (dokumen 01 bagian 3.7)
 * Isian Event: Nama (wajib, maks 100), Kategori (kegiatan/event/prestasi/kolaborasi, wajib),
 * Tanggal (opsional), Deskripsi, Banner (gambar), Tampilkan di website (ya/tidak).
 * Tanggal tampil format Indonesia, dikirim YYYY-MM-DD. Hapus: konfirmasi dulu; 409 di kotak informasi. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var E = window.SIKESA.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_event', 'id'],
    nama: ['nama_event', 'nama'],
    kategori: ['kategori'],
    tanggal: ['tanggal'],
    deskripsi: ['deskripsi'],
    banner: ['banner', 'banner_url'],
    tampil: ['status_tampil'],
    foto: ['jumlah_foto', 'total_galeri']
  };
  var SEND = { nama: 'nama_event', kategori: 'kategori', tanggal: 'tanggal', deskripsi: 'deskripsi', banner: 'banner', tampil: 'status_tampil' };
  var KAT = { kegiatan: 'Kegiatan', event: 'Event', prestasi: 'Prestasi', kolaborasi: 'Kolaborasi' };
  var KAT_CARD = { kegiatan: 'olive', event: 'blue', prestasi: 'blue', kolaborasi: 'green' };
  var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var PER_PAGE = 10, NAMA_MAX = 100, MAX_BYTES = 2 * 1024 * 1024, MAX_SIDE = 1280;

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
  function itemUrl(id) { return E.event + '/' + encodeURIComponent(id); }
  function safeSrc(s) {
    s = String(s || '');
    return /^(https?:\/\/|\/|data:image\/(png|jpe?g|webp);base64,)/i.test(s) ? s : '';
  }
  function fmtDate(iso) {
    var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso || '');
    return m ? Number(m[3]) + ' ' + BULAN[Number(m[2]) - 1] + ' ' + m[1] : '';
  }

  function norm(r) {
    var kat = String(pick(r, KEYS.kategori, '') || '').toLowerCase();
    var tampil = pick(r, KEYS.tampil, 1);
    var foto = pick(r, KEYS.foto, null);
    return {
      id: pick(r, KEYS.id, null),
      nama: String(pick(r, KEYS.nama, '') || '').trim(),
      kategori: KAT[kat] ? kat : '',
      tanggal: String(pick(r, KEYS.tanggal, '') || '').slice(0, 10),
      deskripsi: String(pick(r, KEYS.deskripsi, '') || '').trim(),
      banner: safeSrc(pick(r, KEYS.banner, '')),
      tampil: tampil === true || Number(tampil) === 1,
      foto: foto === null ? null : Number(foto) || 0
    };
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }

  var elBody = $('ev-body'), elState = $('ev-state'), elFooter = $('ev-footer'), elCount = $('ev-count'),
      elPager = $('ev-pager'), elSearch = $('ev-search'), elKat = $('ev-kategori'), elTable = $('ev-table'),
      elFeat = $('ev-featured');
  var all = [], query = '', kategori = '', sort = { key: 'tanggal', dir: 'desc' }, page = 1, loadState = 'loading';

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
    return fetchAll(E.event).then(function (rows) {
      all = rows.map(norm);
      loadState = 'ok';
      render();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      loadState = 'error';
      render(errText(err));
    });
  }

  /* ---------- kartu event terbaru ---------- */
  function byDateDesc(a, b) { return (b.tanggal || '').localeCompare(a.tanggal || ''); }
  function renderFeatured() {
    var top = all.slice().sort(byDateDesc).slice(0, 3);
    if (loadState !== 'ok' || !top.length) { elFeat.hidden = true; elFeat.innerHTML = ''; return; }
    elFeat.innerHTML = top.map(function (r) {
      var tone = KAT_CARD[r.kategori] || 'olive';
      if (r.kategori === 'kegiatan' && top.indexOf(r) === 1) tone = 'amber';
      var meta = [];
      if (r.foto !== null) meta.push(r.foto + ' foto');
      else if (r.tanggal) meta.push(fmtDate(r.tanggal));
      return '<article class="ev-card ev-card--' + tone + (r.tampil ? '' : ' ev-card--draft') + '">' +
        (r.banner ? '<img class="ev-card__img" src="' + esc(r.banner) + '" alt="" loading="lazy">' : '') +
        '<div class="ev-card__shade"></div><div class="ev-card__body">' +
        (r.kategori ? '<span class="pill pill--' + r.kategori + '">' + KAT[r.kategori] + '</span>' : '') +
        '<h2 class="ev-card__title">' + esc(r.nama) + '</h2>' +
        '<div class="ev-card__meta">' + esc(meta.join(' · ')) + '</div></div></article>';
    }).join('');
    elFeat.hidden = false;
  }

  /* ---------- tabel ---------- */
  function filtered() {
    var q = query.trim().toLowerCase();
    var rows = all.filter(function (r) {
      if (kategori && r.kategori !== kategori) return false;
      return !q || (r.nama + ' ' + r.deskripsi).toLowerCase().indexOf(q) > -1;
    });
    var m = sort.dir === 'desc' ? -1 : 1;
    return rows.slice().sort(function (a, b) {
      if (sort.key === 'tanggal') return m * (a.tanggal || '').localeCompare(b.tanggal || '');
      return m * a.nama.localeCompare(b.nama, 'id', { sensitivity: 'base' });
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
    renderFeatured();

    if (loadState === 'loading') {
      var sk = '';
      for (i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>';
      elBody.innerHTML = sk; showState(''); elFooter.hidden = true; return;
    }
    if (loadState === 'error') {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Data gagal dimuat</strong>' + esc(errMsg || 'Terjadi gangguan, coba lagi') + btn('retry', 'Coba lagi', 'refresh'));
      return;
    }
    if (!all.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Belum ada event</strong>Klik Tambah Event untuk mulai mempublikasikan kegiatan sekolah.');
      return;
    }
    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Tidak ada hasil</strong>Tidak ada event yang cocok dengan pencarian atau filter.' + btn('reset', 'Reset pencarian', 'refresh'));
      return;
    }
    showState('');

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE, slice = rows.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      var kat = r.kategori ? '<span class="pill pill--' + r.kategori + '">' + KAT[r.kategori] + '</span>' : '<span class="is-muted">-</span>';
      var tgl = r.tanggal ? esc(fmtDate(r.tanggal)) : '<span class="is-muted">-</span>';
      var st = '<span class="badge ' + (r.tampil ? 'badge--terbit' : 'badge--draf') + '">' + (r.tampil ? 'Terbit' : 'Draf') + '</span>';
      return '<tr><td>' + esc(r.nama) + '</td><td>' + kat + '</td><td>' + tgl + '</td><td>' + st + '</td>' +
        '<td><div class="row-actions">' +
        '<button type="button" class="icon-act" data-act="edit" data-id="' + esc(r.id) + '" aria-label="Ubah ' + esc(r.nama) + '" title="Ubah">' + icon('pencil', 'ico--sm') + '</button>' +
        '<button type="button" class="icon-act icon-act--danger" data-act="del" data-id="' + esc(r.id) + '" aria-label="Hapus ' + esc(r.nama) + '" title="Hapus">' + icon('trash', 'ico--sm') + '</button>' +
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
    query = ''; kategori = ''; sort = { key: 'tanggal', dir: 'desc' }; page = 1;
    elSearch.value = ''; elKat.value = ''; render();
  }
  $('ev-reset').addEventListener('click', resetView);
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
  var dlg = $('dlg-event'), form = $('form-event');
  var F = { nama: $('ev-nama'), kategori: $('ev-kat'), tanggal: $('ev-tanggal'), deskripsi: $('ev-deskripsi'), banner: $('ev-banner') };
  var errBox = { nama: $('ev-nama-err'), kategori: $('ev-kat-err'), tanggal: $('ev-tanggal-err'), deskripsi: $('ev-deskripsi-err'), banner: $('ev-banner-err') };
  var elTampil = $('ev-tampil'), btnSave = $('ev-save'), elFormMsg = $('ev-form-msg');
  var elPrev = $('ev-banner-preview'), btnClear = $('ev-banner-clear');
  var editing = null, saving = false, bannerState = 'keep', bannerData = null; // keep | new | clear

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
  function showBanner(src) {
    elPrev.innerHTML = src ? '<img alt="" src="' + esc(src) + '">' : icon('upload');
    btnClear.hidden = !src;
  }

  function openForm(item) {
    editing = item || null;
    clearErrors();
    $('ev-form-title').textContent = editing ? 'Ubah Event' : 'Tambah Event';
    $('ev-form-lead').textContent = editing ? 'Perbarui data event, lalu simpan.' : 'Isi data event, lalu simpan.';
    F.nama.value = editing ? editing.nama : '';
    F.kategori.value = editing ? editing.kategori : '';
    F.tanggal.value = editing ? editing.tanggal : '';
    F.deskripsi.value = editing ? editing.deskripsi : '';
    elTampil.checked = editing ? editing.tampil : true;
    F.banner.value = ''; bannerState = 'keep'; bannerData = null;
    showBanner(editing ? editing.banner : '');
    setSaving(false);
    dlg.showModal();
    F.nama.focus();
  }
  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('ev-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  F.banner.addEventListener('change', function () {
    fieldError('banner', '');
    var file = F.banner.files && F.banner.files[0];
    if (!file) return;
    if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
      F.banner.value = ''; fieldError('banner', 'Format gambar tidak didukung'); return;
    }
    if (file.size > MAX_BYTES) {
      F.banner.value = ''; fieldError('banner', 'Ukuran gambar maksimal 2 MB'); return;
    }
    compress(file).then(function (d) {
      bannerState = 'new'; bannerData = d; showBanner(d);
    }).catch(function () {
      F.banner.value = ''; fieldError('banner', 'Format gambar tidak didukung');
    });
  });
  btnClear.addEventListener('click', function () {
    F.banner.value = ''; bannerState = 'clear'; bannerData = null; fieldError('banner', ''); showBanner('');
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
    var nama = F.nama.value.trim();
    if (!nama) { fieldError('nama', 'Nama wajib diisi'); F.nama.focus(); return; }
    if (nama.length > NAMA_MAX) { fieldError('nama', 'Nama maksimal 100 karakter'); F.nama.focus(); return; }
    if (!KAT[F.kategori.value]) { fieldError('kategori', 'Kategori wajib dipilih'); F.kategori.focus(); return; }

    var body = {};
    body[SEND.nama] = nama;
    body[SEND.kategori] = F.kategori.value;
    body[SEND.tanggal] = F.tanggal.value || null; // YYYY-MM-DD
    body[SEND.deskripsi] = F.deskripsi.value.trim() || null;
    body[SEND.tampil] = elTampil.checked ? 1 : 0;
    if (bannerState === 'new') body[SEND.banner] = bannerData;
    else if (bannerState === 'clear') body[SEND.banner] = null;

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.event, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Event diperbarui' : 'Event ditambahkan');
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
    $('dlg-hapus-title').textContent = 'Hapus Event';
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
      dDlg.close(); toast('Event dihapus'); return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = errText(err);
      if (err && err.status === 409) {
        dMsg.className = 'notice notice--info';
        dMsg.innerHTML = esc(msg) + '<span class="notice__hint">Event masih terkait data lain. Anda bisa menonaktifkan “Tampilkan di website” lewat tombol Ubah.</span>';
        dOk.hidden = true; dCancel.textContent = 'Tutup';
      } else {
        dMsg.className = 'notice notice--warn'; dMsg.textContent = msg;
      }
      dMsg.hidden = false;
    }).then(function () { setDelBusy(false); });
  });

  load();
})();