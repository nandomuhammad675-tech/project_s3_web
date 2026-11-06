/* SI-KESA PWA Admin | Konten Website: Pengaturan (dokumen 01 bagian 3.7)
 * Dua isian: URL Formulir PPDB dan URL Video Profil.
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

  var form = $('form-pengaturan'), elState = $('set-state');
  var F = { ppdb: $('set-ppdb'), video: $('set-video') };
  var errBox = { ppdb: $('set-ppdb-err'), video: $('set-video-err') };
  var elConv = $('set-video-conv'), elMsg = $('set-msg'), elMeta = $('set-meta'), elMetaText = $('set-meta-text'), btnSave = $('set-save');
  var saving = false;

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

  /* ---------- helper tampilan ---------- */
  function fieldError(key, text) {
    errBox[key].textContent = text || ''; errBox[key].hidden = !text;
    F[key].classList.toggle('is-invalid', !!text);
    F[key].setAttribute('aria-invalid', text ? 'true' : 'false');
  }
  function clearMessages() {
    fieldError('ppdb', ''); fieldError('video', '');
    elMsg.hidden = true; elMsg.textContent = '';
  }
  function setSaving(on) { saving = on; btnSave.disabled = on; btnSave.textContent = on ? 'Menyimpan...' : 'Simpan Pengaturan'; }
  function showState(html) { elState.innerHTML = html; elState.hidden = !html; }
  function setFormEnabled(on) {
    F.ppdb.disabled = !on; F.video.disabled = !on;
    btnSave.disabled = !on; form.hidden = false;
  }
  function showMeta(oleh, pada) {
    var w = fmtWaktu(pada);
    if (oleh && w) elMetaText.innerHTML = 'Terakhir diubah oleh <strong>' + esc(oleh) + '</strong> pada ' + esc(w);
    else if (oleh) elMetaText.innerHTML = 'Terakhir diubah oleh <strong>' + esc(oleh) + '</strong>';
    else if (w) elMetaText.textContent = 'Terakhir diubah pada ' + w;
    else elMetaText.textContent = 'Belum pernah diubah.';
  }

  /* ---------- muat data ---------- */
  function readSettings(res) {
    var d = res && res.data !== undefined ? res.data : res;
    // Bentuk daftar kunci-nilai: [{ kunci, nilai }]
    if (Array.isArray(d)) {
      var o = {};
      d.forEach(function (r) { if (r && r.kunci) o[r.kunci] = r.nilai; });
      return o;
    }
    return d && typeof d === 'object' ? d : {};
  }

  function load() {
    form.hidden = false; showState('');
    F.ppdb.disabled = true; F.video.disabled = true; btnSave.disabled = true;
    elMetaText.innerHTML = '<span class="skeleton"></span>';
    return Api.get(E.pengaturan).then(function (res) {
      var d = readSettings(res);
      F.ppdb.value = String(pick(d, KEYS.ppdb, '') || '');
      F.video.value = String(pick(d, KEYS.video, '') || '');
      showMeta(String(pick(d, KEYS.oleh, '') || '').trim(), pick(d, KEYS.pada, ''));
      setFormEnabled(true);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      form.hidden = true;
      showState('<strong>Pengaturan gagal dimuat</strong>' + esc(errText(err)) +
        '<br><button type="button" class="btn btn--outline" data-act="retry">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>');
    });
  }
  elState.addEventListener('click', function (ev) { if (ev.target.closest('[data-act="retry"]')) load(); });

  /* ---------- ubah otomatis alamat YouTube ---------- */
  function convertVideo(announce) {
    var v = F.video.value.trim();
    var e = toEmbed(v);
    if (v && e !== v) {
      F.video.value = e;
      elConv.textContent = 'Alamat YouTube diubah otomatis ke bentuk embed.';
      elConv.hidden = false;
    } else if (!announce) {
      elConv.hidden = true;
    }
    return F.video.value.trim();
  }
  F.video.addEventListener('blur', function () { convertVideo(true); });
  F.video.addEventListener('input', function () { elConv.hidden = true; fieldError('video', ''); });
  F.ppdb.addEventListener('input', function () { fieldError('ppdb', ''); });
  F.ppdb.addEventListener('blur', function () { F.ppdb.value = F.ppdb.value.trim(); });

  /* ---------- simpan ---------- */
  function validate(key, value, label) {
    if (!value) return true; // kosong diperbolehkan
    if (value.length > URL_MAX) { fieldError(key, label + ' terlalu panjang (maksimal ' + URL_MAX + ' karakter)'); return false; }
    if (!isHttpUrl(value)) { fieldError(key, 'Gunakan URL yang diawali http:// atau https://'); return false; }
    return true;
  }

  /* Validasi di layar hanya pelengkap; server yang menentukan. */
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (saving) return;
    clearMessages();

    var ppdb = F.ppdb.value.trim();
    var video = convertVideo(true);
    var okPpdb = validate('ppdb', ppdb, 'URL PPDB');
    var okVideo = validate('video', video, 'URL video');
    if (!okPpdb) { F.ppdb.focus(); return; }
    if (!okVideo) { F.video.focus(); return; }

    var body = {};
    body[SEND.ppdb] = ppdb || null;
    body[SEND.video] = video || null;

    setSaving(true);
    Api.put(E.pengaturan, body).then(function (res) {
      toast('Pengaturan tersimpan');
      var d = readSettings(res);
      var oleh = String(pick(d, KEYS.oleh, '') || '').trim();
      if (oleh || pick(d, KEYS.pada, '')) showMeta(oleh, pick(d, KEYS.pada, ''));
      else return load(); // server tidak mengirim ulang data: muat ulang untuk mengambil "terakhir diubah"
    }).catch(function (err) {
      if (err && err.status === 401) return;
      elMsg.textContent = errText(err); elMsg.hidden = false;
    }).then(function () { setSaving(false); });
  });

  load();
})();