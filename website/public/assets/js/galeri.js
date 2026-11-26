/* galeri.js - perbesar foto (lightbox) pada halaman Galeri dan galeri di detail berita.
   Foto = tautan <a class="zoom" href="URL-foto" data-caption="keterangan"> yang berisi <img>. Tanpa JS tautan tetap membuka foto. */
(function () {
    'use strict';

    function init() {
        var links = Array.prototype.slice.call(document.querySelectorAll('a.zoom'));
        if (!links.length) { return; }

        var box = document.createElement('div');
        box.className = 'lightbox';
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');
        box.setAttribute('aria-label', 'Foto diperbesar');
        box.hidden = true;
        box.innerHTML =
            '<button type="button" class="lightbox__close" aria-label="Tutup">&times;</button>' +
            '<button type="button" class="lightbox__nav lightbox__nav--prev" aria-label="Foto sebelumnya">&#8249;</button>' +
            '<figure class="lightbox__fig"><img alt=""><figcaption></figcaption></figure>' +
            '<button type="button" class="lightbox__nav lightbox__nav--next" aria-label="Foto berikutnya">&#8250;</button>';
        document.body.appendChild(box);

        var img = box.querySelector('img');
        var cap = box.querySelector('figcaption');
        var closeBtn = box.querySelector('.lightbox__close');
        var prevBtn = box.querySelector('.lightbox__nav--prev');
        var nextBtn = box.querySelector('.lightbox__nav--next');
        var index = 0, opener = null;

        function show(i) {
            index = (i + links.length) % links.length;
            var a = links[index];
            img.src = a.getAttribute('href');
            img.alt = a.getAttribute('data-caption') || '';
            cap.textContent = a.getAttribute('data-caption') || '';
            cap.hidden = !cap.textContent;
            var many = links.length > 1;
            prevBtn.hidden = nextBtn.hidden = !many;
        }
        function open(i, from) {
            opener = from;
            show(i);
            box.hidden = false;
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }
        function close() {
            box.hidden = true;
            img.removeAttribute('src');
            document.body.style.overflow = '';
            if (opener) { opener.focus(); }
        }

        links.forEach(function (a, i) {
            a.addEventListener('click', function (e) { e.preventDefault(); open(i, a); });
        });
        closeBtn.addEventListener('click', close);
        prevBtn.addEventListener('click', function () { show(index - 1); });
        nextBtn.addEventListener('click', function () { show(index + 1); });
        box.addEventListener('click', function (e) { if (e.target === box) { close(); } });
        document.addEventListener('keydown', function (e) {
            if (box.hidden) { return; }
            if (e.key === 'Escape') { close(); }
            else if (e.key === 'ArrowLeft') { show(index - 1); }
            else if (e.key === 'ArrowRight') { show(index + 1); }
            else if (e.key === 'Tab') {   // fokus tidak keluar dari dialog
                var f = [closeBtn, prevBtn, nextBtn].filter(function (b) { return !b.hidden; });
                var pos = f.indexOf(document.activeElement);
                e.preventDefault();
                f[(pos + (e.shiftKey ? -1 : 1) + f.length) % f.length].focus();
            }
        });
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); }
    else { init(); }
})();
