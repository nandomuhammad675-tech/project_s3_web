/* SI-KESA PWA Admin | Data Master: Kelas (dokumen 01 bagian 3.4.3)
 * Tabel: Tahun Ajaran, Nama Kelas, Tingkat, Wali Kelas, Jumlah Siswa, Aksi. Filter: tahun ajaran.
 * Aturan rombel dijaga server; panel di kanan hanya membantu menjelaskan:
 *  tingkat 2-5 tepat 1 kelas, tingkat 1 dan 6 satu atau dua kelas (maksimal 2). */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_kelas', 'id'],
    idTA: ['id_tahun_ajaran'],
    tahun: ['nama_tahun', 'tahun_ajaran'],
    nama: ['nama_kelas', 'nama'],
    tingkat: ['tingkat'],
    wali: ['nama_wali', 'wali_kelas', 'wali'],
    idWali: ['id_wali_kelas', 'id_wali', 'id_guru_wali'],
    waliAktif: ['wali_aktif'],
    jumlah: ['jumlah_siswa', 'total_siswa']
  };
  var TA_KEYS = {
    id: ['id', 'id_tahun_ajaran'],
    nama: ['nama_tahun', 'nama', 'tahun_ajaran', 'tahun'],
    aktif: ['status', 'status_aktif', 'is_aktif', 'aktif']
  };
  var GURU_KEYS = {
    id: ['id_guru', 'id'],
    nama: ['nama_guru', 'nama'],
    aktif: ['status_akun', 'akun_aktif', 'status_aktif', 'aktif']
  };
  var SEND = { tahun: 'id_tahun_ajaran', tingkat: 'tingkat', nama: 'nama_kelas', wali: 'id_wali_kelas' };
  var PER_PAGE = 10;

  /* Aturan jumlah kelas per tingkat (min/max). Tingkat 1 boleh 0 saat tahun ajaran baru disiapkan. */
  var RULE = {
    1: { min: 0, max: 2, hint: 'maksimal 2 kelas' },
    2: { min: 1, max: 1, hint: 'perlu tepat 1 kelas' },
    3: { min: 1, max: 1, hint: 'perlu tepat 1 kelas' },
    4: { min: 1, max: 1, hint: 'perlu tepat 1 kelas' },
    5: { min: 1, max: 1, hint: 'perlu tepat 1 kelas' },
    6: { min: 1, max: 2, hint: 'perlu 1 sampai 2 kelas' }
  };

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
    return ['1', 'true', 'aktif', 'active', 'ya'].indexOf(String(v == null ? '' : v).toLowerCase()) > -1;
  }
  function str(v) { return String(v == null ? '' : v).trim(); }
  function icon(n, cls) { return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
  function toast(t, type) { if (window.SikesaUI) window.SikesaUI.toast(t, type); }
  function errText(err) { return err && err.message ? err.message : 'Terjadi gangguan, coba lagi'; }
  function itemUrl(id) { return E.kelas + '/' + encodeURIComponent(id); }

  /* ---------- data ---------- */
  var tahunList = [], guruList = [], all = [];

  function normTA(r) {
    var a = pick(r, TA_KEYS.aktif, null);
    return { id: String(pick(r, TA_KEYS.id, '')), nama: str(pick(r, TA_KEYS.nama, '')), aktif: a !== null && flag(a) };
  }
  function normGuru(r) {
    var a = pick(r, GURU_KEYS.aktif, null);
    return {
      id: String(pick(r, GURU_KEYS.id, '')),
      nama: str(pick(r, GURU_KEYS.nama, '')),
      aktif: a === null ? true : flag(a)   /* bila server tidak mengirim status, anggap aktif */
    };
  }
  function tahunName(id) {
    for (var i = 0; i < tahunList.length; i++) if (tahunList[i].id === String(id)) return tahunList[i].nama;
    return '';
  }
  function norm(r) {
    var idTA = String(pick(r, KEYS.idTA, ''));
    var wa = pick(r, KEYS.waliAktif, null);
    var idWali = pick(r, KEYS.idWali, null);
    return {
      id: pick(r, KEYS.id, null),
      idTA: idTA,
      tahun: str(pick(r, KEYS.tahun, '')) || tahunName(idTA),
      nama: str(pick(r, KEYS.nama, '')),
      tingkat: Number(pick(r, KEYS.tingkat, 0)) || 0,
      wali: str(pick(r, KEYS.wali, '')),
      idWali: idWali === null || idWali === '' ? '' : String(idWali),
      waliAktif: wa === null ? true : flag(wa),
      jumlah: Number(pick(r, KEYS.jumlah, 0)) || 0
    };
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }

  function fetchAll(path) {
    return Api.get(path).then(function (res) {
      var rows = toList(res);
      var last = Number(res && res.meta && res.meta.last_page) || 0;
      if (last <= 1) return rows;
      var jobs = [];
      for (var p = 2; p <= last; p++) jobs.push(Api.get(path, { page: p }));
      return Promise.all(jobs).then(function (rs) {
        rs.forEach(function (r) { rows = rows.concat(toList(r)); });
        return rows;
      });
    });
  }

  /* ---------- elemen & state ---------- */
  var elBody = $('kl-body'), elState = $('kl-state'), elFooter = $('kl-footer'),
      elCount = $('kl-count'), elPager = $('kl-pager'), elSearch = $('kl-search'),
      elTahun = $('kl-tahun'), elTable = $('kl-table'),
      elRombel = $('kl-rombel'), elRombelYear = $('kl-rombel-year'),
      elRombelBadge = $('kl-rombel-badge'), elRombelList = $('kl-rombel-list');

  var query = '', tahunFilter = '', sort = null, page = 1, loadState = 'loading';

  function defaultTahun() {
    for (var i = 0; i < tahunList.length; i++) if (tahunList[i].aktif) return tahunList[i].id;
    return tahunList.length ? tahunList[0].id : '';
  }

  function fillTahunSelect(sel, withPlaceholder) {
    var h = withPlaceholder ? '<option value="">Pilih tahun ajaran</option>' : '';
    tahunList.forEach(function (t) {
      h += '<option value="' + esc(t.id) + '">' + esc(t.nama) + (withPlaceholder && t.aktif ? ' (aktif)' : '') + '</option>';
    });
    sel.innerHTML = h;
  }

  /* ---------- muat data ---------- */
  function load(silent) {
    if (!silent) { loadState = 'loading'; render(); }
    /* Tahun ajaran dan kelas wajib; guru hanya untuk pilihan wali (gagal tidak memblokir). */
    var jobs = [
      fetchAll(E.tahunAjaran),
      fetchAll(E.kelas),
      fetchAll(E.guru).catch(function () { return null; })
    ];
    return Promise.all(jobs).then(function (r) {
      tahunList = r[0].map(normTA);
      all = r[1].map(norm);
      if (r[2]) guruList = r[2].map(normGuru);
      var keep = tahunFilter && tahunList.some(function (t) { return t.id === tahunFilter; });
      if (!keep) tahunFilter = defaultTahun();
      fillTahunSelect(elTahun, false);
      elTahun.value = tahunFilter;
      loadState = 'ok';
      render();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      loadState = 'error';
      render(errText(err));
    });
  }

  /* ---------- tampilan daftar ---------- */
  function cmp(a, b, key) {
    var x = a[key], y = b[key];
    if (typeof x === 'number' && typeof y === 'number') return x - y;
    return String(x).localeCompare(String(y), 'id', { sensitivity: 'base', numeric: true });
  }
  function filtered() {
    var q = query.trim().toLowerCase();
    var rows = all.filter(function (r) {
      if (tahunFilter && r.idTA !== tahunFilter) return false;
      return !q || (r.nama + ' ' + r.wali + ' Tingkat ' + r.tingkat).toLowerCase().indexOf(q) > -1;
    });
    var m = sort && sort.dir === 'desc' ? -1 : 1;
    rows = rows.slice().sort(function (a, b) {
      if (sort) return m * cmp(a, b, sort.key);
      return (a.tingkat - b.tingkat) || cmp(a, b, 'nama');   /* urutan bawaan: tingkat, lalu nama */
    });
    return rows;
  }

  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }

  function waliCell(r) {
    if (!r.wali) return '<span class="is-muted" title="Wali wajib ditetapkan sebelum kenaikan kelas">Belum ada wali</span>';
    if (!r.waliAktif) return '<span class="cell-italic" title="Akun wali nonaktif. Tetapkan wali pengganti sebelum kenaikan kelas.">' + esc(r.wali) + '</span>';
    return esc(r.wali);
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
      for (i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="6"><span class="skeleton skeleton--row"></span></td></tr>';
      elBody.innerHTML = sk; showState(''); elFooter.hidden = true; elRombel.hidden = true;
      return;
    }

    if (loadState === 'error') {
      elBody.innerHTML = ''; elFooter.hidden = true; elRombel.hidden = true;
      showState('<strong>Data gagal dimuat</strong>' + esc(errMsg || 'Terjadi gangguan, coba lagi') +
        '<br><button type="button" class="btn btn--outline" data-act="retry">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>');
      return;
    }

    renderRombel();

    var inYear = all.filter(function (r) { return !tahunFilter || r.idTA === tahunFilter; });
    if (!inYear.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Belum ada kelas</strong>Klik Tambah Kelas untuk mulai mengisi tahun ajaran ini.');
      return;
    }

    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Tidak ada hasil</strong>Tidak ada kelas yang cocok dengan “' + esc(query.trim()) + '”.' +
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
        '<td>' + esc(r.tahun) + '</td>' +
        '<td>' + esc(r.nama) + '</td>' +
        '<td>Tingkat ' + esc(r.tingkat) + '</td>' +
        '<td>' + waliCell(r) + '</td>' +
        '<td>' + esc(r.jumlah) + '</td>' +
        '<td><div class="row-actions">' +
          '<button type="button" class="icon-act" data-act="edit" data-id="' + esc(r.id) + '" aria-label="Ubah kelas ' + esc(r.nama) + '" title="Ubah">' + icon('pencil', 'ico--sm') + '</button>' +
          '<button type="button" class="icon-act icon-act--danger" data-act="del" data-id="' + esc(r.id) + '" aria-label="Hapus kelas ' + esc(r.nama) + '" title="Hapus">' + icon('trash', 'ico--sm') + '</button>' +
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

  /* ---------- panel kelengkapan rombel (tahun ajaran terpilih, tidak terpengaruh pencarian) ---------- */
  function renderRombel() {
    if (!tahunFilter) { elRombel.hidden = true; return; }
    var counts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0 };
    all.forEach(function (r) { if (r.idTA === tahunFilter && counts[r.tingkat] !== undefined) counts[r.tingkat]++; });

    var okCount = 0, h = '';
    for (var t = 1; t <= 6; t++) {
      var n = counts[t], ru = RULE[t];
      var ok = n >= ru.min && n <= ru.max;
      if (ok) okCount++;
      h += '<li class="rombel__item">' +
        '<span class="rombel__ic ' + (ok ? 'rombel__ic--ok' : 'rombel__ic--warn') + '">' + icon(ok ? 'check' : 'alert') + '</span>' +
        '<div><strong>Tingkat ' + t + '</strong>' +
        '<small' + (ok ? '' : ' class="is-warn"') + '>' + n + ' kelas' + (ok ? '' : ' · ' + ru.hint) + '</small></div></li>';
    }
    elRombelList.innerHTML = h;
    elRombelYear.textContent = 'Tahun Ajaran ' + tahunName(tahunFilter);
    elRombelBadge.textContent = okCount + ' dari 6 sesuai';
    elRombelBadge.className = 'badge ' + (okCount === 6 ? 'badge--sudah' : 'badge--belum');
    elRombel.hidden = false;
  }

  /* ---------- cari, filter, urut, reset, halaman ---------- */
  var timer = null;
  elSearch.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { query = elSearch.value; page = 1; render(); }, 150);
  });
  elTahun.addEventListener('change', function () { tahunFilter = elTahun.value; page = 1; render(); });

  function resetView() {
    query = ''; sort = null; page = 1; tahunFilter = defaultTahun();
    elSearch.value = ''; elTahun.value = tahunFilter;
    render();
  }
  $('kl-reset').addEventListener('click', resetView);

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
  var dlg = $('dlg-kelas'), form = $('form-kelas');
  var F = { tahun: $('kl-f-tahun'), tingkat: $('kl-f-tingkat'), nama: $('kl-f-nama'), wali: $('kl-f-wali') };
  var ERR = {};
  Object.keys(F).forEach(function (k) { ERR[k] = $('kl-f-' + k + '-err'); });
  var btnSave = $('kl-save'), elFormMsg = $('kl-form-msg'), elTahunHint = $('kl-f-tahun-hint');
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

  /* Pilihan wali: guru aktif. Guru yang sudah jadi wali kelas lain di tahun ajaran yang sama dinonaktifkan. */
  function fillWali(selected) {
    var taId = F.tahun.value;
    var used = {};
    all.forEach(function (r) {
      if (r.idWali && r.idTA === taId && !(editing && String(r.id) === String(editing.id))) used[r.idWali] = r.nama;
    });
    var h = '<option value="">Belum ditetapkan</option>';
    guruList.forEach(function (g) {
      if (!g.aktif && g.id !== selected) return;
      var u = used[g.id];
      h += '<option value="' + esc(g.id) + '"' + (u ? ' disabled' : '') + '>' + esc(g.nama) +
        (u ? ' (wali kelas ' + esc(u) + ')' : '') + (!g.aktif ? ' (akun nonaktif)' : '') + '</option>';
    });
    F.wali.innerHTML = h;
    F.wali.value = selected || '';
  }

  function openForm(item) {
    editing = item || null;
    clearErrors();
    $('kl-form-title').textContent = editing ? 'Ubah Kelas' : 'Tambah Kelas';
    $('kl-form-lead').textContent = editing ? 'Perbarui data kelas, lalu simpan.' : 'Isi data kelas, lalu simpan.';

    fillTahunSelect(F.tahun, true);
    F.tahun.value = editing ? editing.idTA : tahunFilter;
    F.tahun.disabled = !!editing;           /* kelas tidak dipindah antar tahun ajaran */
    elTahunHint.hidden = !editing;
    F.tingkat.value = editing ? String(editing.tingkat) : '';
    F.nama.value = editing ? editing.nama : '';
    fillWali(editing ? editing.idWali : '');

    setSaving(false);
    dlg.showModal();
    (editing ? F.nama : F.tingkat).focus();
  }

  F.tahun.addEventListener('change', function () { fillWali(F.wali.value); });
  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('kl-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  /* Validasi di layar hanya pelengkap; server yang menentukan. */
  function validate(v) {
    var ok = true, first = null;
    function bad(key, text) { fieldError(key, text); ok = false; if (!first) first = F[key]; }
    if (!v.tahun) bad('tahun', 'Tahun ajaran wajib dipilih');
    if (!/^[1-6]$/.test(v.tingkat)) bad('tingkat', 'Tingkat harus 1 sampai 6');
    if (!v.nama) bad('nama', 'Nama kelas wajib diisi');
    else if (v.nama.length > 10) bad('nama', 'Nama kelas maksimal 10 karakter');
    if (first) first.focus();
    return ok;
  }

  /* Pesan 409/422 server tampil apa adanya; tandai isian yang paling mungkin terkait. */
  function showServerError(err) {
    var msg = errText(err);
    if (err && (err.status === 409 || err.status === 422)) {
      var key = /wali|guru/i.test(msg) ? 'wali' : /nama kelas/i.test(msg) ? 'nama' : /tingkat/i.test(msg) ? 'tingkat' : null;
      if (key && !F[key].disabled) { fieldError(key, msg); F[key].focus(); return; }
    }
    elFormMsg.textContent = msg; elFormMsg.hidden = false;
  }

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    clearErrors();

    var v = { tahun: F.tahun.value, tingkat: F.tingkat.value, nama: F.nama.value.trim(), wali: F.wali.value };
    if (!validate(v)) return;

    var body = {};
    if (!editing) body[SEND.tahun] = isNaN(Number(v.tahun)) ? v.tahun : Number(v.tahun);
    body[SEND.tingkat] = Number(v.tingkat);
    body[SEND.nama] = v.nama;
    body[SEND.wali] = v.wali ? (isNaN(Number(v.wali)) ? v.wali : Number(v.wali)) : null;

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.kelas, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Data kelas diperbarui' : 'Kelas ditambahkan');
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
    $('dlg-hapus-title').textContent = 'Hapus Kelas';
    dTarget.innerHTML = '<strong>Kelas ' + esc(item.nama) + '</strong> · ' + esc(item.tahun);
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
      toast('Kelas dihapus');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = errText(err);
      if (err && err.status === 409) {
        /* Kotak informasi (bukan error merah) + saran jalan keluar */
        dMsg.className = 'notice notice--info';
        var hint = /hari libur/i.test(msg) ? '' :
          '<span class="notice__hint">Kelas masih dipakai. Jika kelas masih menjadi target hari libur, ubah atau hapus target hari libur lebih dulu.</span>';
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