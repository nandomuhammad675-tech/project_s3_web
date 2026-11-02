/* SI-KESA PWA Admin | Penempatan Siswa / Riwayat Kelas (dokumen 01 bagian 3.5)
 * Tabel: Tahun, Kelas, Nomor Absen, NISN, Nama, Status Siswa, Aksi. Filter: tahun, kelas.
 * Tempatkan: siswa baru/aktif yang belum punya kelas, hanya ke tahun aktif.
 * Ubah: hanya nomor absen. Hapus: hanya untuk koreksi salah tempat (409 ditampilkan alasannya). */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_riwayat_kelas', 'id_riwayat', 'id'],
    idSiswa: ['id_siswa'],
    idKelas: ['id_kelas'],
    idTA: ['id_tahun_ajaran'],
    tahun: ['nama_tahun', 'tahun_ajaran'],
    kelas: ['nama_kelas', 'kelas'],
    absen: ['nomor_absen', 'no_absen'],
    nisn: ['nisn'],
    nama: ['nama_siswa', 'nama'],
    status: ['status_siswa', 'status']
  };
  var SISWA_KEYS = {
    id: ['id_siswa', 'id'],
    nisn: ['nisn'],
    nama: ['nama_siswa', 'nama'],
    status: ['status', 'status_siswa'],
    kelas: ['kelas_tahun_aktif', 'nama_kelas', 'kelas']
  };
  var TA_KEYS = {
    id: ['id', 'id_tahun_ajaran'],
    nama: ['nama_tahun', 'nama', 'tahun_ajaran', 'tahun'],
    aktif: ['status', 'status_aktif', 'is_aktif', 'aktif']
  };
  var KELAS_KEYS = {
    id: ['id_kelas', 'id'],
    idTA: ['id_tahun_ajaran'],
    tahun: ['nama_tahun', 'tahun_ajaran'],
    nama: ['nama_kelas', 'nama'],
    tingkat: ['tingkat']
  };
  var SEND = { siswa: 'id_siswa', tahun: 'id_tahun_ajaran', kelas: 'id_kelas', absen: 'nomor_absen' };
  var LABEL = { baru: 'Baru', aktif: 'Aktif', lulus: 'Lulus', pindah: 'Pindah' };
  var PER_PAGE = 10;

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
  function num(v) { return /^\d+$/.test(String(v)) ? Number(v) : v; }
  function icon(n, cls) { return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
  function toast(t, type) { if (window.SikesaUI) window.SikesaUI.toast(t, type); }
  function errText(err) { return err && err.message ? err.message : 'Terjadi gangguan, coba lagi'; }
  function itemUrl(id) { return E.riwayatKelas + '/' + encodeURIComponent(id); }
  function cmpText(a, b) { return String(a).localeCompare(String(b), 'id', { sensitivity: 'base', numeric: true }); }

  /* ---------- data ---------- */
  var tahunList = [], kelasList = [], all = [];

  function normTA(r) {
    var a = pick(r, TA_KEYS.aktif, null);
    return { id: String(pick(r, TA_KEYS.id, '')), nama: str(pick(r, TA_KEYS.nama, '')), aktif: a !== null && flag(a) };
  }
  function tahunName(id) {
    for (var i = 0; i < tahunList.length; i++) if (tahunList[i].id === String(id)) return tahunList[i].nama;
    return '';
  }
  function normKelas(r) {
    var idTA = String(pick(r, KELAS_KEYS.idTA, ''));
    return {
      id: String(pick(r, KELAS_KEYS.id, '')),
      idTA: idTA,
      tahun: str(pick(r, KELAS_KEYS.tahun, '')) || tahunName(idTA),
      nama: str(pick(r, KELAS_KEYS.nama, '')),
      tingkat: Number(pick(r, KELAS_KEYS.tingkat, 0)) || 0
    };
  }
  function kelasName(id) {
    for (var i = 0; i < kelasList.length; i++) if (kelasList[i].id === String(id)) return kelasList[i].nama;
    return '';
  }
  function norm(r) {
    var idTA = String(pick(r, KEYS.idTA, ''));
    var idKelas = String(pick(r, KEYS.idKelas, ''));
    var ab = pick(r, KEYS.absen, null);
    var st = str(pick(r, KEYS.status, 'aktif')).toLowerCase();
    return {
      id: pick(r, KEYS.id, null),
      idSiswa: pick(r, KEYS.idSiswa, null),
      idKelas: idKelas,
      idTA: idTA,
      tahun: str(pick(r, KEYS.tahun, '')) || tahunName(idTA),
      kelas: str(pick(r, KEYS.kelas, '')) || kelasName(idKelas),
      absen: ab === null || ab === '' ? null : Number(ab),
      nisn: str(pick(r, KEYS.nisn, '')),
      nama: str(pick(r, KEYS.nama, '')),
      status: LABEL[st] ? st : 'aktif'
    };
  }
  function normSiswa(r) {
    var kelas = pick(r, SISWA_KEYS.kelas, '');
    if (kelas && typeof kelas === 'object') kelas = kelas.nama_kelas || kelas.nama || '';
    var st = str(pick(r, SISWA_KEYS.status, 'baru')).toLowerCase();
    return {
      id: String(pick(r, SISWA_KEYS.id, '')),
      nisn: str(pick(r, SISWA_KEYS.nisn, '')),
      nama: str(pick(r, SISWA_KEYS.nama, '')),
      status: LABEL[st] ? st : 'baru',
      kelas: str(kelas)
    };
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }
  function activeTahun() {
    for (var i = 0; i < tahunList.length; i++) if (tahunList[i].aktif) return tahunList[i];
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
  var elBody = $('rk-body'), elState = $('rk-state'), elFooter = $('rk-footer'),
      elCount = $('rk-count'), elPager = $('rk-pager'), elSearch = $('rk-search'),
      elTahun = $('rk-tahun'), elKelas = $('rk-kelas');

  var query = '', tahunFilter = '', kelasFilter = '', page = 1, loadState = 'loading', firstLoad = true;
  var presetSiswa = '';
  try { presetSiswa = new URLSearchParams(window.location.search).get('siswa') || ''; } catch (e) { presetSiswa = ''; }

  function fillTahunFilter() {
    var h = '<option value="">Semua tahun</option>';
    tahunList.forEach(function (t) { h += '<option value="' + esc(t.id) + '">' + esc(t.nama) + '</option>'; });
    elTahun.innerHTML = h;
    elTahun.value = tahunFilter;
  }
  function fillKelasFilter() {
    var rows = kelasList.filter(function (k) { return !tahunFilter || k.idTA === tahunFilter; });
    rows.sort(function (a, b) { return cmpText(b.tahun, a.tahun) || (a.tingkat - b.tingkat) || cmpText(a.nama, b.nama); });
    var h = '<option value="">Semua kelas</option>';
    rows.forEach(function (k) {
      h += '<option value="' + esc(k.id) + '">' + esc(k.nama) + (tahunFilter ? '' : ' (' + esc(k.tahun) + ')') + '</option>';
    });
    elKelas.innerHTML = h;
    if (!rows.some(function (k) { return k.id === kelasFilter; })) kelasFilter = '';
    elKelas.value = kelasFilter;
  }

  /* ---------- muat data ---------- */
  function load(silent) {
    if (!silent) { loadState = 'loading'; render(); }
    return Promise.all([fetchAll(E.tahunAjaran), fetchAll(E.kelas), fetchAll(E.riwayatKelas)]).then(function (r) {
      tahunList = r[0].map(normTA);
      kelasList = r[1].map(normKelas);
      all = r[2].map(norm);
      if (firstLoad) { var a = activeTahun(); tahunFilter = a ? a.id : ''; }
      else if (tahunFilter && !tahunList.some(function (t) { return t.id === tahunFilter; })) tahunFilter = '';
      fillTahunFilter(); fillKelasFilter();
      loadState = 'ok';
      render();
      if (firstLoad && presetSiswa) {
        /* Datang dari tombol "Tempatkan ke Kelas" di menu Siswa. */
        var id = presetSiswa; presetSiswa = '';
        try { window.history.replaceState(null, '', window.location.pathname); } catch (e) { /* abaikan */ }
        openForm(null, id);
      }
      firstLoad = false;
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
      if (tahunFilter && r.idTA !== tahunFilter) return false;
      if (kelasFilter && r.idKelas !== kelasFilter) return false;
      return !q || (r.nisn + ' ' + r.nama).toLowerCase().indexOf(q) > -1;
    });
    rows.sort(function (a, b) {
      var an = a.absen === null ? Infinity : a.absen, bn = b.absen === null ? Infinity : b.absen;
      return cmpText(b.tahun, a.tahun) || cmpText(a.kelas, b.kelas) || (an === bn ? 0 : an < bn ? -1 : 1) || cmpText(a.nama, b.nama);
    });
    return rows;
  }

  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }

  function render(errMsg) {
    var i;
    if (loadState === 'loading') {
      var sk = '';
      for (i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="7"><span class="skeleton skeleton--row"></span></td></tr>';
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
      showState('<strong>Belum ada penempatan siswa</strong>Klik Tempatkan Siswa untuk menetapkan siswa ke kelas.');
      return;
    }

    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Tidak ada hasil</strong>Tidak ada penempatan yang cocok dengan pencarian atau filter ini.' +
        '<br><button type="button" class="btn btn--outline" data-act="reset">' + icon('refresh', 'ico--sm') + ' Reset pencarian</button>');
      return;
    }
    showState('');

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE;
    var slice = rows.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      var absen = r.absen === null ? '<span class="is-muted">-</span>' : '<span class="cell-accent">' + (r.absen < 10 ? '0' : '') + r.absen + '</span>';
      return '<tr>' +
        '<td>' + esc(r.tahun) + '</td>' +
        '<td><span class="cell-accent">' + esc(r.kelas) + '</span></td>' +
        '<td>' + absen + '</td>' +
        '<td>' + esc(r.nisn) + '</td>' +
        '<td>' + esc(r.nama) + '</td>' +
        '<td><span class="badge badge--' + r.status + '">' + LABEL[r.status] + '</span></td>' +
        '<td><div class="row-actions">' +
          '<button type="button" class="icon-act" data-act="edit" data-id="' + esc(r.id) + '" aria-label="Ubah nomor absen ' + esc(r.nama) + '" title="Ubah nomor absen">' + icon('pencil', 'ico--sm') + '</button>' +
          '<button type="button" class="icon-act icon-act--danger" data-act="del" data-id="' + esc(r.id) + '" aria-label="Hapus penempatan ' + esc(r.nama) + '" title="Hapus">' + icon('trash', 'ico--sm') + '</button>' +
        '</div></td></tr>';
    }).join('');

    elFooter.hidden = pages <= 1;
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

  /* ---------- cari, filter, halaman ---------- */
  var timer = null;
  elSearch.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { query = elSearch.value; page = 1; render(); }, 150);
  });
  elTahun.addEventListener('change', function () {
    tahunFilter = elTahun.value; kelasFilter = ''; page = 1; fillKelasFilter(); render();
  });
  elKelas.addEventListener('change', function () { kelasFilter = elKelas.value; page = 1; render(); });

  function resetView() {
    query = ''; kelasFilter = ''; page = 1; elSearch.value = '';
    var a = activeTahun(); tahunFilter = a ? a.id : '';
    fillTahunFilter(); fillKelasFilter(); render();
  }

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

  /* ---------- formulir tempatkan / ubah nomor absen ---------- */
  var dlg = $('dlg-rk'), form = $('form-rk');
  var F = { cari: $('rk-f-cari'), siswa: $('rk-f-siswa'), tahun: $('rk-f-tahun'), kelas: $('rk-f-kelas'), absen: $('rk-f-absen') };
  var errBox = { siswa: $('rk-f-siswa-err'), tahun: $('rk-f-tahun-err'), kelas: $('rk-f-kelas-err'), absen: $('rk-f-absen-err') };
  var btnSave = $('rk-save'), elFormMsg = $('rk-form-msg');
  var editing = null, saving = false;
  var siswaPool = [], siswaState = 'idle', wantSiswa = '';

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
  function setOnly(sel, val, label) {
    sel.innerHTML = '<option value="' + esc(val) + '">' + esc(label) + '</option>';
    sel.value = String(val);
  }
  function lockSelects(on) { F.siswa.disabled = on; F.tahun.disabled = on; F.kelas.disabled = on; }

  /* Tahun ajaran: hanya tahun aktif (penempatan ke tahun lain lewat kenaikan kelas). */
  function fillTahunForm() {
    var aktif = tahunList.filter(function (t) { return t.aktif; });
    if (!aktif.length) { F.tahun.innerHTML = '<option value="">Belum ada tahun ajaran aktif</option>'; return; }
    F.tahun.innerHTML = aktif.map(function (t) { return '<option value="' + esc(t.id) + '">' + esc(t.nama) + '</option>'; }).join('');
    F.tahun.value = aktif[0].id;
  }
  /* Kelas: hanya kelas milik tahun yang dipilih. */
  function renderKelasForm() {
    var rows = kelasList.filter(function (k) { return k.idTA === F.tahun.value; });
    rows.sort(function (a, b) { return (a.tingkat - b.tingkat) || cmpText(a.nama, b.nama); });
    var h = rows.length ? '<option value="">Pilih kelas</option>' : '<option value="">Belum ada kelas di tahun ini</option>';
    rows.forEach(function (k) { h += '<option value="' + esc(k.id) + '">' + esc(k.nama) + '</option>'; });
    F.kelas.innerHTML = h;
  }

  /* Siswa: status baru atau aktif, belum punya kelas tahun aktif. Lulus dan pindah tidak ditampilkan. */
  function loadSiswa() {
    siswaState = 'loading'; renderSiswa();
    return fetchAll(E.siswa).then(function (rows) {
      var a = activeTahun(), placed = {};
      all.forEach(function (r) { if (a && r.idTA === a.id) placed[String(r.idSiswa)] = true; });
      siswaPool = rows.map(normSiswa).filter(function (s) {
        return (s.status === 'baru' || s.status === 'aktif') && !s.kelas && !placed[s.id];
      });
      siswaPool.sort(function (x, y) { return cmpText(x.nama, y.nama); });
      siswaState = 'ok'; renderSiswa();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      siswaState = 'error'; renderSiswa();
      elFormMsg.textContent = errText(err) + '. Tutup formulir lalu coba lagi.';
      elFormMsg.hidden = false;
    });
  }

  function renderSiswa() {
    if (editing) return;
    var cur = F.siswa.value;
    if (siswaState === 'loading' || siswaState === 'idle') { F.siswa.innerHTML = '<option value="">Memuat siswa...</option>'; return; }
    if (siswaState === 'error') { F.siswa.innerHTML = '<option value="">Daftar siswa gagal dimuat</option>'; return; }
    var q = F.cari.value.trim().toLowerCase();
    var rows = siswaPool.filter(function (s) { return !q || (s.nisn + ' ' + s.nama).toLowerCase().indexOf(q) > -1; });
    var h;
    if (!siswaPool.length) h = '<option value="">Tidak ada siswa yang menunggu penempatan</option>';
    else if (!rows.length) h = '<option value="">Tidak ada siswa yang cocok</option>';
    else {
      h = '<option value="">Pilih siswa</option>' + rows.map(function (s) {
        return '<option value="' + esc(s.id) + '">' + esc(s.nisn) + ' · ' + esc(s.nama) + ' (' + LABEL[s.status] + ')</option>';
      }).join('');
    }
    F.siswa.innerHTML = h;
    var want = wantSiswa || cur;
    for (var i = 0; i < F.siswa.options.length; i++) {
      if (want && F.siswa.options[i].value === String(want)) { F.siswa.selectedIndex = i; wantSiswa = ''; break; }
    }
  }

  function openForm(item, preset) {
    editing = item || null;
    clearErrors();
    var isEdit = !!editing;
    $('rk-form-title').textContent = isEdit ? 'Ubah Nomor Absen' : 'Tempatkan Siswa';
    $('rk-form-lead').textContent = isEdit
      ? 'Hanya nomor absen yang dapat diubah. Untuk memindahkan kelas, hubungi pengembang.'
      : 'Pilih siswa yang belum punya kelas, lalu tentukan kelas dan nomor absen.';
    $('rk-cari-wrap').hidden = isEdit;
    $('rk-f-siswa-hint').hidden = isEdit;
    F.cari.value = '';

    if (isEdit) {
      setOnly(F.siswa, editing.idSiswa, editing.nisn + ' · ' + editing.nama);
      setOnly(F.tahun, editing.idTA, editing.tahun);
      setOnly(F.kelas, editing.idKelas, editing.kelas);
      lockSelects(true);
      F.absen.value = editing.absen === null ? '' : String(editing.absen);
    } else {
      lockSelects(false);
      fillTahunForm();
      renderKelasForm();
      F.absen.value = '';
      wantSiswa = preset ? String(preset) : '';
      loadSiswa();
    }
    setSaving(false);
    dlg.showModal();
    (isEdit ? F.absen : F.cari).focus();
  }

  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('rk-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });
  F.cari.addEventListener('input', renderSiswa);
  F.tahun.addEventListener('change', renderKelasForm);

  /* Validasi di layar hanya pelengkap; server yang menentukan. */
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    clearErrors();

    var absenRaw = F.absen.value.trim(), ok = true, first = null;
    function bad(key, text) { fieldError(key, text); ok = false; if (!first) first = F[key]; }

    if (!editing) {
      if (!F.siswa.value) bad('siswa', 'Pilih siswa');
      if (!F.tahun.value) bad('tahun', 'Belum ada tahun ajaran aktif');
      if (!F.kelas.value) bad('kelas', 'Pilih kelas');
    }
    if (absenRaw !== '' && !/^[1-9]\d*$/.test(absenRaw)) bad('absen', 'Nomor absen harus angka positif');
    if (!ok) { first.focus(); return; }

    var body = {};
    body[SEND.absen] = absenRaw === '' ? null : Number(absenRaw);
    if (!editing) {
      body[SEND.siswa] = num(F.siswa.value);
      body[SEND.tahun] = num(F.tahun.value);
      body[SEND.kelas] = num(F.kelas.value);
    }

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.riwayatKelas, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Nomor absen diperbarui' : 'Siswa berhasil ditempatkan dan kini berstatus aktif');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      elFormMsg.textContent = errText(err);   /* 409 / 422: pesan server apa adanya */
      elFormMsg.hidden = false;
    }).then(function () { setSaving(false); });
  });

  /* ---------- hapus (hanya untuk koreksi salah tempat) ---------- */
  var dDlg = $('dlg-hapus'), dTarget = $('dlg-hapus-target'), dMsg = $('dlg-hapus-msg'),
      dOk = $('dlg-hapus-ok'), dCancel = $('dlg-hapus-cancel');
  var deleting = null, delBusy = false;

  function setDelBusy(on) { delBusy = on; dOk.disabled = on; dOk.textContent = on ? 'Menghapus...' : 'Hapus'; }

  function openDelete(item) {
    deleting = item;
    $('dlg-hapus-title').textContent = 'Hapus Penempatan';
    dTarget.innerHTML = '<strong>' + esc(item.nama) + '</strong> · ' + esc(item.kelas) + ' (' + esc(item.tahun) + ')' +
      '<span class="dialog__sub">Hapus penempatan hanya untuk memperbaiki kesalahan.</span>';
    dMsg.hidden = true; dMsg.textContent = '';
    dOk.hidden = false; dCancel.textContent = 'Batal';
    setDelBusy(false);
    dDlg.showModal();
    dCancel.focus();
  }

  dCancel.addEventListener('click', function () { if (!delBusy) dDlg.close(); });
  dDlg.addEventListener('cancel', function (ev) { if (delBusy) ev.preventDefault(); });

  function backToBaru(res, item) {
    var st = res && (res.status_siswa || (res.data && (res.data.status_siswa || res.data.status)));
    if (st) return String(st).toLowerCase() === 'baru';
    return !all.some(function (r) { return String(r.idSiswa) === String(item.idSiswa) && String(r.id) !== String(item.id); });
  }

  dOk.addEventListener('click', function () {
    if (delBusy || !deleting) return;
    setDelBusy(true);
    var item = deleting;
    Api.del(itemUrl(item.id)).then(function (res) {
      dDlg.close();
      toast(backToBaru(res, item) ? 'Penempatan dihapus. Status siswa kembali Baru.' : 'Penempatan dihapus');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = errText(err);
      if (err && err.status === 409) {
        /* Alasan dari server (absensi, nilai, kedisiplinan, piket, log kenaikan kelas) di kotak informasi. */
        dMsg.className = 'notice notice--info';
        dMsg.innerHTML = esc(msg) + '<span class="notice__hint">Penempatan yang sudah punya riwayat tidak dapat dihapus.</span>';
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