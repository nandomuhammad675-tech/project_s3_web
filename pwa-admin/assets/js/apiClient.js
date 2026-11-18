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

    // Gambar berupa data URL pada body (banner, ikon, gambar mading) diunggah dulu; yang dikirim path-nya.
    if ((method === 'POST' || method === 'PUT') && opts.body && typeof opts.body === 'object' && !opts._materialized) {
      return materialize(path, opts.body).then(function (b) {
        return request(method, path, Object.assign({}, opts, { body: b, _materialized: true }));
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

  /* Folder tujuan gambar di server: aset/<jenis>/<nama>/ (nama berkas mengikuti nama event/guru/dll). */
  var MEDIA_META = [
    { re: /\/admin\/event(\/|$)/,              jenis: 'event',              nama: 'nama_event' },
    { re: /\/admin\/mading(\/|$)/,             jenis: 'mading',             nama: 'judul_karya' },
    { re: /\/admin\/guru(\/|$)/,               jenis: 'guru',               nama: 'nama_guru' },
    { re: /\/admin\/ekstrakurikuler(\/|$)/,    jenis: 'ekstrakurikuler',    nama: 'nama_ekskul' },
    { re: /\/admin\/program-pembiasaan(\/|$)/, jenis: 'program-pembiasaan', nama: 'nama_program' },
    { re: /\/admin\/galeri(\/|$)/,             jenis: 'galeri',             nama: 'keterangan' }
  ];
  function mediaMeta(path, body) {
    for (var i = 0; i < MEDIA_META.length; i++) {
      if (MEDIA_META[i].re.test(path)) return { jenis: MEDIA_META[i].jenis, nama: String(body[MEDIA_META[i].nama] || '') };
    }
    return { jenis: 'galeri', nama: '' };
  }

  function materialize(path, body) {
    var re = /^data:image\/(png|jpe?g|webp);base64,/i;
    var keys = Object.keys(body).filter(function (k) { return typeof body[k] === 'string' && re.test(body[k]); });
    if (!keys.length) return Promise.resolve(body);
    var meta = mediaMeta(path, body);
    var copy = Object.assign({}, body);
    return Promise.all(keys.map(function (k) {
      return fetch(body[k]).then(function (r) { return r.blob(); }).then(function (blob) {
        var ext = blob.type === 'image/png' ? 'png' : blob.type === 'image/webp' ? 'webp' : 'jpg';
        return upload(new File([blob], 'gambar.' + ext, { type: blob.type }), meta);
      }).then(function (p) { copy[k] = p; });
    })).then(function () { return copy; });
  }

  /** Unggah gambar (multipart: file, jenis, nama) => path "aset/<jenis>/<nama>/<berkas>.jpg". */
  function upload(file, meta) {
    var token = localStorage.getItem(TOKEN_KEY);
    var fd = new FormData();
    fd.append('file', file);
    if (meta && meta.jenis) fd.append('jenis', meta.jenis);
    if (meta && meta.nama) fd.append('nama', meta.nama);
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
    var p = String(path).replace(/^\//, '');
    var base = (window.SIKESA && window.SIKESA.uploadBase) || '';
    // gambar hasil unggahan (assets/img/...) dilayani dari database lewat backend
    if (/^assets\/img\//.test(p)) return base + '/api/media?path=' + encodeURIComponent(p);
    return base + '/' + p;
  }

  /** Unduh berkas (XLSX) dengan token di header (bukan di URL). => { blob, name }. Galat JSON dilempar sebagai ApiError. */
  function download(path, query) {
    var token = localStorage.getItem(TOKEN_KEY);
    var headers = { Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json' };
    if (token) headers.Authorization = 'Bearer ' + token;
    return fetch(buildUrl(path, query), { method: 'GET', headers: headers })
      .catch(function () { throw new ApiError(0, 'Terjadi gangguan, coba lagi'); })
      .then(function (res) {
        var ct = res.headers.get('Content-Type') || '';
        if (res.ok && !/json/i.test(ct)) {
          return res.blob().then(function (blob) {
            var m = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(res.headers.get('Content-Disposition') || '');
            return { blob: blob, name: m ? decodeURIComponent(m[1]) : 'data.xlsx' };
          });
        }
        return res.json().catch(function () { return null; }).then(function (body) {
          if (res.status === 401) { if (window.SikesaAuth) window.SikesaAuth.expire(); throw new ApiError(401, 'Sesi berakhir, silakan masuk lagi', body); }
          if (res.status === 403) throw new ApiError(403, 'Anda tidak memiliki akses ke halaman ini', body);
          if (res.status === 404 || res.status === 409 || res.status === 422 || res.status === 400) throw new ApiError(res.status, serverMessage(body, 'Data tidak dapat diekspor'), body);
          throw new ApiError(res.status || 500, 'Terjadi gangguan, coba lagi', body);
        });
      });
  }
  function saveBlob(blob, name) {
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = name;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(a.href); }, 1500);
  }

  window.SikesaApi = {
    download: download,
    saveBlob: saveBlob,
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