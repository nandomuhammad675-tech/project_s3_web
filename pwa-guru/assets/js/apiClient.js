/*
 * SI-KESA PWA Guru: klien API (TANPA DOM / tampilan).
 * Skrip biasa (bukan ES module): muat PERTAMA, sebelum file lain. Hasilnya di window.SIKESA.api.
 *
 * Konfigurasi (opsional), definisikan SEBELUM memuat skrip ini:
 *   window.SIKESA_CONFIG = { apiBase: 'https://…/backend/public', loginUrl: '…', tokenKey: 'sikesa_guru_token' };
 *
 * Kontrak endpoint: docs/KONTRAK_API.md
 */
(function (global) {
  'use strict';

  var SIKESA = (global.SIKESA = global.SIKESA || {});
  var DEFAULTS = {
    apiBase: 'http://localhost/si-kesa/backend/public',
    tokenKey: 'sikesa_guru_token',
    loginUrl: null // bila diisi, klien pindah ke alamat ini saat sesi berakhir (401)
  };
  var pendengarSesi = [];

  function cfg() {
    var c = {};
    var u = global.SIKESA_CONFIG || {};
    for (var k in DEFAULTS) c[k] = u[k] !== undefined ? u[k] : DEFAULTS[k];
    c.apiBase = String(c.apiBase).replace(/\/+$/, '');
    return c;
  }

  /** Kesalahan API. status 0 = jaringan/format respons bukan JSON. */
  function ApiError(status, message, errors) {
    var e = new Error(message);
    e.name = 'ApiError';
    e.status = status;
    e.errors = errors || {};
    return e;
  }
  ApiError.prototype = Object.create(Error.prototype);

  // ---- token (localStorage) ----
  function ambilToken() {
    try { return global.localStorage.getItem(cfg().tokenKey); } catch (e) { return null; }
  }
  function simpanToken(t) {
    try { global.localStorage.setItem(cfg().tokenKey, t); } catch (e) { /* mode privat: abaikan */ }
  }
  function hapusToken() {
    try { global.localStorage.removeItem(cfg().tokenKey); } catch (e) { /* abaikan */ }
  }

  /** Daftarkan fungsi yang dipanggil saat sesi berakhir (401). Tampilan memakainya untuk kembali ke login. */
  function onSesiBerakhir(fn) {
    if (typeof fn === 'function') pendengarSesi.push(fn);
  }
  function sesiBerakhir(pesan) {
    hapusToken();
    pendengarSesi.forEach(function (fn) { try { fn(pesan); } catch (e) { /* jangan menggagalkan yang lain */ } });
    if (typeof global.dispatchEvent === 'function' && typeof global.CustomEvent === 'function') {
      global.dispatchEvent(new global.CustomEvent('sikesa:sesi-berakhir', { detail: { pesan: pesan } }));
    }
    var url = cfg().loginUrl;
    if (url && global.location) global.location.assign(url);
  }

  function qs(query) {
    var bagian = [];
    Object.keys(query || {}).forEach(function (k) {
      var v = query[k];
      if (v === null || v === undefined || v === '') return;
      bagian.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
    });
    return bagian.length ? '?' + bagian.join('&') : '';
  }

  function buatHeader(opsi, adaBody) {
    var h = { Accept: 'application/json' };
    if (adaBody) h['Content-Type'] = 'application/json';
    var t = opsi.auth === false ? null : ambilToken();
    if (t) h.Authorization = 'Bearer ' + t;
    return h;
  }

  function pesanJaringan() {
    return 'Tidak dapat terhubung ke server. Periksa koneksi internet lalu coba lagi.';
  }

  function ubahKeError(res, json, opsi) {
    var pesan = (json && json.message) || 'Terjadi gangguan, coba lagi.';
    if (res.status === 401 && opsi.auth !== false) sesiBerakhir(pesan);
    return ApiError(res.status, pesan, json && json.errors);
  }

  /**
   * request(metode, path, { query, body, auth = true, penuh = false })
   * Mengembalikan `data` dari respons sukses ({success,message,data}); penuh:true mengembalikan seluruh badan.
   * Melempar ApiError. 401 pada permintaan bertoken => token dihapus + pemberitahuan sesi berakhir.
   */
  function request(metode, path, opsi) {
    opsi = opsi || {};
    var adaBody = opsi.body !== undefined && opsi.body !== null;
    var url = cfg().apiBase + path + qs(opsi.query);

    return global.fetch(url, {
      method: metode,
      headers: buatHeader(opsi, adaBody),
      body: adaBody ? JSON.stringify(opsi.body) : undefined
    }).then(function (res) {
      return res.text().then(function (teks) {
        var json = null;
        try { json = teks ? JSON.parse(teks) : null; } catch (e) { json = null; }
        if (!res.ok || !json || json.success !== true) {
          if (json === null && res.ok) throw ApiError(0, 'Respons server tidak dapat dibaca.');
          throw ubahKeError(res, json, opsi);
        }
        return opsi.penuh ? json : json.data;
      });
    }, function () {
      throw ApiError(0, pesanJaringan());
    });
  }

  /** Unduh berkas (mis. Excel). Mengembalikan { blob, namaFile }. Token dikirim lewat header, bukan URL. */
  function unduh(path, query) {
    return global.fetch(cfg().apiBase + path + qs(query), {
      method: 'GET',
      headers: buatHeader({}, false)
    }).then(function (res) {
      var tipe = (res.headers.get('Content-Type') || '').toLowerCase();
      if (!res.ok || tipe.indexOf('application/json') === 0) {
        return res.text().then(function (teks) {
          var json = null;
          try { json = JSON.parse(teks); } catch (e) { json = null; }
          throw ubahKeError(res, json, {});
        });
      }
      return res.blob().then(function (blob) {
        var m = /filename="?([^";]+)"?/i.exec(res.headers.get('Content-Disposition') || '');
        return { blob: blob, namaFile: m ? m[1] : 'laporan.xlsx' };
      });
    }, function () {
      throw ApiError(0, pesanJaringan());
    });
  }

  /** Memicu penyimpanan berkas di browser (satu-satunya bagian yang menyentuh document). */
  function simpanBlob(blob, namaFile) {
    var url = global.URL.createObjectURL(blob);
    var a = global.document.createElement('a');
    a.href = url;
    a.download = namaFile;
    global.document.body.appendChild(a);
    a.click();
    global.document.body.removeChild(a);
    global.setTimeout(function () { global.URL.revokeObjectURL(url); }, 1000);
  }

  SIKESA.api = {
    ApiError: ApiError,
    request: request,
    get: function (p, query) { return request('GET', p, { query: query }); },
    post: function (p, body) { return request('POST', p, { body: body }); },
    put: function (p, body) { return request('PUT', p, { body: body }); },
    del: function (p) { return request('DELETE', p); },
    unduh: unduh,
    simpanBlob: simpanBlob,
    ambilToken: ambilToken,
    simpanToken: simpanToken,
    hapusToken: hapusToken,
    onSesiBerakhir: onSesiBerakhir,
    config: cfg
  };
})(typeof window !== 'undefined' ? window : globalThis);
