/**
 * apiClient.js - klien API + sesi untuk PWA Guru SI-KESA
 * Memakai kunci localStorage yang sama dengan login: sikesa_token, sikesa_user.
 *   SIKESA.api.get('/guru/dashboard')  -> data (isi "data" jika ada)
 * Gagal -> melempar ApiError { status, message }. Status 401 otomatis keluar ke login.
 */
(function (w) {
    'use strict';

    var CFG = w.SIKESA_CONFIG || {};
    var TOKEN_KEY = 'sikesa_token';
    var USER_KEY = 'sikesa_user';

    function getToken() { try { return localStorage.getItem(TOKEN_KEY); } catch (e) { return null; } }
    function getUser()  { try { return JSON.parse(localStorage.getItem(USER_KEY) || 'null'); } catch (e) { return null; } }
    function clearSession() {
        try { localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(USER_KEY); } catch (e) { /* diabaikan */ }
    }
    function logout() {
        clearSession();
        w.location.replace(CFG.loginUrl || 'index.php?page=login');
    }

    function ApiError(status, message) {
        this.name = 'ApiError';
        this.status = status;
        this.message = message;
    }
    ApiError.prototype = Object.create(Error.prototype);

    function request(method, path, opts) {
        opts = opts || {};
        var url = (CFG.apiBase || '') + path;
        if (opts.query) {
            var qs = new URLSearchParams(opts.query).toString();
            if (qs) { url += (url.indexOf('?') > -1 ? '&' : '?') + qs; }
        }

        var headers = { 'Accept': 'application/json' };
        var token = getToken();
        if (token) { headers['Authorization'] = 'Bearer ' + token; }
        if (opts.body !== undefined) { headers['Content-Type'] = 'application/json'; }

        var ctrl = new AbortController();
        var timer = setTimeout(function () { ctrl.abort(); }, opts.timeout || 15000);

        return fetch(url, {
            method: method,
            headers: headers,
            body: opts.body !== undefined ? JSON.stringify(opts.body) : undefined,
            signal: ctrl.signal
        }).then(function (res) {
            clearTimeout(timer);
            return res.json().catch(function () { return {}; }).then(function (body) {
                if (res.status === 401) { logout(); throw new ApiError(401, 'Sesi berakhir. Silakan masuk lagi.'); }
                if (!res.ok) {
                    throw new ApiError(res.status, (body && body.message) || 'Terjadi kesalahan pada server.');
                }
                return (body && body.data !== undefined) ? body.data : body;
            });
        }, function (err) {
            clearTimeout(timer);
            throw new ApiError(0, err && err.name === 'AbortError'
                ? 'Server tidak merespons. Coba lagi.'
                : 'Tidak bisa terhubung ke server. Periksa koneksi internet Anda.');
        });
    }

    w.SIKESA = w.SIKESA || {};
    w.SIKESA.getToken = getToken;
    w.SIKESA.getUser = getUser;
    w.SIKESA.logout = logout;
    w.SIKESA.ApiError = ApiError;
    w.SIKESA.api = {
        get:  function (path, query) { return request('GET', path, { query: query }); },
        post: function (path, body)  { return request('POST', path, { body: body || {} }); },
        put:  function (path, body)  { return request('PUT', path, { body: body || {} }); },
        del:  function (path)        { return request('DELETE', path); }
    };
})(window);