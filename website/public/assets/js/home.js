/* home.js - carousel berita + video profil + latar hero (dipakai Beranda dan Profil). Menu mobile ada di main.js. */
(function () {
    'use strict';

    function initCarousel() {
        var root = document.getElementById('carousel');
        if (!root) { return; }
        var slides = root.querySelectorAll('.carousel__slide');
        var dots = document.querySelectorAll('.carousel__dots button');
        var total = slides.length;
        if (!total) { return; }
        var current = Math.min(1, total - 1);
        var timer = null;

        function render() {
            slides.forEach(function (slide, i) {
                slide.classList.toggle('is-active', i === current);
                slide.classList.toggle('is-prev', total > 1 && i === (current - 1 + total) % total && i !== current);
                slide.classList.toggle('is-next', total > 2 && i === (current + 1) % total && i !== current);
                slide.setAttribute('aria-hidden', i === current ? 'false' : 'true');
                var a = slide.querySelector('a');
                if (a) { a.tabIndex = i === current ? 0 : -1; }
            });
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-on', i === current);
                dot.setAttribute('aria-current', i === current ? 'true' : 'false');
            });
        }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }
        function start() { stop(); if (total > 1) { timer = setInterval(function () { goTo(current + 1, false); }, 5000); } }
        function goTo(index, manual) {
            current = (index + total) % total;
            render();
            if (manual) { start(); }
        }

        var prev = document.getElementById('prevBtn');
        var next = document.getElementById('nextBtn');
        if (prev) { prev.addEventListener('click', function () { goTo(current - 1, true); }); }
        if (next) { next.addEventListener('click', function () { goTo(current + 1, true); }); }
        dots.forEach(function (dot, i) { dot.addEventListener('click', function () { goTo(i, true); }); });

        document.addEventListener('visibilitychange', function () { if (document.hidden) { stop(); } else { start(); } });

        render();
        start();
    }

    /* iframe YouTube baru dimuat saat kotak diklik */
    function initVideo() {
        var box = document.getElementById('videoBox');
        if (!box || !box.dataset.yt) { return; }
        box.addEventListener('click', function () {
            var iframe = document.createElement('iframe');
            iframe.src = 'https://www.youtube.com/embed/' + encodeURIComponent(box.dataset.yt) + '?autoplay=1';
            iframe.allow = 'autoplay; encrypted-media';
            iframe.allowFullscreen = true;
            iframe.title = 'Video profil sekolah';
            box.replaceChildren(iframe);
        }, { once: true });
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

    function init() { initCarousel(); initVideo(); initHeroSlides(); initHeroTyping(); }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); }
    else { init(); }
})();