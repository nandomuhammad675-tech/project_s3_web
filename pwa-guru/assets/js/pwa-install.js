/* SI-KESA | pemasangan PWA: tombol "Pasang Aplikasi" muncul bila browser mengizinkan (beforeinstallprompt).
 * Syarat browser: HTTPS (atau localhost), manifest valid, service worker aktif. Tombol: elemen [data-pwa-install]. */
(function () {
  'use strict';
  var tunda = null;
  var tombol = function () { return document.querySelectorAll('[data-pwa-install]'); };
  function tampil(on) { tombol().forEach(function (b) { b.hidden = !on; }); }
  var terpasang = window.matchMedia && window.matchMedia('(display-mode: standalone)').matches;

  window.addEventListener('beforeinstallprompt', function (e) { e.preventDefault(); tunda = e; if (!terpasang) tampil(true); });
  window.addEventListener('appinstalled', function () { tunda = null; tampil(false); });
  document.addEventListener('click', function (e) {
    if (!e.target.closest || !e.target.closest('[data-pwa-install]') || !tunda) return;
    var p = tunda; tunda = null; tampil(false);
    p.prompt(); if (p.userChoice) p.userChoice.catch(function () {});
  });
  document.addEventListener('DOMContentLoaded', function () { tampil(!!tunda && !terpasang); });
})();
