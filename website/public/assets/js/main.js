/* main.js - skrip BERSAMA semua halaman: menu mobile + gambar cadangan. Skrip khusus halaman: js/<halaman>.js */
(function () {
    'use strict';

    /* ---- Menu mobile ---- */
    function initNavbar() {
        var toggle = document.getElementById('navToggle');
        var menu = document.getElementById('navMenu');
        var header = document.getElementById('navbar');
        if (!toggle || !menu) { return; }

        function setOpen(open) {
            menu.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        }
        toggle.addEventListener('click', function () { setOpen(!menu.classList.contains('is-open')); });
        menu.addEventListener('click', function (e) { if (e.target.closest('a')) { setOpen(false); } });
        document.addEventListener('click', function (e) {
            if (menu.classList.contains('is-open') && header && !header.contains(e.target)) { setOpen(false); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && menu.classList.contains('is-open')) { setOpen(false); toggle.focus(); }
        });
        window.addEventListener('resize', function () { if (window.innerWidth > 1100) { setOpen(false); } });
    }

    /* ---- Gambar gagal dimuat -> gambar cadangan (tidak pernah tampil ikon rusak) ---- */
    function initImageFallback() {
        var fallback = document.body.getAttribute('data-placeholder');
        if (!fallback) { return; }
        document.addEventListener('error', function (e) {
            var img = e.target;
            if (!img || img.tagName !== 'IMG' || img.getAttribute('data-fallback-done')) { return; }
            img.setAttribute('data-fallback-done', '1');
            img.removeAttribute('srcset');
            img.src = fallback;
        }, true);
        // Gambar yang sudah gagal sebelum skrip berjalan
        Array.prototype.forEach.call(document.images, function (img) {
            if (img.complete && img.naturalWidth === 0 && img.src && !img.getAttribute('data-fallback-done')) {
                img.setAttribute('data-fallback-done', '1');
                img.src = fallback;
            }
        });
    }

    function init() { initNavbar(); initImageFallback(); }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); }
    else { init(); }
})();
