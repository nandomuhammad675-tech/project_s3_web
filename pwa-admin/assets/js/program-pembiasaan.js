/* SI-KESA PWA Admin | Konten Website: Program Pembiasaan (dokumen 01 bagian 3.7)
 * Hanya ubah enam baris: nama, deskripsi, ikon. Tidak ada tombol tambah atau hapus.
 * Hari tidak bisa diubah (tidak dikirim ke server). */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var E = window.SIKESA.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    id: ['id_program', 'id_program_pembiasaan', 'id'],
    hari: ['hari'],
    nama: ['nama_program', 'nama'],
    deskripsi: ['deskripsi'],
    ikon: ['ikon', 'ikon_url']
  };
  var SEND = { nama: 'nama_program', deskripsi: 'deskripsi', ikon: 'ikon' };
  var HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
  var HARI_LABEL = { senin: 'Senin', selasa: 'Selasa', rabu: 'Rabu', kamis: 'Kamis', jumat: 'Jumat', sabtu: 'Sabtu' };
  var NAMA_MAX = 50, MAX_BYTES = 2 * 1024 * 1024, MAX_SIDE = 256;

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
  function itemUrl(id) { return E.programPembiasaan + '/' + encodeURIComponent(id); }
  function safeSrc(s) {
    s = String(s || '');
    return /^(https?:\/\/|\/|data:image\/(png|jpe?g|webp);base64,)/i.test(s) ? s : '';
  }

  function norm(r) {
    var hari = String(pick(r, KEYS.hari, '') || '').toLowerCase();
    return {
      id: pick(r, KEYS.id, null),
      hari: hari,
      nama: String(pick(r, KEYS.nama, '') || '').trim(),
      deskripsi: String(pick(r, KEYS.deskripsi, '') || '').trim(),
      ikon: safeSrc(pick(r, KEYS.ikon, ''))
    };
  }
  function byId(id) {
    for (var i = 0; i < items.length; i++) if (String(items[i].id) === String(id)) return items[i];
    return null;
  }

  var elGrid = $('pb-grid'), elState = $('pb-state');
  var items = [];

  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }

  /* ---------- muat data ---------- */
  function load(silent) {
    if (!silent) { elGrid.hidden = false; showState(''); }
    return Api.get(E.programPembiasaan).then(function (res) {
      items = toList(res).map(norm).filter(function (r) { return HARI.indexOf(r.hari) > -1; });
      // Urut Senin sampai Sabtu
      items.sort(function (a, b) { return HARI.indexOf(a.hari) - HARI.indexOf(b.hari); });
      render();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      elGrid.hidden = true;
      showState('<strong>Data gagal dimuat</strong>' + esc(errText(err)) +
        '<br><button type="button" class="btn btn--outline" data-act="retry">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>');
    });
  }

  function render() {
    if (!items.length) {
      elGrid.hidden = true;
      showState('<strong>Data program pembiasaan belum tersedia</strong>Enam program (Senin sampai Sabtu) disiapkan oleh Backend. Hubungi pengembang bila daftar ini kosong.');
      return;
    }
    showState(''); elGrid.hidden = false;
    elGrid.innerHTML = items.map(function (r, i) {
      var no = (i + 1 < 10 ? '0' : '') + (i + 1);
      var desk = r.deskripsi || 'Belum ada deskripsi.';
      var ikon = r.ikon ? '<span class="pb-card__ikon"><img src="' + esc(r.ikon) + '" alt="" loading="lazy"></span>' : '';
      return '<article class="pb-card">' +
        '<span class="pb-card__no" aria-hidden="true">' + no + '</span>' +
        '<div class="pb-card__body">' +
          '<div class="pb-card__hari">' + esc(HARI_LABEL[r.hari]) + '</div>' +
          '<h2 class="pb-card__nama">' + esc(r.nama || 'Belum diberi nama') + '</h2>' +
          '<p class="pb-card__desk">' + esc(desk) + '</p>' +
        '</div>' + ikon +
        '<button type="button" class="icon-act" data-act="edit" data-id="' + esc(r.id) + '" aria-label="Ubah program ' + esc(HARI_LABEL[r.hari]) + '" title="Ubah">' + icon('pencil', 'ico--sm') + '</button>' +
        '</article>';
    }).join('');
  }

  elState.addEventListener('click', function (ev) {
    if (ev.target.closest('[data-act="retry"]')) load();
  });
  elGrid.addEventListener('click', function (ev) {
    var b = ev.target.closest('button[data-act="edit"]');
    if (!b) return;
    var item = byId(b.getAttribute('data-id'));
    if (item) openForm(item);
  });

  /* ---------- formulir ubah ---------- */
  var dlg = $('dlg-program'), form = $('form-program');
  var F = { nama: $('pb-nama'), deskripsi: $('pb-deskripsi'), ikon: $('pb-ikon') };
  var errBox = { nama: $('pb-nama-err'), deskripsi: $('pb-deskripsi-err'), ikon: $('pb-ikon-err') };
  var btnSave = $('pb-save'), elFormMsg = $('pb-form-msg'), elPrev = $('pb-ikon-preview'), btnClear = $('pb-ikon-clear');
  var editing = null, saving = false, ikonState = 'keep', ikonData = null; // keep | new | clear

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
  function showIkon(src) {
    elPrev.innerHTML = src ? '<img alt="" src="' + esc(src) + '">' : icon('image');
    btnClear.hidden = !src;
  }

  function openForm(item) {
    editing = item;
    clearErrors();
    $('pb-hari').textContent = HARI_LABEL[item.hari];
    F.nama.value = item.nama;
    F.deskripsi.value = item.deskripsi;
    F.ikon.value = ''; ikonState = 'keep'; ikonData = null;
    showIkon(item.ikon);
    setSaving(false);
    dlg.showModal();
    F.nama.focus();
  }
  $('pb-cancel').addEventListener('click', function () { if (!saving) dlg.close(); });
  dlg.addEventListener('cancel', function (ev) { if (saving) ev.preventDefault(); });

  F.ikon.addEventListener('change', function () {
    fieldError('ikon', '');
    var file = F.ikon.files && F.ikon.files[0];
    if (!file) return;
    if (['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
      F.ikon.value = ''; fieldError('ikon', 'Format gambar tidak didukung'); return;
    }
    if (file.size > MAX_BYTES) {
      F.ikon.value = ''; fieldError('ikon', 'Ukuran gambar maksimal 2 MB'); return;
    }
    compress(file).then(function (d) {
      ikonState = 'new'; ikonData = d; showIkon(d);
    }).catch(function () {
      F.ikon.value = ''; fieldError('ikon', 'Format gambar tidak didukung');
    });
  });
  btnClear.addEventListener('click', function () {
    F.ikon.value = ''; ikonState = 'clear'; ikonData = null; fieldError('ikon', ''); showIkon('');
  });

  function compress(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file), img = new Image();
      img.onload = function () {
        var w = img.naturalWidth, h = img.naturalHeight;
        if (!w || !h) { URL.revokeObjectURL(url); reject(); return; }
        var s = Math.min(1, MAX_SIDE / Math.max(w, h)), c = document.createElement('canvas');
        c.width = Math.round(w * s); c.height = Math.round(h * s);
        var ctx = c.getContext('2d'), keepAlpha = file.type !== 'image/jpeg';
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
    if (saving || !editing) return;
    clearErrors();
    var nama = F.nama.value.trim();
    if (!nama) { fieldError('nama', 'Nama wajib diisi'); F.nama.focus(); return; }
    if (nama.length > NAMA_MAX) { fieldError('nama', 'Nama maksimal 50 karakter'); F.nama.focus(); return; }

    var body = {};
    body[SEND.nama] = nama;
    body[SEND.deskripsi] = F.deskripsi.value.trim() || null;
    if (ikonState === 'new') body[SEND.ikon] = ikonData;
    else if (ikonState === 'clear') body[SEND.ikon] = null;
    // Hari sengaja tidak dikirim: tidak bisa diubah.

    setSaving(true);
    Api.put(itemUrl(editing.id), body).then(function () {
      dlg.close();
      toast('Program pembiasaan diperbarui');
      return load(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      elFormMsg.textContent = errText(err); elFormMsg.hidden = false;
    }).then(function () { setSaving(false); });
  });

  load();
})();