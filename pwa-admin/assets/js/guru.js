/* SI-KESA PWA Admin | Data Master: Guru (dokumen 01 bagian 3.4)
 * Tabel: Nama, NIP, Jabatan, Status Akun, Kelas Tahun Aktif, Aksi.
 * Membuat Guru sekaligus membuat akun role=guru (server). Email dan telepon tidak tampil di publik. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_guru', 'id'],
    nama: ['nama_guru', 'nama'],
    nip: ['nip'],
    jabatan: ['jabatan'],
    foto: ['foto', 'foto_url'],
    email: ['email'],
    telepon: ['no_telepon', 'telepon'],
    username: ['username'],
    status: ['status_akun', 'akun_aktif', 'status_aktif', 'aktif', 'status'],
    kelas: ['kelas_tahun_aktif', 'nama_kelas', 'kelas']
  };
  var SEND = {
    nama: 'nama_guru', nip: 'nip', jabatan: 'jabatan', foto: 'foto',
    email: 'email', telepon: 'no_telepon', username: 'username', password: 'password'
  };
  var PER_PAGE = 10;
  var FOTO_MAX_SIDE = 800;

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
  function flag(v) {
    if (typeof v === 'boolean') return v;
    if (typeof v === 'number') return v === 1;
    var s = String(v == null ? '' : v).toLowerCase();
    if (['1', 'true', 'aktif', 'active', 'ya'].indexOf(s) > -1) return true;
    return false;
  }
  function icon(n, cls) { return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
  function toast(t, type) { if (window.SikesaUI) window.SikesaUI.toast(t, type); }
  function errText(err) { return err && err.message ? err.message : 'Terjadi gangguan, coba lagi'; }
  function itemUrl(id) { return E.guru + '/' + encodeURIComponent(id); }

  function norm(r) {
    var kelas = pick(r, KEYS.kelas, '');
    if (kelas && typeof kelas === 'object') kelas = kelas.nama_kelas || kelas.nama || '';
    return {
      id: pick(r, KEYS.id, null),
      nama: String(pick(r, KEYS.nama, '') || '').trim(),
      nip: String(pick(r, KEYS.nip, '') || '').trim(),
      jabatan: String(pick(r, KEYS.jabatan, '') || '').trim(),
      foto: String(pick(r, KEYS.foto, '') || ''),
      email: String(pick(r, KEYS.email, '') || '').trim(),
      telepon: String(pick(r, KEYS.telepon, '') || '').trim(),
      username: String(pick(r, KEYS.username, '') || '').trim(),
      aktif: flag(pick(r, KEYS.status, 0)),
      kelas: String(kelas || '').trim()
    };
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }

  /* ---------- elemen & state ---------- */
  var elBody = $('gr-body'), elState = $('gr-state'), elFooter = $('gr-footer'),
      elCount = $('gr-count'), elPager = $('gr-pager'), elSearch = $('gr-search'),
      elStatus = $('gr-status'), elTable = $('gr-table');

  var all = [], query = '', statusFilter = '', sort = null, page = 1, loadState = 'loading';

  /* ---------- muat data ---------- */
  function fetchAll() {
    return Api.get(E.guru).then(function (res) {
      var rows = toList(res);
      var last = Number(res && res.meta && res.meta.last_page) || 0;
      if (last <= 1) return rows;
      var jobs = [];
      for (var p = 2; p <= last; p++) jobs.push(Api.get(E.guru, { page: p }));
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
      render(errText(err));
    });
  }

  /* ---------- tampilan daftar ---------- */
  function filtered() {
    var q = query.trim().toLowerCase();
    var rows = all.filter(function (r) {
      if (statusFilter === 'aktif' && !r.aktif) return false;
      if (statusFilter === 'nonaktif' && r.aktif) return false;
      return !q || (r.nama + ' ' + r.nip + ' ' + r.jabatan + ' ' + r.kelas).toLowerCase().indexOf(q) > -1;
    });
    if (sort) {
      var k = sort.key, m = sort.dir === 'desc' ? -1 : 1;
      rows = rows.slice().sort(function (a, b) { return m * a[k].localeCompare(b[k], 'id', { sensitivity: 'base', numeric: true }); });
    }
    return rows;
  }

  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }
  function skeleton() {
    var sk = '';
    for (var i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>';
    return sk;
  }

  function render(errMsg) {
    Array.prototype.forEach.call(elTable.querySelectorAll('.th-sort'), function (b) {
      var on = sort && sort.key === b.getAttribute('data-sort');
      b.classList.toggle('is-sorted', !!on);
      b.querySelector('.th-sort__arrow').textContent = on ? (sort.dir === 'asc' ? '▲' : '▼') : '';
      b.parentNode.setAttribute('aria-sort', on ? (sort.dir === 'asc' ? 'ascending' : 'descending') : 'none');
    });

    if (loadState === 'loading') {
      elBody.innerHTML = skeleton(); showState(''); elFooter.hidden = true;
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
      showState('<strong>Belum ada data guru</strong>Klik Tambah Guru untuk mulai mengisi.');
      return;
    }

    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      var what = query.trim() ? 'yang cocok dengan “' + esc(query.trim()) + '”' : 'dengan status ini';
      showState('<strong>Tidak ada hasil</strong>Tidak ada guru ' + what + '.' +
        '<br><button type="button" class="btn btn--outline" data-act="reset">' + icon('refresh', 'ico--sm') + ' Reset pencarian</button>');
      return;
    }
    showState('');

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE;
    var slice = rows.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      var nip = r.nip ? esc(r.nip) : '<span class="is-muted">-</span>';
      var jab = r.jabatan ? esc(r.jabatan) : '<span class="is-muted">-</span>';
      var badge = '<span class="badge ' + (r.aktif ? 'badge--aktif' : 'badge--nonaktif') + '">' + (r.aktif ? 'Aktif' : 'Nonaktif') + '</span>';
      var kelas = r.kelas
        ? (r.aktif ? esc(r.kelas) : '<span class="cell-italic" title="Akun nonaktif, kelas ini perlu wali pengganti">' + esc(r.kelas) + '</span>')
        : '<span class="is-muted">-</span>';
      return '<tr>' +
        '<td>' + esc(r.nama) + '</td>' +
        '<td>' + nip + '</td>' +
        '<td>' + jab + '</td>' +
        '<td>' + badge + '</td>' +
        '<td>' + kelas + '</td>' +
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

  /* ---------- cari, filter, urut, reset, halaman ---------- */
  var timer = null;
  elSearch.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { query = elSearch.value; page = 1; render(); }, 150);
  });
  elStatus.addEventListener('change', function () { statusFilter = elStatus.value; page = 1; render(); });

  function resetView() {
    query = ''; statusFilter = ''; sort = null; page = 1;
    elSearch.value = ''; elStatus.value = '';
    render();
  }
  $('gr-reset').addEventListener('click', resetView);

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
  var dlg = $('dlg-guru'), form = $('form-guru');
  var F = {
    nama: $('gr-nama'), nip: $('gr-nip'), jabatan: $('gr-jabatan'), email: $('gr-email'),
    telepon: $('gr-telepon'), foto: $('gr-foto'), username: $('gr-username'), password: $('gr-password')
  };
  var ERR = {};
  Object.keys(F).forEach(function (k) { ERR[k] = $('gr-' + k + '-err'); });
  var btnSave = $('gr-save'), elFormMsg = $('gr-form-msg'),
      elAkun = $('gr-akun'), elAkunNote = $('gr-akun-note'),
      elFotoPrev = $('gr-foto-preview'), btnFotoClear = $('gr-foto-clear'), btnPw = $('gr-pw-toggle');
  var editing = null, saving = false;
  /* fotoState: 'keep' (tidak diubah), 'new' (dataUrl terpilih), 'clear' (dihapus) */
  var fotoState = 'keep', fotoData = null;

  function fieldError(key, text) {
    ERR[key].textContent = text || ''; ERR[key].hidden = !text;
    F[key].classList.toggle('is-invalid', !!text);
    F[key].setAttribute('aria-invalid', text ? 'true' : 'false');
  }
  function clearErrors() {
    Object.keys(F).forEach(function (k) { fieldError(k, ''); });
    elFormMsg.hidden = true; elFormMsg.textContent = '';
  }
  function setSaving(on) {
    saving = on; btnSave.disabled = on; btnSave.textContent = on ? 'Menyimpan...' : 'Simpan';
  }

  function showFoto(src) {
    elFotoPrev.innerHTML = src ? '<img alt="" src="' + esc(src) + '">' : icon('user-plus');
    btnFotoClear.hidden = !src;
  }

  function openForm(item) {
    editing = item || null;
    clearErrors();
    $('gr-form-title').textContent = editing ? 'Ubah Guru' : 'Tambah Guru';
    $('gr-form-lead').textContent = editing
      ? 'Perbarui data guru, lalu simpan.'
      : 'Isi data guru. Akun login Guru dibuat sekaligus.';

    F.nama.value = editing ? editing.nama : '';
    F.nip.value = editing ? editing.nip : '';
    F.jabatan.value = editing ? editing.jabatan : '';
    F.email.value = editing ? editing.email : '';
    F.telepon.value = editing ? editing.telepon : '';
    F.username.value = ''; F.password.value = '';
    F.password.type = 'password'; btnPw.textContent = 'Tampilkan'; btnPw.setAttribute('aria-pressed', 'false');
    F.foto.value = ''; fotoState = 'keep'; fotoData = null;
    showFoto(editing ? editing.foto : '');

    /* Saat ubah: username dan password disembunyikan (reset lewat menu Akun). */
    elAkun.hidden = !!editing;
    elAkunNote.hidden = !editing;

    setSaving(false);
    dlg.showModal();
    F.nama.focus();
  }

  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('gr-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  btnPw.addEventListener('click', function () {
    var show = F.password.type === 'password';
    F.password.type = show ? 'text' : 'password';
    btnPw.textContent = show ? 'Sembunyikan' : 'Tampilkan';
    btnPw.setAttribute('aria-pressed', String(show));
  });

  /* Foto: periksa tipe, kecilkan di browser (maks 800 px, JPEG) lalu kirim sebagai data URL. */
  F.foto.addEventListener('change', function () {
    fieldError('foto', '');
    var file = F.foto.files && F.foto.files[0];
    if (!file) return;
    if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
      F.foto.value = ''; fieldError('foto', 'Format gambar tidak didukung'); return;
    }
    compress(file).then(function (dataUrl) {
      fotoState = 'new'; fotoData = dataUrl; showFoto(dataUrl);
    }).catch(function () {
      F.foto.value = ''; fieldError('foto', 'Format gambar tidak didukung');
    });
  });
  btnFotoClear.addEventListener('click', function () {
    F.foto.value = ''; fotoState = 'clear'; fotoData = null; fieldError('foto', ''); showFoto('');
  });

  function compress(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () {
        var w = img.naturalWidth, h = img.naturalHeight;
        if (!w || !h) { URL.revokeObjectURL(url); reject(); return; }
        var s = Math.min(1, FOTO_MAX_SIDE / Math.max(w, h));
        var c = document.createElement('canvas');
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
  function validate(v) {
    var ok = true, first = null;
    function bad(key, text) { fieldError(key, text); ok = false; if (!first) first = F[key]; }

    if (!v.nama) bad('nama', 'Nama wajib diisi');
    else if (v.nama.length > 100) bad('nama', 'Nama maksimal 100 karakter');
    if (v.nip.length > 20) bad('nip', 'NIP maksimal 20 karakter');
    if (v.jabatan.length > 50) bad('jabatan', 'Jabatan maksimal 50 karakter');
    if (v.email) {
      if (v.email.length > 100) bad('email', 'Email maksimal 100 karakter');
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.email)) bad('email', 'Format email tidak valid');
    }
    if (v.telepon.length > 20) bad('telepon', 'No. telepon maksimal 20 karakter');
    if (!editing) {
      if (!v.username) bad('username', 'Username wajib diisi');
      else if (v.username.length > 50) bad('username', 'Username maksimal 50 karakter');
      if (v.password.length < 8) bad('password', 'Password minimal 8 karakter');
    }
    if (first) first.focus();
    return ok;
  }

  /* Pesan 409/422 server tampil apa adanya; bila jelas menyangkut satu isian, tandai isian itu. */
  function showServerError(err) {
    var msg = errText(err);
    var map = [['username', /username/i], ['password', /password/i], ['nip', /\bnip\b/i], ['email', /email/i]];
    if (err && (err.status === 409 || err.status === 422)) {
      for (var i = 0; i < map.length; i++) {
        if (map[i][1].test(msg) && !(map[i][0] === 'username' && editing)) {
          fieldError(map[i][0], msg); F[map[i][0]].focus(); return;
        }
      }
    }
    elFormMsg.textContent = msg; elFormMsg.hidden = false;
  }

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    clearErrors();

    var v = {
      nama: F.nama.value.trim(), nip: F.nip.value.trim(), jabatan: F.jabatan.value.trim(),
      email: F.email.value.trim(), telepon: F.telepon.value.trim(),
      username: F.username.value.trim(), password: F.password.value
    };
    if (!validate(v)) return;

    var body = {};
    body[SEND.nama] = v.nama;
    body[SEND.nip] = v.nip || null;
    body[SEND.jabatan] = v.jabatan || null;
    body[SEND.email] = v.email || null;
    body[SEND.telepon] = v.telepon || null;
    if (fotoState === 'new') body[SEND.foto] = fotoData;
    else if (fotoState === 'clear') body[SEND.foto] = null;
    if (!editing) { body[SEND.username] = v.username; body[SEND.password] = v.password; }

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.guru, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Data guru diperbarui' : 'Guru dan akunnya ditambahkan');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      showServerError(err);
    }).then(function () { setSaving(false); });
  });

  /* ---------- hapus ---------- */
  var dDlg = $('dlg-hapus'), dTarget = $('dlg-hapus-target'), dMsg = $('dlg-hapus-msg'),
      dOk = $('dlg-hapus-ok'), dCancel = $('dlg-hapus-cancel');
  var deleting = null, delBusy = false;

  function setDelBusy(on) { delBusy = on; dOk.disabled = on; dOk.textContent = on ? 'Menghapus...' : 'Hapus'; }

  function openDelete(item) {
    deleting = item;
    $('dlg-hapus-title').textContent = 'Hapus Guru';
    dTarget.innerHTML = '<strong>' + esc(item.nama) + '</strong>' + (item.nip ? ' · NIP ' + esc(item.nip) : '');
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
      toast('Guru dihapus');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = errText(err);
      if (err && err.status === 409) {
        /* Kotak informasi (bukan error merah) + saran jalan keluar */
        dMsg.className = 'notice notice--info';
        var hint = /nonaktifkan akun/i.test(msg) ? '' :
          '<span class="notice__hint">Guru masih terkait data atau masih menjadi wali kelas. Lepas penetapan wali atau nonaktifkan akun.</span>';
        dMsg.innerHTML = esc(msg) + hint;
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