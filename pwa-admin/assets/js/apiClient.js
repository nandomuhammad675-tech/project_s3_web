/* SI-KESA PWA Admin | klien API
 * Aturan (dokumen 01 bagian 1.1):
 *  401 -> hapus token, ke login ("Sesi berakhir, silakan masuk lagi")
 *  403 -> "Anda tidak memiliki akses ke halaman ini"
 *  409/422 -> pesan dari server apa adanya
 *  500/jaringan -> "Terjadi gangguan, coba lagi"
 * Token dikirim lewat header Authorization, tidak pernah lewat URL. */
(function () {
  'use strict';

  var TOKEN_KEY = 'sikesa_admin_token';
  var USER_KEY = 'sikesa_admin_user';
  var DEMO_KEY = 'sikesa_demo';

  function ApiError(status, message, body) {
    this.name = 'ApiError';
    this.status = status;
    this.message = message;
    this.body = body || null;
  }
  ApiError.prototype = Object.create(Error.prototype);

  var demoPromise = null;
  function ensureDemo() {
    if (window.SikesaDemo) return Promise.resolve();
    if (!demoPromise) {
      demoPromise = new Promise(function (resolve, reject) {
        var s = document.createElement('script');
        s.src = (window.SIKESA.base || '') + '/assets/js/demo-data.js';
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
      });
    }
    return demoPromise;
  }

  function serverMessage(body, fallback) {
    if (!body || typeof body !== 'object') return fallback;
    if (typeof body.message === 'string' && body.message) return body.message;
    if (typeof body.error === 'string' && body.error) return body.error;
    if (body.errors && typeof body.errors === 'object') {
      var first = Object.values(body.errors)[0];
      if (Array.isArray(first)) first = first[0];
      if (typeof first === 'string') return first;
    }
    return fallback;
  }

  function buildUrl(path, query) {
    var base = (window.SIKESA.apiBase || '/api').replace(/\/$/, '');
    // Endpoint dituliskan lengkap diawali /api/...; apiBase dipakai bila API di domain lain.
    var p = path.indexOf('/api/') === 0 ? path.slice(4) : path;
    var url = base + p;
    if (query) {
      var qs = Object.keys(query)
        .filter(function (k) { return query[k] !== undefined && query[k] !== null && query[k] !== ''; })
        .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(query[k]); })
        .join('&');
      if (qs) url += (url.indexOf('?') > -1 ? '&' : '?') + qs;
    }
    return url;
  }

  function request(method, path, opts) {
    opts = opts || {};

    if (sessionStorage.getItem(DEMO_KEY)) {
      return ensureDemo().then(function () {
        return window.SikesaDemo.handle(method, path, opts.query || {}, opts.body || null);
      });
    }

    var headers = { Accept: 'application/json' };
    var token = localStorage.getItem(TOKEN_KEY);
    if (token) headers.Authorization = 'Bearer ' + token;
    if (opts.body !== undefined && opts.body !== null) headers['Content-Type'] = 'application/json';

    // Daftar berhalaman backend: {data:{items,page,per_page,total}}. Minta halaman besar agar modul
    // (yang mengikuti res.meta.last_page) tidak berhenti di halaman pertama.
    var query = opts.query;
    if (method === 'GET' && !(query && query.per_page !== undefined) && !/\/(me|\d+)$/.test(path) && opts.paged !== false) {
      query = Object.assign({ per_page: 100 }, query || {});
    }

    return fetch(buildUrl(path, query), {
      method: method,
      headers: headers,
      body: opts.body !== undefined && opts.body !== null ? JSON.stringify(opts.body) : undefined,
      credentials: 'same-origin'
    }).catch(function () {
      throw new ApiError(0, 'Terjadi gangguan, coba lagi');
    }).then(function (res) {
      return res.text().then(function (txt) {
        var body = null;
        try { body = txt ? JSON.parse(txt) : null; } catch (e) { body = null; }

        if (res.ok) {
          // Selaraskan dengan modul: sediakan meta.last_page dari data.total / data.per_page
          var d = body && body.data;
          if (d && !Array.isArray(d) && Array.isArray(d.items) && d.per_page && !body.meta) {
            body.meta = { page: d.page, per_page: d.per_page, total: d.total, last_page: Math.max(1, Math.ceil(d.total / d.per_page)) };
          }
          return body;
        }

        if (res.status === 401 && !opts.noAuthRedirect) {
          if (window.SikesaAuth) window.SikesaAuth.expire();
          throw new ApiError(401, 'Sesi berakhir, silakan masuk lagi', body);
        }
        if (res.status === 401) throw new ApiError(401, serverMessage(body, 'Username atau password salah'), body);
        if (res.status === 403) throw new ApiError(403, 'Anda tidak memiliki akses ke halaman ini', body);
        if (res.status === 400) throw new ApiError(400, serverMessage(body, 'Permintaan tidak valid'), body);
        if (res.status === 409 || res.status === 422) {
          throw new ApiError(res.status, serverMessage(body, 'Permintaan tidak dapat diproses'), body);
        }
        if (res.status === 404) throw new ApiError(404, serverMessage(body, 'Data tidak ditemukan'), body);
        throw new ApiError(res.status, 'Terjadi gangguan, coba lagi', body);
      });
    });
  }

  /** Unggah gambar (multipart, field "file") => path "uploads/<acak>.png". Dipakai kolom foto/ikon/banner. */
  function upload(file) {
    var token = localStorage.getItem(TOKEN_KEY);
    var fd = new FormData();
    fd.append('file', file);
    var headers = { Accept: 'application/json' };
    if (token) headers.Authorization = 'Bearer ' + token;
    return fetch(buildUrl(window.SIKESA.endpoints.upload || '/api/admin/upload'), { method: 'POST', headers: headers, body: fd })
      .catch(function () { throw new ApiError(0, 'Terjadi gangguan, coba lagi'); })
      .then(function (res) {
        return res.json().catch(function () { return null; }).then(function (body) {
          if (res.ok) return (body && body.data && body.data.path) || null;
          if (res.status === 401) { if (window.SikesaAuth) window.SikesaAuth.expire(); throw new ApiError(401, 'Sesi berakhir, silakan masuk lagi', body); }
          throw new ApiError(res.status, res.status === 422 ? serverMessage(body, 'Berkas tidak valid') : 'Terjadi gangguan, coba lagi', body);
        });
      });
  }

  /** URL tampil untuk path gambar dari server (path relatif => {uploadBase}/{path}; URL https dibiarkan). */
  function fileUrl(path) {
    if (!path) return '';
    if (/^(https?:|data:)/i.test(path)) return path;
    return ((window.SIKESA && window.SIKESA.uploadBase) || '') + '/' + String(path).replace(/^\//, '');
  }

  window.SikesaApi = {
    upload: upload,
    fileUrl: fileUrl,
    ApiError: ApiError,
    TOKEN_KEY: TOKEN_KEY,
    USER_KEY: USER_KEY,
    DEMO_KEY: DEMO_KEY,
    get: function (path, query, opts) { return request('GET', path, Object.assign({ query: query }, opts)); },
    post: function (path, body, opts) { return request('POST', path, Object.assign({ body: body }, opts)); },
    put: function (path, body, opts) { return request('PUT', path, Object.assign({ body: body }, opts)); },
    del: function (path, opts) { return request('DELETE', path, opts); }
  };
})();