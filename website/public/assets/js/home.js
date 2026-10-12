/* home.js - carousel berita + video profil (dipakai Beranda dan Profil). Menu mobile ada di main.js. */
(function () {
    function initCarousel() {
        var slides = document.querySelectorAll('.carousel__slide');
        var dots = document.querySelectorAll('.carousel__dots button');
        var total = slides.length;
        if (!total) { return; }
        var current = Math.min(1, total - 1);
        var timer;

        function render() {
            slides.forEach(function (slide, i) {
                slide.classList.toggle('is-active', i === current);
                slide.classList.toggle('is-prev', i === (current - 1 + total) % total && i !== current);
                slide.classList.toggle('is-next', i === (current + 1) % total && i !== current);
            });
            dots.forEach(function (dot, i) { dot.classList.toggle('is-on', i === current); });
        }
        function restartAutoplay() {
            clearInterval(timer);
            timer = setInterval(function () { goTo(current + 1); }, 5000);
        }
        function goTo(index) {
            current = (index + total) % total;
            render();
            restartAutoplay();
        }

        var prev = document.getElementById('prevBtn');
        var next = document.getElementById('nextBtn');
        if (prev) { prev.addEventListener('click', function () { goTo(current - 1); }); }
        if (next) { next.addEventListener('click', function () { goTo(current + 1); }); }
        dots.forEach(function (dot, i) { dot.addEventListener('click', function () { goTo(i); }); });

        render();
        restartAutoplay();
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
