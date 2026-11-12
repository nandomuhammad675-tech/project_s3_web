/* SI-KESA PWA Guru | halaman Ganti Password (PUT /api/auth/password; token baru menggantikan yang lama). */
(function () {
  'use strict';
  var S = window.SIKESA, U = S.ui;
  var $ = function (id) { return document.getElementById(id); };

  function alertBox(t, jenis) {
    var a = $('pwAlert'); a.hidden = !t; a.textContent = t || '';
    a.className = 'rounded-xl px-4 py-3 text-sm font-medium mb-5 ' + (jenis === 'ok' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700');
  }
  function galat(peta) {
    document.querySelectorAll('#formPassword [data-err]').forEach(function (p) {
      var m = peta && peta[p.getAttribute('data-err')]; p.hidden = !m; p.textContent = m || '';
    });
  }

  $('formPassword').addEventListener('submit', function (e) {
    e.preventDefault();
    galat(null); alertBox('');
    var btn = $('pwSimpan'); btn.disabled = true;
    S.profil.gantiPassword($('pwLama').value, $('pwBaru').value, $('pwUlang').value).then(function () {
      $('formPassword').reset();
      alertBox('Password berhasil diganti.', 'ok');
    }, function (err) {
      var peta = (err && err.errors) || {}; galat(peta);
      alertBox(Object.keys(peta).length && err.status === 422 ? '' : U.pesanGalat(err));
    }).then(function () { btn.disabled = false; });
  });
})();
