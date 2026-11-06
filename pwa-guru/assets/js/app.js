/*
 * SI-KESA PWA Guru | kerangka halaman: penjaga sesi, profil di sidebar, keluar, toast, utilitas tampilan.
 * Bergantung pada apiClient.js, auth.js, profil.js (dimuat lebih dulu oleh layouts/footer.php).
 * Hak akses sebenarnya dijaga server; penjaga ini hanya kenyamanan (mengarahkan ke login).
 */
(function (global) {
  'use strict';

  var S = global.SIKESA;
  var api = S.api;
  var cfg = global.SIKESA_CONFIG || {};
  var doc = global.document;

  function ke(url) { if (url) global.location.replace(url); }

  /* ---------- sesi ---------- */
  function getUser() {
    try { return JSON.parse(global.localStorage.getItem(cfg.userKey || 'sikesa_guru_user') || 'null'); } catch (e) { return null; }
  }
  function bersihkanSesi() {
    api.hapusToken();
    try { global.localStorage.removeItem(cfg.userKey || 'sikesa_guru_user'); } catch (e) { /* abaikan */ }
  }
  function keLogin(pesan) {
    bersihkanSesi();
    if (pesan) { try { global.sessionStorage.setItem('sikesa_login_msg', pesan); } catch (e) { /* abaikan */ } }
    ke(cfg.loginUrl);
  }

  // apiClient menghapus token pada 401; di sini hapus juga data pengguna dan pesan untuk layar login.
  api.onSesiBerakhir(function (pesan) {
    try { global.localStorage.removeItem(cfg.userKey || 'sikesa_guru_user'); } catch (e) { /* abaikan */ }
    try { global.sessionStorage.setItem('sikesa_login_msg', 'Sesi berakhir, silakan masuk lagi.'); } catch (e) { /* abaikan */ }
  });

  var u0 = getUser();
  if (!S.auth.adalahMasuk() || (u0 && u0.role && u0.role !== 'guru')) {
    keLogin();
    return;
  }

  /* ---------- utilitas tampilan ---------- */
  var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  var HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function dua(n) { return (n < 10 ? '0' : '') + n; }
  function isoDari(d) { return d.getFullYear() + '-' + dua(d.getMonth() + 1) + '-' + dua(d.getDate()); }
  function dariIso(iso) { var p = String(iso).split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
  function tanggalPanjang(iso) { var d = dariIso(iso); return HARI[d.getDay()] + ', ' + d.getDate() + ' ' + BULAN[d.getMonth()] + ' ' + d.getFullYear(); }
  function tanggalPendek(iso) { var d = dariIso(iso); return d.getDate() + ' ' + BULAN[d.getMonth()].slice(0, 3) + ' ' + d.getFullYear(); }
  function cap(s) { s = String(s || ''); return s.charAt(0).toUpperCase() + s.slice(1); }
  function inisial(nama) {
    var p = String(nama || '').trim().split(/\s+/);
    return ((p[0] || '').charAt(0) + (p.length > 1 ? p[p.length - 1].charAt(0) : (p[0] || '').charAt(1))).toUpperCase() || '--';
  }

  function toast(teks, jenis) {
    var stack = doc.getElementById('toast-stack');
    if (!stack) return;
    var el = doc.createElement('div');
    el.className = 'pointer-events-auto px-4 py-3 rounded-xl shadow-lg text-sm font-medium text-white ' +
      (jenis === 'error' ? 'bg-red-600' : 'bg-[#455a2d]');
    el.textContent = teks;
    stack.appendChild(el);
    global.setTimeout(function () { el.remove(); }, 4500);
  }

  /** Teks galat untuk pengguna: pesan server apa adanya (403/409/422), umum untuk jaringan/500. */
  function pesanGalat(err) {
    if (!err) return 'Terjadi gangguan, coba lagi.';
    if (err.status === 0 || err.status >= 500) return err.status === 0 ? err.message : 'Terjadi gangguan, coba lagi.';
    if (err.status === 403) return err.message || 'Anda tidak memiliki akses.';
    return err.message || 'Terjadi gangguan, coba lagi.';
  }

  /** Tandai tombol sibuk selama promise berjalan. */
  function sibuk(tombol, janji, teksSibuk) {
    if (!tombol) return janji;
    var awal = tombol.innerHTML;
    tombol.disabled = true;
    if (teksSibuk) tombol.textContent = teksSibuk;
    function selesai() { tombol.disabled = false; tombol.innerHTML = awal; }
    janji.then(selesai, selesai);
    return janji;
  }

  /* ---------- profil + kelas ---------- */
  var profilData = null;
  var meJanji = S.profil.me().then(function (me) {
    profilData = me;
    var guru = (me && me.guru) || {};
    var kelas = (me && me.kelas) || null;
    setProfil({ nama: guru.nama_guru, peran: kelas ? 'Wali Kelas ' + kelas.nama_kelas : 'Belum menjadi wali kelas' });
    // teks yang memuat nama kelas: <span data-kelas>…</span> atau data-kelas-awalan="Absensi Kelas"
    doc.querySelectorAll('[data-kelas-awalan]').forEach(function (el) {
      el.textContent = el.getAttribute('data-kelas-awalan') + (kelas ? ' ' + kelas.nama_kelas : '');
    });
    var ta = me && me.tahun_ajaran;
    doc.querySelectorAll('[data-ta]').forEach(function (el) {
      el.textContent = ta ? 'Tahun Ajaran ' + ta.nama_tahun + ' \u2022 Semester ' + cap(ta.semester_aktif) : '';
    });
    if (!kelas && global.SIKESA_PAGE && global.SIKESA_PAGE.butuhKelas) tampilTanpaKelas();
    return me;
  }, function (err) {
    if (err && err.status === 401) return null;     // apiClient sudah mengalihkan ke login
    setProfil({ nama: (getUser() || {}).username || 'Guru', peran: '' });
    toast(pesanGalat(err), 'error');
    throw err;
  });

  function setProfil(p) {
    var nama = p.nama || (getUser() || {}).username || 'Guru';
    var set = function (id, t) { var el = doc.getElementById(id); if (el) el.textContent = t; };
    set('profilNama', nama);
    set('profilPeran', p.peran || '\u00a0');
    set('profilInisial', inisial(nama));
    set('topbarInisial', inisial(nama));
  }

  function tampilTanpaKelas() {
    var main = doc.querySelector('main');
    if (!main) return;
    main.innerHTML = '<div class="flex-1 flex flex-col items-center justify-center text-center py-20">' +
      '<i class="ph ph-warning-circle text-5xl text-[#eba048] mb-3"></i>' +
      '<h3 class="text-lg font-bold text-gray-800 mb-1">Belum ditetapkan sebagai wali kelas</h3>' +
      '<p class="text-sm text-gray-500 max-w-md">Akun Anda belum menjadi wali kelas pada tahun ajaran aktif, sehingga menu ini belum dapat dipakai. Hubungi Admin sekolah.</p></div>';
  }

  /* ---------- keluar ---------- */
  doc.addEventListener('click', function (ev) {
    var b = ev.target.closest && ev.target.closest('[data-action="logout"]');
    if (!b) return;
    ev.preventDefault();
    S.auth.keluar().then(function () { keLogin(); });
  });

  /* ---------- ekspor ---------- */
  S.getUser = getUser;
  S.me = meJanji;
  S.dataMe = function () { return profilData; };
  S.layout = { setProfil: setProfil };
  S.ui = {
    esc: esc, dua: dua, isoDari: isoDari, dariIso: dariIso, tanggalPanjang: tanggalPanjang, tanggalPendek: tanggalPendek,
    cap: cap, inisial: inisial, toast: toast, pesanGalat: pesanGalat, sibuk: sibuk, BULAN: BULAN, HARI: HARI
  };
})(window);
