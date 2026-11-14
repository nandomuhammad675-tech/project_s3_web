/* SI-KESA PWA Admin | Foto galeri (per event atau galeri umum).
 * Foto diunggah ke server (database + folder website assets/img/event|galeri), lalu dicatat di tabel galeri_kegiatan
 * lewat POST /api/admin/galeri { id_event | null, file_gambar: path, keterangan }. Website menampilkan dari sana. */
(function () {
  'use strict';
  var Api = window.SikesaApi, E = window.SIKESA.endpoints;
  var dlg = document.getElementById('dlg-galeri');
  if (!Api || !dlg) return;
  var $ = function (id) { return document.getElementById(id); };
  var MAX_SIDE = 1600;
  var ctx = { idEvent: null, nama: '' };
  var sibuk = false, pilih = [];

  function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function pesan(t) { var m = $('gl-msg'); m.hidden = !t; m.textContent = t || ''; }
  function toast(t, j) { if (window.SikesaUI) window.SikesaUI.toast(t, j); }

  function compress(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file), img = new Image();
      img.onload = function () {
        var w = img.naturalWidth, h = img.naturalHeight;
        if (!w || !h) { URL.revokeObjectURL(url); reject(new Error('Gambar tidak valid')); return; }
        var s = Math.min(1, MAX_SIDE / Math.max(w, h)), c = document.createElement('canvas');
        c.width = Math.round(w * s); c.height = Math.round(h * s);
        var g = c.getContext('2d'); g.fillStyle = '#fff'; g.fillRect(0, 0, c.width, c.height); g.drawImage(img, 0, 0, c.width, c.height);
        URL.revokeObjectURL(url);
        c.toBlob(function (b) { b ? resolve(b) : reject(new Error('Gagal memproses gambar')); }, 'image/jpeg', 0.82);
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('Berkas ' + file.name + ' bukan gambar yang didukung')); };
      img.src = url;
    });
  }

  function daftar() {
    $('gl-grid').innerHTML = '';
    var q = ctx.idEvent ? { id_event: ctx.idEvent, per_page: 100 } : { mandiri: 1, per_page: 100 };
    return Api.get(E.galeri, q).then(function (res) {
      var items = ((res && res.data && res.data.items) || []);
      $('gl-empty').hidden = items.length > 0;
      $('gl-grid').innerHTML = items.map(function (g) {
        return '<div class="gl-item"><img src="' + esc(Api.fileUrl(g.file_gambar)) + '" alt="' + esc(g.keterangan || 'Foto galeri') + '" loading="lazy">' +
          '<small>' + esc(g.keterangan || '') + '</small>' +
          '<button type="button" data-hapus="' + g.id_galeri + '" aria-label="Hapus foto" title="Hapus foto">&times;</button></div>';
      }).join('');
    }, function (err) { if (!(err && err.status === 401)) pesan((err && err.message) || 'Foto belum bisa dimuat.'); });
  }

  function buka(c) {
    ctx = c; pilih = []; pesan(''); $('gl-file').value = ''; $('gl-ket').value = ''; $('gl-unggah').disabled = true; $('gl-progress').hidden = true;
    $('gl-title').textContent = c.idEvent ? 'Foto Event: ' + c.nama : 'Galeri Umum';
    $('gl-lead').textContent = c.idEvent
      ? 'Foto disimpan di database dan di folder website (assets/img/event) dengan nama mengikuti nama event, lalu tampil di website.'
      : 'Foto galeri umum (mis. fasilitas sekolah) tampil pada halaman Galeri di website.';
    if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open', '');
    daftar();
  }

  $('gl-file').addEventListener('change', function (e) {
    pesan(''); pilih = Array.prototype.slice.call(e.target.files || []);
    $('gl-unggah').disabled = !pilih.length;
  });

  $('gl-unggah').addEventListener('click', function () {
    if (sibuk || !pilih.length) return;
    sibuk = true; $('gl-unggah').disabled = true; pesan('');
    var prog = $('gl-progress'); prog.hidden = false;
    var ket = $('gl-ket').value.trim() || null, ok = 0, gagal = [], rantai = Promise.resolve();
    pilih.forEach(function (f, i) {
      rantai = rantai.then(function () {
        prog.textContent = 'Mengunggah foto ' + (i + 1) + ' dari ' + pilih.length + '...';
        return compress(f).then(function (blob) {
          if (blob.size > 2 * 1024 * 1024) throw new Error(f.name + ' masih lebih dari 2 MB setelah diperkecil');
          var meta = ctx.idEvent ? { jenis: 'event', nama: ctx.nama } : { jenis: 'galeri', nama: ket || 'galeri-umum' };
          return Api.upload(new File([blob], 'foto.jpg', { type: 'image/jpeg' }), meta);
        }).then(function (path) {
          return Api.post(E.galeri, { id_event: ctx.idEvent, file_gambar: path, keterangan: ket });
        }).then(function () { ok++; }, function (err) {
          if (err && err.status === 401) throw err;
          gagal.push(f.name + ': ' + ((err && err.message) || 'gagal'));
        });
      });
    });
    rantai.then(function () {
      prog.hidden = true;
      if (gagal.length) pesan(ok + ' foto tersimpan, ' + gagal.length + ' gagal. ' + gagal[0]);
      else toast(ok + ' foto berhasil diunggah');
      $('gl-file').value = ''; pilih = [];
      return daftar();
    }, function () { /* 401 dialihkan ke login */ }).then(function () { sibuk = false; $('gl-unggah').disabled = true; });
  });

  $('gl-grid').addEventListener('click', function (e) {
    var b = e.target.closest('[data-hapus]'); if (!b || sibuk) return;
    if (!window.confirm('Hapus foto ini dari galeri? Berkasnya ikut dihapus.')) return;
    b.disabled = true;
    Api.del(E.galeri + '/' + encodeURIComponent(b.getAttribute('data-hapus'))).then(function () { toast('Foto dihapus'); return daftar(); },
      function (err) { b.disabled = false; pesan((err && err.message) || 'Foto gagal dihapus.'); });
  });

  $('gl-tutup').addEventListener('click', function () { if (!sibuk) dlg.close(); });
  dlg.addEventListener('cancel', function (e) { if (sibuk) e.preventDefault(); });
  document.addEventListener('sikesa:galeri-buka', function (e) { buka(e.detail || { idEvent: null, nama: '' }); });
})();
