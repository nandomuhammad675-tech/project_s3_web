/* SI-KESA PWA Admin | Data Master: Siswa (dokumen 01 bagian 3.4.2)
 * Tabel: NISN, Nama, Status (baru/aktif/lulus/pindah), Kelas Tahun Aktif, Aksi.
 * Status aktif dan lulus berubah otomatis (penempatan dan kenaikan kelas), bukan pilihan manual. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_siswa', 'id'],
    nisn: ['nisn'],
    nama: ['nama_siswa', 'nama'],
    status: ['status', 'status_siswa'],
    kelas: ['kelas_tahun_aktif', 'nama_kelas', 'kelas'],
    kelasAkhir: ['kelas_terakhir'],
    tahunAkhir: ['tahun_ajaran_terakhir', 'tahun_terakhir'],
    wali: ['nama_wali', 'wali'],
    hp: ['no_hp_wali', 'hp_wali']
  };
  var SEND = { nisn: 'nisn', nama: 'nama_siswa', status: 'status', wali: 'nama_wali', hp: 'no_hp_wali' };
  var LABEL = { baru: 'Baru', aktif: 'Aktif', lulus: 'Lulus', pindah: 'Pindah' };
  var PER_PAGE = 10;
  var PENEMPATAN_URL = (CFG.base || '') + '/riwayat-kelas';

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
  function itemUrl(id) { return E.siswa + '/' + encodeURIComponent(id); }
  function str(v) { return String(v == null ? '' : v).trim(); }

  function norm(r) {
    var kelas = pick(r, KEYS.kelas, '');
    if (kelas && typeof kelas === 'object') kelas = kelas.nama_kelas || kelas.nama || '';
    var st = str(pick(r, KEYS.status, 'baru')).toLowerCase();
    return {
      id: pick(r, KEYS.id, null),
      nisn: str(pick(r, KEYS.nisn, '')),
      nama: str(pick(r, KEYS.nama, '')),
      status: LABEL[st] ? st : 'baru',
      kelas: str(kelas),
      kelasAkhir: str(pick(r, KEYS.kelasAkhir, '')),
      tahunAkhir: str(pick(r, KEYS.tahunAkhir, '')),
      wali: str(pick(r, KEYS.wali, '')),
      hp: str(pick(r, KEYS.hp, ''))
    };
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }

  /* ---------- elemen & state ---------- */
  var elBody = $('sw-body'), elState = $('sw-state'), elFooter = $('sw-footer'),
      elCount = $('sw-count'), elPager = $('sw-pager'), elSearch = $('sw-search'),
      elStatus = $('sw-status'), elKelas = $('sw-kelas'), elTable = $('sw-table');

  var all = [], query = '', statusFilter = '', kelasFilter = '', sort = null, page = 1, loadState = 'loading';

  /* ---------- muat data ---------- */
  function fetchAll() {
    return Api.get(E.siswa).then(function (res) {
      var rows = toList(res);
      var last = Number(res && res.meta && res.meta.last_page) || 0;
      if (last <= 1) return rows;
      var jobs = [];
      for (var p = 2; p <= last; p++) jobs.push(Api.get(E.siswa, { page: p }));
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

  /* Pilihan kelas untuk penyaring. Bila gagal dimuat, penyaring kelas tetap "Semua kelas". */
  function loadKelas() {
    Api.get(E.kelas).then(function (res) {
      var seen = {}, names = [];
      toList(res).forEach(function (k) {
        var n = str(pick(k, ['nama_kelas', 'nama'], ''));
        if (n && !seen[n]) { seen[n] = 1; names.push(n); }
      });
      names.sort(function (a, b) { return a.localeCompare(b, 'id', { numeric: true }); });
      names.forEach(function (n) {
        var o = document.createElement('option');
        o.value = n; o.textContent = n; elKelas.appendChild(o);
      });
    }).catch(function () {});
  }

  /* ---------- tampilan daftar ---------- */
  function filtered() {
    var q = query.trim().toLowerCase();
    var rows = all.filter(function (r) {
      if (statusFilter && r.status !== statusFilter) return false;
      if (kelasFilter && r.kelas !== kelasFilter) return false;
      return !q || (r.nisn + ' ' + r.nama).toLowerCase().indexOf(q) > -1;
    });
    if (sort) {
      var k = sort.key, m = sort.dir === 'desc' ? -1 : 1;
      rows = rows.slice().sort(function (a, b) { return m * a[k].localeCompare(b[k], 'id', { sensitivity: 'base', numeric: true }); });
    }
    return rows;
  }

  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }

  function kelasCell(r) {
    if (r.status === 'baru') {
      return '<a class="btn btn--outline btn--sm" href="' + esc(PENEMPATAN_URL + '?siswa=' + encodeURIComponent(r.id)) + '">Tempatkan ke Kelas</a>';
    }
    if (r.status === 'aktif') return r.kelas ? esc(r.kelas) : '<span class="is-muted">-</span>';
    if (r.status === 'lulus') {
      var akhir = r.kelasAkhir || r.kelas;
      if (!akhir) return '<span class="is-muted">-</span>';
      return esc(akhir) + (r.tahunAkhir ? ' · ' + esc(r.tahunAkhir) : '');
    }
    return '<span class="cell-dash">—</span>';
  }

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
      showState('<strong>Belum ada data siswa</strong>Klik Tambah Siswa untuk mulai mengisi.');
      return;
    }

    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      var what = query.trim() ? 'yang cocok dengan “' + esc(query.trim()) + '”' : 'dengan penyaring ini';
      showState('<strong>Tidak ada hasil</strong>Tidak ada siswa ' + what + '.' +
        '<br><button type="button" class="btn btn--outline" data-act="reset">' + icon('refresh', 'ico--sm') + ' Reset pencarian</button>');
      return;
    }
    showState('');

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE;
    var slice = rows.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      return '<tr>' +
        '<td>' + esc(r.nisn) + '</td>' +
        '<td>' + esc(r.nama) + '</td>' +
        '<td><span class="badge badge--' + r.status + '">' + LABEL[r.status] + '</span></td>' +
        '<td>' + kelasCell(r) + '</td>' +
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
  elKelas.addEventListener('change', function () { kelasFilter = elKelas.value; page = 1; render(); });

  function resetView() {
    query = ''; statusFilter = ''; kelasFilter = ''; sort = null; page = 1;
    elSearch.value = ''; elStatus.value = ''; elKelas.value = '';
    render();
  }
  $('sw-reset').addEventListener('click', resetView);

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

  /* Impor dari Excel: dokumen 01 belum mendefinisikan endpoint impor. */
  $('btn-impor').addEventListener('click', function () {
    toast('Impor dari Excel belum tersedia. Tambahkan siswa satu per satu dulu.');
  });

  /* ---------- formulir tambah / ubah ---------- */
  var dlg = $('dlg-siswa'), form = $('form-siswa');
  var F = { nama: $('sw-nama'), nisn: $('sw-nisn'), wali: $('sw-wali'), hp: $('sw-hp') };
  var ERR = {};
  Object.keys(F).forEach(function (k) { ERR[k] = $('sw-' + k + '-err'); });
  var btnSave = $('sw-save'), elFormMsg = $('sw-form-msg'),
      elStatusField = $('sw-status-field'), elStatusIn = $('sw-status-in');
  var editing = null, saving = false;

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

  function openForm(item) {
    editing = item || null;
    clearErrors();
    $('sw-form-title').textContent = editing ? 'Ubah Siswa' : 'Tambah Siswa';
    $('sw-form-lead').textContent = editing
      ? 'Perbarui data siswa, lalu simpan.'
      : 'Siswa baru otomatis berstatus Baru. Tempatkan ke kelas lewat menu Penempatan Siswa.';

    F.nama.value = editing ? editing.nama : '';
    F.nisn.value = editing ? editing.nisn : '';
    F.wali.value = editing ? editing.wali : '';
    F.hp.value = editing ? editing.hp : '';

    /* Status: saat tambah otomatis Baru (tidak tampil). Saat ubah hanya boleh ke Pindah. */
    elStatusField.hidden = !editing;
    if (editing) {
      elStatusIn.innerHTML = '<option value="' + editing.status + '">' + LABEL[editing.status] + '</option>' +
        (editing.status !== 'pindah' ? '<option value="pindah">Pindah</option>' : '');
      elStatusIn.value = editing.status;
      elStatusIn.disabled = editing.status === 'pindah';
    }

    setSaving(false);
    dlg.showModal();
    F.nama.focus();
  }

  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('sw-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  /* Validasi di layar hanya pelengkap; server yang menentukan. */
  function validate(v) {
    var ok = true, first = null;
    function bad(key, text) { fieldError(key, text); ok = false; if (!first) first = F[key]; }
    if (!v.nama) bad('nama', 'Nama wajib diisi');
    else if (v.nama.length > 100) bad('nama', 'Nama maksimal 100 karakter');
    if (!/^\d{10}$/.test(v.nisn)) bad('nisn', 'NISN harus 10 digit angka');
    if (v.wali.length > 100) bad('wali', 'Nama wali maksimal 100 karakter');
    if (v.hp.length > 15) bad('hp', 'No. HP wali maksimal 15 karakter');
    if (first) first.focus();
    return ok;
  }

  /* Pesan 409/422 server tampil apa adanya; bila jelas menyangkut NISN, tandai isiannya. */
  function showServerError(err) {
    var msg = errText(err);
    if (err && (err.status === 409 || err.status === 422) && /nisn/i.test(msg)) {
      fieldError('nisn', msg); F.nisn.focus(); return;
    }
    elFormMsg.textContent = msg; elFormMsg.hidden = false;
  }

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    clearErrors();

    var v = { nama: F.nama.value.trim(), nisn: F.nisn.value.trim(), wali: F.wali.value.trim(), hp: F.hp.value.trim() };
    if (!validate(v)) return;

    var body = {};
    body[SEND.nama] = v.nama;
    body[SEND.nisn] = v.nisn;
    body[SEND.wali] = v.wali || null;
    body[SEND.hp] = v.hp || null;
    /* Hanya "pindah" yang boleh dikirim manual. Aktif/lulus diatur server. */
    if (editing && editing.status !== 'pindah' && elStatusIn.value === 'pindah') body[SEND.status] = 'pindah';

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.siswa, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Data siswa diperbarui' : 'Siswa ditambahkan dengan status Baru');
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
    $('dlg-hapus-title').textContent = 'Hapus Siswa';
    dTarget.innerHTML = '<strong>' + esc(item.nama) + '</strong> · NISN ' + esc(item.nisn);
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
      toast('Siswa dihapus');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = errText(err);
      if (err && err.status === 409) {
        /* Kotak informasi (bukan error merah) + saran jalan keluar */
        dMsg.className = 'notice notice--info';
        var hint = /pindah|lulus/i.test(msg) ? '' :
          '<span class="notice__hint">Siswa sudah memiliki data. Ubah statusnya (Pindah atau Lulus).</span>';
        dMsg.innerHTML = esc(msg) + hint;
        dOk.hidden = true; dCancel.textContent = 'Tutup';
      } else {
        dMsg.className = 'notice notice--warn';
        dMsg.textContent = msg;
      }
      dMsg.hidden = false;
    }).then(function () { setDelBusy(false); });
  });

  loadKelas();
  load();
})();