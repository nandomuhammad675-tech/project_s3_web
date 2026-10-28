/* main.js - skrip BERSAMA semua halaman (menu mobile). Skrip khusus halaman: assets/js/<halaman>.js */
(function () {
    function initNavbar() {
        var toggle = document.getElementById('navToggle');
        var menu = document.getElementById('navMenu');
        if (!toggle || !menu) { return; }
        toggle.addEventListener('click', function () {
            var open = menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initNavbar); }
    else { initNavbar(); }
})();
