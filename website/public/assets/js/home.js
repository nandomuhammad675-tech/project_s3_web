/* home.js - carousel berita + video profil (dipakai Beranda dan Profil). Menu mobile ada di main.js. */
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

    function init() { initCarousel(); initVideo(); }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); }
    else { init(); }
})();
