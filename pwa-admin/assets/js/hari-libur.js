/* SI-KESA PWA Admin | Data Master: Hari Libur (dokumen 01 bagian 3.4.6)
 * Isian: Tanggal (wajib), Keterangan (opsional, maks 100), Berlaku untuk: Semua kelas / Kelas tertentu.
 * "Semua kelas" = tidak ada baris target di database -> dikirim sebagai daftar kelas kosong.
 * Kelas yang bisa dipilih hanya kelas tahun ajaran aktif. Hari Minggu otomatis libur (tidak perlu diinput). */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_hari_libur', 'id'],
    tanggal: ['tanggal'],
    ket: ['keterangan'],
    target: ['target_kelas', 'kelas', 'id_kelas']   /* daftar id kelas, atau objek {id_kelas, nama_kelas}; kosong = semua kelas */
  };
  var TA_KEYS = { id: ['id', 'id_tahun_ajaran'], aktif: ['status', 'status_aktif', 'is_aktif', 'aktif'] };
  var KELAS_KEYS = { id: ['id_kelas', 'id'], idTA: ['id_tahun_ajaran'], nama: ['nama_kelas', 'nama'], tingkat: ['tingkat'] };
  var SEND = { tanggal: 'tanggal', ket: 'keterangan', kelas: 'id_kelas' };
  var PER_PAGE = 10;
  var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

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
  function itemUrl(id) { return E.hariLibur + '/' + encodeURIComponent(id); }

  /* Tanggal: tampil "5 Oktober 2026", kirim "YYYY-MM-DD". Dipecah manual agar tidak bergeser zona waktu. */
  function ymd(v) { var m = String(v || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? m[1] + '-' + m[2] + '-' + m[3] : ''; }
  function idDate(v) {
    var s = ymd(v);
    if (!s) return '-';
    var p = s.split('-');
    return Number(p[2]) + ' ' + BULAN[Number(p[1]) - 1] + ' ' + p[0];
  }
  function isSunday(s) {
    var p = ymd(s).split('-');
    return p.length === 3 && new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2])).getDay() === 0;
  }

  /* ---------- data ---------- */
  var all = [], kelasAktif = [];

  function kelasNama(id) {
    for (var i = 0; i < kelasAktif.length; i++) if (kelasAktif[i].id === String(id)) return kelasAktif[i].nama;
    return null;
  }
  function norm(r) {
    var t = pick(r, KEYS.target, []);
    if (!Array.isArray(t)) t = [];
    var ids = [], names = [];
    t.forEach(function (x) {
      var id, nm;
      if (x && typeof x === 'object') { id = pick(x, KELAS_KEYS.id, ''); nm = str(pick(x, KELAS_KEYS.nama, '')); }
      else { id = x; nm = ''; }
      id = String(id);
      ids.push(id);
      names.push(nm || kelasNama(id) || ('#' + id));
    });
    return {
      id: pick(r, KEYS.id, null),
      tanggal: ymd(pick(r, KEYS.tanggal, '')),
      ket: str(pick(r, KEYS.ket, '')),
      ids: ids,
      names: names
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
  var elBody = $('hl-body'), elState = $('hl-state'), elFooter = $('hl-footer'),
      elCount = $('hl-count'), elPager = $('hl-pager'), elSearch = $('hl-search'), elTable = $('hl-table');
  var query = '', sort = { key: 'tanggal', dir: 'desc' }, page = 1, loadState = 'loading';

  function load(silent) {
    if (!silent) { loadState = 'loading'; render(); }
    /* Hari libur wajib; tahun ajaran + kelas hanya untuk pilihan "Kelas tertentu" (gagal tidak memblokir daftar). */
    var kelasJob = Promise.all([fetchAll(E.tahunAjaran), fetchAll(E.kelas)]).then(function (r) {
      var aktifId = '';
      r[0].forEach(function (t) {
        var a = pick(t, TA_KEYS.aktif, null);
        if (!aktifId && a !== null && flag(a)) aktifId = String(pick(t, TA_KEYS.id, ''));
      });
      return r[1].map(function (k) {
        return { id: String(pick(k, KELAS_KEYS.id, '')), idTA: String(pick(k, KELAS_KEYS.idTA, '')), nama: str(pick(k, KELAS_KEYS.nama, '')), tingkat: Number(pick(k, KELAS_KEYS.tingkat, 0)) || 0 };
      }).filter(function (k) { return aktifId && k.idTA === aktifId; })
        .sort(function (a, b) { return (a.tingkat - b.tingkat) || a.nama.localeCompare(b.nama, 'id', { numeric: true }); });
    }).catch(function () { return null; });

    return Promise.all([fetchAll(E.hariLibur), kelasJob]).then(function (r) {
      if (r[1]) kelasAktif = r[1];
      all = r[0].map(norm);
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
      return !q || (idDate(r.tanggal) + ' ' + r.tanggal + ' ' + r.ket + ' ' + berlaku(r)).toLowerCase().indexOf(q) > -1;
    });
    var m = sort.dir === 'desc' ? -1 : 1;
    return rows.slice().sort(function (a, b) {
      var x = sort.key === 'ket' ? a.ket : a.tanggal, y = sort.key === 'ket' ? b.ket : b.tanggal;
      return m * String(x).localeCompare(String(y), 'id', { sensitivity: 'base', numeric: true });
    });
  }
  function berlaku(r) { return r.names.length ? 'Kelas ' + r.names.join(', ') : 'Semua kelas'; }
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
      for (i = 0; i < 3; i++) sk += '<tr class="is-skeleton"><td colspan="4"><span class="skeleton skeleton--row"></span></td></tr>';
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
      showState('<strong>Belum ada hari libur</strong>Klik Tambah Hari Libur untuk menambahkan.');
      return;
    }
    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Tidak ada hasil</strong>Tidak ada hari libur yang cocok dengan “' + esc(query.trim()) + '”.' +
        '<br><button type="button" class="btn btn--outline" data-act="reset">' + icon('refresh', 'ico--sm') + ' Reset pencarian</button>');
      return;
    }
    showState('');

    var pages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
    if (page > pages) page = pages;
    var from = (page - 1) * PER_PAGE;
    var slice = rows.slice(from, from + PER_PAGE);

    elBody.innerHTML = slice.map(function (r) {
      var label = idDate(r.tanggal);
      return '<tr>' +
        '<td>' + esc(label) + '</td>' +
        '<td>' + (r.ket ? esc(r.ket) : '<span class="is-muted">Tanpa keterangan</span>') + '</td>' +
        '<td>' + esc(berlaku(r)) + '</td>' +
        '<td><div class="row-actions">' +
          '<button type="button" class="icon-act" data-act="edit" data-id="' + esc(r.id) + '" aria-label="Ubah hari libur ' + esc(label) + '" title="Ubah">' + icon('pencil', 'ico--sm') + '</button>' +
          '<button type="button" class="icon-act icon-act--danger" data-act="del" data-id="' + esc(r.id) + '" aria-label="Hapus hari libur ' + esc(label) + '" title="Hapus">' + icon('trash', 'ico--sm') + '</button>' +
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
  function resetView() { query = ''; sort = { key: 'tanggal', dir: 'desc' }; page = 1; elSearch.value = ''; render(); }
  $('hl-reset').addEventListener('click', resetView);

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
    if (b.getAttribute('data-act') === 'retry') load();
    if (b.getAttribute('data-act') === 'reset') resetView();
  });
  elBody.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-act]');
    if (!b) return;
    var item = byId(b.getAttribute('data-id'));
    if (!item) return;
    if (b.getAttribute('data-act') === 'edit') openForm(item); else openDelete(item);
  });

  /* ---------- formulir tambah / ubah ---------- */
  var dlg = $('dlg-libur'), form = $('form-libur');
  var F = { tanggal: $('hl-f-tanggal'), ket: $('hl-f-ket'), kelas: $('hl-kelas-list') };
  var ERR = { tanggal: $('hl-f-tanggal-err'), ket: $('hl-f-ket-err'), kelas: $('hl-f-kelas-err') };
  var elHint = $('hl-f-tanggal-hint'), elBox = $('hl-kelas-box'), elNote = $('hl-kelas-note');
  var btnSave = $('hl-save'), elFormMsg = $('hl-form-msg');
  var radios = form.querySelectorAll('input[name="hl-target"]');
  var editing = null, saving = false;

  function target() { return form.querySelector('input[name="hl-target"]:checked').value; }
  function fieldError(key, text) {
    ERR[key].textContent = text || ''; ERR[key].hidden = !text;
    if (key !== 'kelas') { F[key].classList.toggle('is-invalid', !!text); F[key].setAttribute('aria-invalid', text ? 'true' : 'false'); }
  }
  function clearErrors() {
    Object.keys(ERR).forEach(function (k) { fieldError(k, ''); });
    elFormMsg.hidden = true; elFormMsg.textContent = '';
  }
  function setSaving(on) { saving = on; btnSave.disabled = on; btnSave.textContent = on ? 'Menyimpan...' : 'Simpan'; }

  function fillKelas(selected) {
    if (!kelasAktif.length) {
      F.kelas.innerHTML = '';
      elNote.textContent = 'Daftar kelas tahun ajaran aktif belum tersedia. Buat kelas lebih dulu di menu Kelas.';
      return;
    }
    elNote.textContent = 'Pilih satu atau lebih kelas (tahun ajaran aktif).';
    F.kelas.innerHTML = kelasAktif.map(function (k) {
      return '<label class="check check--chip"><input type="checkbox" value="' + esc(k.id) + '"' + (selected.indexOf(k.id) > -1 ? ' checked' : '') + '> Kelas ' + esc(k.nama) + '</label>';
    }).join('');
  }
  function syncTarget() { elBox.hidden = target() !== 'tertentu'; fieldError('kelas', ''); }
  function syncHint() {
    var sun = isSunday(F.tanggal.value);
    elHint.textContent = sun ? 'Tanggal ini hari Minggu. Hari Minggu sudah otomatis libur, jadi tidak perlu diinput.' : 'Hari Minggu otomatis libur.';
  }

  function openForm(item) {
    editing = item || null;
    clearErrors();
    $('hl-form-title').textContent = editing ? 'Ubah Hari Libur' : 'Tambah Hari Libur';
    F.tanggal.value = editing ? editing.tanggal : '';
    F.ket.value = editing ? editing.ket : '';
    var ids = editing ? editing.ids : [];
    radios[0].checked = !ids.length; radios[1].checked = !!ids.length;
    fillKelas(ids);
    syncTarget(); syncHint();
    setSaving(false);
    dlg.showModal();
    F.tanggal.focus();
  }

  Array.prototype.forEach.call(radios, function (r) { r.addEventListener('change', syncTarget); });
  F.tanggal.addEventListener('input', syncHint);
  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('hl-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  function checkedIds() {
    return Array.prototype.map.call(F.kelas.querySelectorAll('input:checked'), function (c) { return c.value; });
  }

  /* Validasi di layar hanya pelengkap; server yang menentukan. */
  function validate(v) {
    var ok = true, first = null;
    function bad(key, text, el) { fieldError(key, text); ok = false; if (!first) first = el; }
    if (!/^\d{4}-\d{2}-\d{2}$/.test(v.tanggal) || isNaN(new Date(v.tanggal).getTime())) bad('tanggal', 'Tanggal wajib diisi dengan tanggal yang valid', F.tanggal);
    if (v.ket.length > 100) bad('ket', 'Keterangan maksimal 100 karakter', F.ket);
    if (v.target === 'tertentu' && !v.ids.length) bad('kelas', 'Pilih minimal satu kelas, atau pilih Semua kelas', F.kelas.querySelector('input') || radios[0]);
    if (first) first.focus();
    return ok;
  }

  /* Pesan 409/422 server tampil apa adanya; tandai isian yang paling mungkin terkait. */
  function showServerError(err) {
    var msg = errText(err);
    if (err && (err.status === 409 || err.status === 422)) {
      var key = /tanggal/i.test(msg) ? 'tanggal' : /kelas/i.test(msg) ? 'kelas' : /keterangan/i.test(msg) ? 'ket' : null;
      if (key) { fieldError(key, msg); return; }
    }
    elFormMsg.textContent = msg; elFormMsg.hidden = false;
  }

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    clearErrors();
    var v = { tanggal: F.tanggal.value, ket: F.ket.value.trim(), target: target(), ids: checkedIds() };
    if (!validate(v)) return;

    var body = {};
    body[SEND.tanggal] = v.tanggal;                 /* YYYY-MM-DD */
    body[SEND.ket] = v.ket || null;
    body[SEND.kelas] = v.target === 'tertentu'      /* kosong = Semua kelas (tanpa baris target) */
      ? v.ids.map(function (x) { return isNaN(Number(x)) ? x : Number(x); }) : [];

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), body) : Api.post(E.hariLibur, body);
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Hari libur diperbarui' : 'Hari libur ditambahkan');
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
    $('dlg-hapus-title').textContent = 'Hapus Hari Libur';
    dTarget.innerHTML = '<strong>' + esc(idDate(item.tanggal)) + '</strong>' + (item.ket ? ' · ' + esc(item.ket) : '') + ' · ' + esc(berlaku(item));
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
      toast('Hari libur dihapus');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      dMsg.className = err && err.status === 409 ? 'notice notice--info' : 'notice notice--warn';
      dMsg.textContent = errText(err);
      dMsg.hidden = false;
      if (err && err.status === 409) { dOk.hidden = true; dCancel.textContent = 'Tutup'; }
    }).then(function () { setDelBusy(false); });
  });

  load();
})();