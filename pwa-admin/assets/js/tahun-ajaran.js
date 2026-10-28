(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

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
    if (v === undefined || v === null || v === '') return null;
    if (typeof v === 'boolean') return v;
    if (typeof v === 'number') return v === 1;
    var s = String(v).toLowerCase();
    if (['1', 'true', 'aktif', 'active', 'ya'].indexOf(s) > -1) return true;
    if (['0', 'false', 'nonaktif', 'tidak', 'inactive', 'tidak aktif'].indexOf(s) > -1) return false;
    return null;
  }
  function icon(n, cls) { return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
  function toast(t, type) { if (window.SikesaUI) window.SikesaUI.toast(t, type); }
  function errText(err) { return err && err.message ? err.message : 'Terjadi gangguan, coba lagi'; }
  function itemUrl(id) { return E.tahunAjaran + '/' + encodeURIComponent(id); }

  function norm(r) {
    return {
      id: pick(r, ['id', 'id_tahun_ajaran'], null),
      nama: String(pick(r, ['nama_tahun', 'nama', 'tahun_ajaran', 'tahun'], '') || '').trim(),
      semester: String(pick(r, ['semester_berjalan', 'semester_aktif', 'semester'], '') || '').toLowerCase(),
      aktif: flag(pick(r, ['status', 'status_aktif', 'is_aktif', 'aktif'], null)) === true
    };
  }
  function byId(id) {
    for (var i = 0; i < all.length; i++) if (String(all[i].id) === String(id)) return all[i];
    return null;
  }

  /* ---------- elemen & state ---------- */
  var elBody = $('ta-body'), elState = $('ta-state'), elFooter = $('ta-footer'),
      elCount = $('ta-count'), elPager = $('ta-pager'), elSearch = $('ta-search'), elTable = $('ta-table');

  var all = [], query = '', sort = null, page = 1, loadState = 'loading';

  /* ---------- muat data ---------- */
  function load(silent) {
    if (!silent) { loadState = 'loading'; render(); }
    return Api.get(E.tahunAjaran).then(function (res) {
      all = toList(res).map(norm);
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
    var rows = all.filter(function (r) { return !q || r.nama.toLowerCase().indexOf(q) > -1; });
    if (sort) {
      var m = sort.dir === 'desc' ? -1 : 1;
      rows = rows.slice().sort(function (a, b) { return m * a.nama.localeCompare(b.nama, 'id', { numeric: true }); });
    }
    return rows;
  }

  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }

  /* Semester tampil "—" untuk tahun yang belum pernah aktif. */
  function semesterLabel(r) {
    if (r.aktif) return r.semester === 'genap' ? 'Genap' : 'Ganjil';
    return r.semester === 'genap' ? 'Genap' : '—';
  }

  function tindakan(r) {
    if (r.aktif && r.semester !== 'genap') {
      return '<button type="button" class="btn btn--outline btn--sm" data-act="sem" data-id="' + esc(r.id) + '">Ganti Semester</button>';
    }
    if (!r.aktif && r.semester !== 'genap') {
      return '<button type="button" class="btn btn--outline btn--sm" data-act="aktif" data-id="' + esc(r.id) + '">Aktifkan</button>';
    }
    if (!r.aktif) return '<span class="cell-italic">Selesai</span>';
    return '<span class="cell-italic">—</span>'; // aktif & sudah genap: tombol disembunyikan
  }

  function render(errMsg) {
    var i;
    Array.prototype.forEach.call(elTable.querySelectorAll('.th-sort'), function (b) {
      var on = !!sort;
      b.classList.toggle('is-sorted', on);
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
      showState('<strong>Belum ada tahun ajaran</strong>Klik Tambah Tahun Ajaran untuk membuat yang pertama.');
      return;
    }
    var rows = filtered();
    if (!rows.length) {
      elBody.innerHTML = ''; elFooter.hidden = true;
      showState('<strong>Tidak ada hasil</strong>Tidak ada tahun ajaran yang cocok dengan “' + esc(query.trim()) + '”.' +
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
        '<td>' + esc(r.nama) + '</td>' +
        '<td>' + esc(semesterLabel(r)) + '</td>' +
        '<td><span class="badge ' + (r.aktif ? 'badge--aktif' : 'badge--nonaktif') + '">' + (r.aktif ? 'Aktif' : 'Nonaktif') + '</span></td>' +
        '<td>' + tindakan(r) + '</td>' +
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
  $('ta-reset').addEventListener('click', resetView);

  elTable.querySelector('thead').addEventListener('click', function (ev) {
    if (!ev.target.closest('.th-sort')) return;
    if (!sort) sort = { dir: 'asc' };
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
    var act = b.getAttribute('data-act');
    if (act === 'edit') openForm(item);
    else if (act === 'del') openDelete(item);
    else if (act === 'sem') openSemester(item);
    else if (act === 'aktif') openAktif(item);
  });

  /* ---------- tambah / ubah ---------- */
  var dlg = $('dlg-ta'), form = $('form-ta'), elNama = $('ta-nama'), btnSave = $('ta-save'),
      elNamaErr = $('ta-nama-err'), elFormMsg = $('ta-form-msg');
  var editing = null, saving = false;

  function fieldError(text) {
    elNamaErr.textContent = text || ''; elNamaErr.hidden = !text;
    elNama.classList.toggle('is-invalid', !!text);
    elNama.setAttribute('aria-invalid', text ? 'true' : 'false');
  }
  function setSaving(on) { saving = on; btnSave.disabled = on; btnSave.textContent = on ? 'Menyimpan...' : 'Simpan'; }

  function openForm(item) {
    editing = item || null;
    fieldError(''); elFormMsg.hidden = true; elFormMsg.textContent = '';
    $('ta-form-title').textContent = editing ? 'Ubah Tahun Ajaran' : 'Tambah Tahun Ajaran';
    $('ta-form-lead').textContent = editing ? 'Ubah nama tahun ajaran, lalu simpan.' : 'Semester otomatis Ganjil dan tahun ajaran belum aktif.';
    elNama.value = editing ? editing.nama : '';
    setSaving(false);
    dlg.showModal();
    elNama.focus();
  }

  $('btn-tambah').addEventListener('click', function () { openForm(null); });
  $('ta-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    fieldError(''); elFormMsg.hidden = true;
    var nama = elNama.value.trim();
    if (!nama) { fieldError('Nama tahun wajib diisi'); elNama.focus(); return; }
    if (!/^\d{4}\/\d{4}$/.test(nama)) { fieldError('Format harus seperti 2026/2027'); elNama.focus(); return; }

    setSaving(true);
    var wasEdit = !!editing;
    var req = wasEdit ? Api.put(itemUrl(editing.id), { nama_tahun: nama }) : Api.post(E.tahunAjaran, { nama_tahun: nama });
    req.then(function () {
      dlg.close();
      toast(wasEdit ? 'Tahun ajaran diperbarui' : 'Tahun ajaran ditambahkan');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      elFormMsg.textContent = errText(err); elFormMsg.hidden = false; // 409/422: pesan server
    }).then(function () { setSaving(false); });
  });

  /* ---------- ganti semester ---------- */
  var sDlg = $('dlg-ta-sem'), sForm = $('form-ta-sem'), sChk = $('ta-sem-confirm'),
      sOk = $('ta-sem-ok'), sMsg = $('ta-sem-msg'), semTarget = null, semBusy = false;

  function openSemester(item) {
    semTarget = item; sChk.checked = false; sOk.disabled = true;
    sMsg.hidden = true; sMsg.textContent = '';
    sOk.textContent = 'Lanjutkan'; semBusy = false;
    sDlg.showModal();
  }
  sChk.addEventListener('change', function () { sOk.disabled = semBusy || !sChk.checked; });
  $('ta-sem-cancel').addEventListener('click', function () { if (!semBusy) sDlg.close(); });
  sDlg.addEventListener('cancel', function (ev) { if (semBusy) ev.preventDefault(); });

  sForm.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (semBusy || !sChk.checked || !semTarget) return;
    semBusy = true; sOk.disabled = true; sOk.textContent = 'Memproses...'; sMsg.hidden = true;
    Api.put(itemUrl(semTarget.id), { semester_aktif: 'genap', konfirmasi_semester: true }).then(function () {
      sDlg.close();
      toast('Semester berhasil diganti ke Genap');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      sMsg.textContent = errText(err); sMsg.hidden = false;
    }).then(function () { semBusy = false; sOk.textContent = 'Lanjutkan'; sOk.disabled = !sChk.checked; });
  });

  /* ---------- aktifkan ---------- */
  var aDlg = $('dlg-ta-aktif'), aOk = $('ta-aktif-ok'), aMsg = $('ta-aktif-msg'), aTarget = null, aBusy = false;

  function openAktif(item) {
    aTarget = item; aBusy = false;
    $('ta-aktif-text').textContent = 'Aktifkan tahun ajaran ' + item.nama + '? Pastikan kenaikan kelas sudah dijalankan. Sebaiknya dilakukan di luar jam sekolah.';
    aMsg.hidden = true; aMsg.textContent = '';
    aOk.disabled = false; aOk.textContent = 'Aktifkan';
    aDlg.showModal();
  }
  $('ta-aktif-cancel').addEventListener('click', function () { if (!aBusy) aDlg.close(); });
  aDlg.addEventListener('cancel', function (ev) { if (aBusy) ev.preventDefault(); });

  aOk.addEventListener('click', function () {
    if (aBusy || !aTarget) return;
    aBusy = true; aOk.disabled = true; aOk.textContent = 'Memproses...'; aMsg.hidden = true;
    Api.post(itemUrl(aTarget.id) + '/aktifkan', {}).then(function () {
      aDlg.close();
      toast('Tahun ajaran ' + aTarget.nama + ' aktif');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      aMsg.textContent = errText(err); aMsg.hidden = false; // 422 (belum ada kenaikan kelas): pesan server
    }).then(function () { aBusy = false; aOk.disabled = false; aOk.textContent = 'Aktifkan'; });
  });

  /* ---------- hapus ---------- */
  var dDlg = $('dlg-hapus'), dTarget = $('dlg-hapus-target'), dMsg = $('dlg-hapus-msg'),
      dOk = $('dlg-hapus-ok'), dCancel = $('dlg-hapus-cancel');
  var deleting = null, delBusy = false;

  function setDelBusy(on) { delBusy = on; dOk.disabled = on; dOk.textContent = on ? 'Menghapus...' : 'Hapus'; }

  function openDelete(item) {
    deleting = item;
    $('dlg-hapus-title').textContent = 'Hapus Tahun Ajaran';
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
      toast('Tahun ajaran dihapus');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      var msg = errText(err);
      if (err && err.status === 409) {
        dMsg.className = 'notice notice--info';
        dMsg.innerHTML = esc(msg) + '<span class="notice__hint">Tahun ajaran yang masih dipakai tidak dapat dihapus.</span>';
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