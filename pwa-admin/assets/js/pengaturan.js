/* SI-KESA PWA Admin | Konten Website: Pengaturan (dokumen 01 bagian 3.7)
 * Dua tautan, masing-masing punya tombol Simpan dan Hapus sendiri.
 * Validasi: harus diawali http:// atau https://. Kosong diperbolehkan (website publik menyesuaikan).
 * Alamat YouTube (watch / youtu.be / shorts) diubah otomatis ke bentuk embed.
 * Tampil "Terakhir diubah oleh [nama] pada [waktu]". Tidak ada unggah video. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var E = window.SIKESA.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* Nama kolom dari/ke server. Ubah di sini bila kontrak API berbeda. */
  var KEYS = {
    ppdb: ['ppdb_form_url'],
    video: ['video_profil_url'],
    oleh: ['diubah_oleh_nama', 'diubah_oleh', 'nama_pengubah', 'updated_by_nama', 'updated_by'],
    pada: ['diubah_pada', 'updated_at']
  };
  var SEND = { ppdb: 'ppdb_form_url', video: 'video_profil_url' };
  var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var URL_MAX = 500;

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
  function icon(n, cls) { return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
  function toast(t, type) { if (window.SikesaUI) window.SikesaUI.toast(t, type); }
  function errText(err) { return err && err.message ? err.message : 'Terjadi gangguan, coba lagi'; }

  /* ---------- util URL ---------- */
  function isHttpUrl(s) {
    if (!/^https?:\/\//i.test(s)) return false;
    try { var u = new URL(s); return (u.protocol === 'http:' || u.protocol === 'https:') && !!u.hostname; } catch (e) { return false; }
  }
  function isYoutubeHost(h) { return /^(www\.|m\.|music\.)?youtube(-nocookie)?\.com$/i.test(h) || /^youtu\.be$/i.test(h); }

  /* Ubah alamat YouTube ke bentuk embed. Bila bukan YouTube atau sudah embed, kembalikan apa adanya. */
  function toEmbed(s) {
    if (!isHttpUrl(s)) return s;
    var u;
    try { u = new URL(s); } catch (e) { return s; }
    if (!isYoutubeHost(u.hostname)) return s;
    var id = '', parts = u.pathname.split('/').filter(Boolean);
    if (/^youtu\.be$/i.test(u.hostname)) id = parts[0] || '';
    else if (parts[0] === 'watch') id = u.searchParams.get('v') || '';
    else if (parts[0] === 'shorts' || parts[0] === 'live') id = parts[1] || '';
    else return s; // sudah /embed/ atau bentuk lain
    return /^[\w-]{6,}$/.test(id) ? 'https://www.youtube.com/embed/' + id : s;
  }

  /* ---------- waktu: tampil WIB ---------- */
  function fmtWaktu(raw) {
    raw = String(raw || '').trim();
    if (!raw) return '';
    var m = /^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/.exec(raw);
    if (!m) return '';
    var y = m[1], mo = Number(m[2]), d = Number(m[3]), hh = m[4], mm = m[5];
    // Bila ada zona waktu (Z / +hh:mm), ubah ke WIB. Tanpa zona dianggap sudah waktu server (WIB).
    if (/(Z|[+-]\d{2}:?\d{2})$/.test(raw)) {
      var dt = new Date(raw);
      if (isNaN(dt.getTime())) return '';
      var p = {};
      new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Jakarta', year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false })
        .formatToParts(dt).forEach(function (x) { p[x.type] = x.value; });
      y = p.year; mo = Number(p.month); d = Number(p.day); hh = p.hour === '24' ? '00' : p.hour; mm = p.minute;
    }
    return Number(d) + ' ' + BULAN[mo - 1] + ' ' + y + ', ' + hh + '.' + mm + ' WIB';
  }

  var KEY = ['ppdb', 'video'];
  var N = { ppdb: 'PPDB', video: 'video profil' };
  var el = {};
  KEY.forEach(function (k) {
    el[k] = { form: $('form-' + k), input: $('set-' + k), err: $('set-' + k + '-err'), conv: $('set-' + k + '-conv'),
              save: $('set-' + k + '-save'), hapus: $('set-' + k + '-hapus'), status: $('set-' + k + '-status') };
  });
  var elState = $('set-state'), elMsg = $('set-msg'), elMetaText = $('set-meta-text');
  var tersimpan = { ppdb: '', video: '' }, saving = false;

  function readSettings(res) {
    var d = res && res.data !== undefined ? res.data : res;
    if (Array.isArray(d)) { var o = {}; d.forEach(function (r) { if (r && r.kunci) o[r.kunci] = r.nilai; }); return o; }
    return d && typeof d === 'object' ? d : {};
  }
  function fieldError(k, text) {
    el[k].err.textContent = text || ''; el[k].err.hidden = !text;
    el[k].input.classList.toggle('is-invalid', !!text);
    el[k].input.setAttribute('aria-invalid', text ? 'true' : 'false');
  }
  function showMeta(oleh, pada) {
    var w = fmtWaktu(pada);
    if (oleh && w) elMetaText.innerHTML = 'Terakhir diubah oleh <strong>' + esc(oleh) + '</strong> pada ' + esc(w);
    else if (oleh) elMetaText.innerHTML = 'Terakhir diubah oleh <strong>' + esc(oleh) + '</strong>';
    else if (w) elMetaText.textContent = 'Terakhir diubah pada ' + w;
    else elMetaText.textContent = 'Belum pernah diubah.';
  }
  function tampilkan(k) {
    var ada = !!tersimpan[k];
    el[k].input.value = tersimpan[k];
    el[k].status.textContent = ada ? 'Tautan aktif dan tampil di website.' : 'Belum ada tautan (tidak tampil di website).';
    el[k].hapus.disabled = !ada || saving;
    el[k].save.disabled = saving;
    el[k].conv.hidden = true;
  }
  function setBusy(on) {
    saving = on;
    KEY.forEach(function (k) {
      el[k].input.disabled = on; el[k].save.disabled = on; el[k].hapus.disabled = on || !tersimpan[k];
    });
  }
  function apply(res) {
    var d = readSettings(res);
    tersimpan.ppdb = String(pick(d, KEYS.ppdb, '') || '');
    tersimpan.video = String(pick(d, KEYS.video, '') || '');
    KEY.forEach(tampilkan);
    showMeta(String(pick(d, KEYS.oleh, '') || ''), pick(d, KEYS.pada, ''));
  }
  function showError(t) { elMsg.textContent = t; elMsg.hidden = !t; }

  function muat() {
    elState.hidden = true;
    return Api.get(E.pengaturan).then(function (res) {
      KEY.forEach(function (k) { el[k].input.disabled = false; });
      apply(res);
    }, function (err) {
      if (err && err.status === 401) return;
      elState.hidden = false;
      elState.innerHTML = '<p>' + esc(errText(err)) + '</p><button type="button" class="btn btn--outline" id="set-retry">Coba lagi</button>';
      var r = $('set-retry'); if (r) r.addEventListener('click', muat);
    });
  }

  function kirim(k, nilai, sukses) {
    var body = {}; body[SEND[k]] = nilai;
    showError(''); setBusy(true);
    Api.put(E.pengaturan, body).then(function (res) {
      setBusy(false);
      // sinkronkan hanya tautan yang diubah; tautan lain tetap seperti tersimpan
      var d = readSettings(res);
      var baru = pick(d, k === 'ppdb' ? KEYS.ppdb : KEYS.video, nilai);
      tersimpan[k] = String(baru || '');
      KEY.forEach(tampilkan);
      showMeta(String(pick(d, KEYS.oleh, '') || ''), pick(d, KEYS.pada, ''));
      toast(sukses);
    }, function (err) {
      setBusy(false); KEY.forEach(tampilkan);
      if (err && err.status === 401) return;
      if (err && err.status === 422 && err.errors && err.errors[SEND[k]]) fieldError(k, String(err.errors[SEND[k]]));
      else showError(errText(err));
    });
  }

  KEY.forEach(function (k) {
    el[k].input.addEventListener('input', function () {
      fieldError(k, '');
      if (k === 'video') {
        var v = el.video.input.value.trim(), c = toEmbed(v);
        el.video.conv.hidden = !(v && c !== v);
        if (!el.video.conv.hidden) el.video.conv.textContent = 'Akan disimpan sebagai: ' + c;
      }
    });
    el[k].form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (saving) return;
      var v = el[k].input.value.trim();
      if (!v) { fieldError(k, 'Isi tautan, atau gunakan tombol Hapus Tautan untuk mengosongkan.'); return; }
      if (v.length > URL_MAX) { fieldError(k, 'Tautan terlalu panjang (maks ' + URL_MAX + ' karakter).'); return; }
      if (!isHttpUrl(v)) { fieldError(k, 'Tautan harus diawali http:// atau https://.'); return; }
      if (k === 'video') v = toEmbed(v);
      kirim(k, v, 'Tautan ' + N[k] + ' disimpan');
    });
    el[k].hapus.addEventListener('click', function () {
      if (saving || !tersimpan[k]) return;
      var pesan = k === 'ppdb'
        ? 'Hapus tautan PPDB? Gunakan ini bila pendaftaran sudah ditutup. Tombol PPDB di website tidak lagi menampilkan tautan.'
        : 'Hapus tautan video profil? Video tidak lagi tampil di website.';
      if (!window.confirm(pesan)) return;
      kirim(k, null, 'Tautan ' + N[k] + ' dihapus');
    });
  });

  muat();
})();
