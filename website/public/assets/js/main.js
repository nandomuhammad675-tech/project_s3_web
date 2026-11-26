/* main.js - skrip BERSAMA semua halaman: menu mobile + gambar cadangan + hero (foto berganti, teks mengetik). Skrip khusus halaman: js/<halaman>.js */
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

    /* latar foto hero berganti otomatis (crossfade), tersinkron lewat data-i */
    function initHeroSlides() {
        var root = document.getElementById('heroSlides');
        if (!root) { return; }
        var all = root.querySelectorAll('.hero__slide');
        var total = root.querySelectorAll('.hero__bg .hero__slide').length;
        if (total < 2) { return; }
        var current = 0;
        var timer = null;

        /* gambar ke-2 dst dimuat setelah halaman selesai agar tampilan awal tidak lambat */
        function preload() {
            all.forEach(function (slide) {
                if (slide.dataset.bg) {
                    slide.style.backgroundImage = "url('" + slide.dataset.bg + "')";
                    slide.removeAttribute('data-bg');
                }
            });
        }
        function show(index) {
            current = index % total;
            all.forEach(function (slide) {
                slide.classList.toggle('is-active', Number(slide.dataset.i) === current);
            });
        }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }
        function start() { stop(); timer = setInterval(function () { show(current + 1); }, 6000); }

        if (document.readyState === 'complete') { preload(); }
        else { window.addEventListener('load', preload); }
        document.addEventListener('visibilitychange', function () { if (document.hidden) { stop(); } else { start(); } });
        start();
    }

    /* teks hero diketik huruf demi huruf, ditahan sebentar, dihapus, lalu diketik lagi (berulang).
       Ruang teks sudah dipesan sehingga tata letak tidak bergeser. */
    function initHeroTyping() {
        var box = document.querySelector('#heroSlides .hero__content');
        if (!box) { return; }
        var els = Array.prototype.slice.call(box.querySelectorAll('[data-type]'));
        if (!els.length) { return; }
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }

        var PAUSE_AFTER_TYPED = 4000;  /* jeda setelah semua teks tampil, sebelum dihapus (ms) */
        var PAUSE_AFTER_ERASED = 600;  /* jeda setelah teks habis, sebelum diketik ulang (ms) */
        var ERASE_SPEED = 14;          /* kecepatan menghapus (ms per huruf) */

        var parts = els.map(function (el) {
            var text = el.textContent;
            var on = document.createElement('span');
            var off = document.createElement('span');
            off.style.visibility = 'hidden';
            off.textContent = text;
            el.textContent = '';
            el.appendChild(on);
            el.appendChild(off);
            return { el: el, text: text, on: on, off: off, speed: Number(el.dataset.speed) || 45 };
        });
        box.classList.add('is-typing');

        function setText(part, count) {
            part.on.textContent = part.text.slice(0, count);
            part.off.textContent = part.text.slice(count);
        }
        function caretTo(part) {
            var old = box.querySelector('.typing-caret');
            if (old) { old.parentNode.removeChild(old); }
            var caret = document.createElement('i');
            caret.className = 'typing-caret';
            part.el.insertBefore(caret, part.off);
        }

        function typeLine(k) {
            if (k >= parts.length) {
                box.classList.add('is-done'); /* tombol muncul sekali, tetap tampil di putaran berikutnya */
                setTimeout(function () { eraseLine(parts.length - 1); }, PAUSE_AFTER_TYPED);
                return;
            }
            var part = parts[k];
            var i = 0;
            caretTo(part);
            (function tick() {
                i++;
                setText(part, i);
                if (i < part.text.length) { setTimeout(tick, part.speed); }
                else { setTimeout(function () { typeLine(k + 1); }, 250); }
            })();
        }

        function eraseLine(k) {
            if (k < 0) { setTimeout(function () { typeLine(0); }, PAUSE_AFTER_ERASED); return; }
            var part = parts[k];
            var i = part.text.length;
            caretTo(part);
            (function tick() {
                i--;
                setText(part, i);
                if (i > 0) { setTimeout(tick, ERASE_SPEED); }
                else { eraseLine(k - 1); }
            })();
        }

        setTimeout(function () { typeLine(0); }, 500);
    }

    function init() { initNavbar(); initImageFallback(); initHeroSlides(); initHeroTyping(); }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); }
    else { init(); }
})();