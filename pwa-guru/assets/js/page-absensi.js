/*
 * SI-KESA PWA Guru | halaman Absensi. Memakai SIKESA.absensi (data) dan SIKESA.ui (tampilan).
 * Alur: lihat(tanggal) -> Guru memilih status -> simpan(tanggal, awal, pilihan) = satu POST untuk siswa baru + PUT per koreksi.
 */
(function () {
  'use strict';
  var S = window.SIKESA, A = S.absensi, U = S.ui;
  var $ = function (id) { return document.getElementById(id); };

  var KODE = { hadir: 'H', sakit: 'S', izin: 'I', alpa: 'A' };
  var WARNA = {
    hadir: 'peer-checked:bg-green-600 peer-checked:border-green-600',
    sakit: 'peer-checked:bg-[#eba048] peer-checked:border-[#eba048]',
    izin: 'peer-checked:bg-blue-600 peer-checked:border-blue-600',
    alpa: 'peer-checked:bg-red-600 peer-checked:border-red-600'
  };

  var st = { tanggal: A.hariIni(), awal: [], pilihan: {}, statusKelas: 'belum', memuat: false, kunci: false };

  function kunciInput() { return st.kunci || st.statusKelas === 'libur' || st.memuat; }

  function banner(teks, jenis) {
    var b = $('bannerStatus');
    if (!teks) { b.hidden = true; return; }
    b.hidden = false;
    b.textContent = teks;
    b.className = 'mt-3 rounded-xl px-4 py-3 text-sm font-medium ' +
      (jenis === 'libur' ? 'bg-gray-100 text-gray-600' : jenis === 'ok' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700');
  }

  function labelTanggal() {
    $('tanggalLabel').textContent = U.tanggalPanjang(st.tanggal);
    var h = A.hariIni();
    $('tanggalSub').textContent = st.tanggal === h ? 'Hari ini' : (st.tanggal < h ? 'Tanggal sebelumnya' : 'Tanggal mendatang');
    $('inputTanggal').value = st.tanggal;
    $('inputTanggal').max = h;
    $('btnNext').disabled = st.tanggal >= h;
  }

  function hitung() {
    var r = A.ringkasan(st.pilihan, st.awal.length);
    $('cH').textContent = r.hadir; $('cS').textContent = r.sakit; $('cI').textContent = r.izin; $('cA').textContent = r.alpa;
    $('cB').textContent = r.belumDiisi;
    var mati = kunciInput() || st.awal.length === 0;
    $('btnSimpan').disabled = mati;
    $('btnSemuaHadir').disabled = mati;
  }

  function barisHtml(r, idx) {
    var terpilih = st.pilihan[r.id_siswa] || '';
    var opsi = A.STATUS.map(function (s) {
      return '<label class="cursor-pointer">' +
        '<input type="radio" name="status_' + r.id_siswa + '" value="' + s + '" data-siswa="' + r.id_siswa + '" class="peer sr-only"' +
        (terpilih === s ? ' checked' : '') + (kunciInput() ? ' disabled' : '') + '>' +
        '<div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-sm font-bold text-gray-500 peer-checked:text-white ' + WARNA[s] + ' hover:bg-gray-50 transition">' + KODE[s] + '</div>' +
        '</label>';
    }).join('');
    var no = r.nomor_absen != null ? U.dua(r.nomor_absen) : String(idx + 1);
    return '<div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 flex items-center justify-between">' +
      '<div class="flex items-center gap-4"><div class="w-8 h-8 rounded-lg bg-[#f7f6f0] flex items-center justify-center text-xs font-bold text-[#455a2d]">' + U.esc(no) + '</div>' +
      '<p class="font-semibold text-gray-800 text-sm">' + U.esc(r.nama_siswa) + '</p></div>' +
      '<div class="flex gap-2">' + opsi + '</div></div>';
  }

  function render() {
    var el = $('daftarSiswa');
    if (!st.awal.length) {
      el.innerHTML = '<div class="bg-white rounded-xl p-10 text-center text-sm text-gray-500 border border-gray-100">Belum ada siswa aktif pada kelas ini.</div>';
    } else {
      el.innerHTML = st.awal.map(barisHtml).join('');
    }
    hitung();
  }

  function muat() {
    st.memuat = true;
    labelTanggal();
    banner('');
    $('daftarSiswa').innerHTML = '<div class="skel h-14"></div><div class="skel h-14"></div><div class="skel h-14"></div>';
    hitung();
    return A.lihat(st.tanggal).then(function (d) {
      st.awal = d.items || [];
      st.statusKelas = d.status_kelas;
      st.pilihan = {};
      st.awal.forEach(function (r) { if (r.status) st.pilihan[r.id_siswa] = r.status; });
      if (d.status_kelas === 'libur') banner(A.adalahMinggu(st.tanggal) ? 'Hari Minggu: tidak ada absensi.' : 'Hari libur: absensi tidak diproses pada tanggal ini.', 'libur');
      else if (d.status_kelas === 'sudah') banner('Absensi tanggal ini sudah tercatat. Anda dapat memperbaiki statusnya.', 'ok');
      st.memuat = false;
      render();
    }, function (err) {
      st.memuat = false; st.awal = [];
      $('daftarSiswa').innerHTML = '<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-5 text-sm flex items-center justify-between gap-4"><span>' + U.esc(U.pesanGalat(err)) + '</span><button type="button" id="btnUlang" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold">Coba lagi</button></div>';
      var u = $('btnUlang'); if (u) u.addEventListener('click', muat);
      hitung();
    });
  }

  function geser(hari) {
    var d = U.dariIso(st.tanggal);
    d.setDate(d.getDate() + hari);
    var iso = U.isoDari(d);
    if (iso > A.hariIni()) return;
    st.tanggal = iso;
    muat();
  }

  $('btnPrev').addEventListener('click', function () { geser(-1); });
  $('btnNext').addEventListener('click', function () { geser(1); });
  $('inputTanggal').addEventListener('change', function (e) {
    var v = e.target.value;
    if (/^\d{4}-\d{2}-\d{2}$/.test(v) && v <= A.hariIni()) { st.tanggal = v; muat(); } else { labelTanggal(); }
  });

  $('daftarSiswa').addEventListener('change', function (e) {
    var t = e.target;
    if (t && t.matches('input[type=radio][data-siswa]')) { st.pilihan[t.getAttribute('data-siswa')] = t.value; hitung(); }
  });

  $('btnSemuaHadir').addEventListener('click', function () {
    if (kunciInput()) return;
    st.pilihan = A.semuaHadir(st.awal);
    render();
  });

  $('btnSimpan').addEventListener('click', function () {
    if (kunciInput()) return;
    var belum = A.ringkasan(st.pilihan, st.awal.length).belumDiisi;
    if (belum > 0) { U.toast('Masih ada ' + belum + ' siswa yang belum diisi statusnya.', 'error'); return; }
    st.kunci = true; hitung();
    A.simpan(st.tanggal, st.awal, st.pilihan).then(function (h) {
      var teks = [];
      if (h.baru) teks.push(h.baru + ' tercatat');
      if (h.diperbarui) teks.push(h.diperbarui + ' diperbarui');
      if (!h.baru && !h.diperbarui && !h.gagal.length) teks.push('tidak ada perubahan');
      if (h.gagal.length) U.toast(h.gagal.length + ' perbaikan gagal: ' + h.gagal[0].pesan, 'error');
      else U.toast('Absensi disimpan (' + teks.join(', ') + ').');
    }, function (err) {
      U.toast(U.pesanGalat(err), 'error');
    }).then(function () { st.kunci = false; return muat(); });
  });

  // Muat setelah profil/kelas diketahui; tanpa kelas, app.js sudah mengganti isi halaman.
  S.me.then(function (me) { if (me && me.kelas) return muat(); }, function () { /* sudah ditampilkan app.js */ });
})();
