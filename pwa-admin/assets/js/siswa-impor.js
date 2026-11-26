/* SI-KESA PWA Admin | Impor Data Siswa dari Excel.
 * Berkas dibaca di browser (SheetJS), divalidasi, lalu dikirim ke POST /api/admin/siswa/impor (maks 500 baris per kirim). */
(function () {
  'use strict';
  var Api = window.SikesaApi;
  var $ = function (id) { return document.getElementById(id); };
  var dlg = $('dlg-impor');
  if (!Api || !dlg) return;

  var rows = [];       // { no, nisn, nama, wali, hp, galat }
  var sibuk = false;

  function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function pesan(t) { var m = $('im-msg'); m.hidden = !t; m.textContent = t || ''; }
  function toast(t, jenis) { if (window.SikesaUI) window.SikesaUI.toast(t, jenis); }
  function perluLib() {
    if (window.XLSX) return true;
    pesan('Pustaka Excel belum termuat (perlu internet saat pertama kali). Muat ulang halaman lalu coba lagi.');
    return false;
  }

  function reset() {
    rows = []; $('im-file').value = ''; pesan(''); $('im-preview').hidden = true; $('im-body').innerHTML = '';
    $('im-kirim').disabled = true; $('im-kirim').textContent = 'Impor';
  }

  /* ---------- contoh tabel ---------- */
  function unduhTemplate() {
    if (!perluLib()) return;
    var data = [
      ['NISN', 'Nama Siswa', 'Nama Wali', 'No. HP Wali'],
      ['0123456789', 'Aditya Pratama', 'Budi Santoso', '081234567890'],
      ['0123456790', 'Siti Nurhaliza', 'Ahmad Fauzi', '081298765432'],
      ['0123456791', 'Rafi Maulana', '', '']
    ];
    var ws = XLSX.utils.aoa_to_sheet(data);
    // NISN dan No. HP harus teks agar angka 0 di depan tidak hilang
    ['A2', 'A3', 'A4', 'D2', 'D3'].forEach(function (a) { if (ws[a]) { ws[a].t = 's'; ws[a].z = '@'; } });
    ws['!cols'] = [{ wch: 14 }, { wch: 28 }, { wch: 24 }, { wch: 16 }];
    var info = XLSX.utils.aoa_to_sheet([
      ['PETUNJUK IMPOR DATA SISWA'], [''],
      ['1. Isi sheet "Data Siswa". Baris pertama (judul kolom) jangan diubah.'],
      ['2. NISN wajib 10 digit angka dan unik (tidak boleh sama dengan siswa yang sudah ada).'],
      ['3. Nama Siswa wajib diisi (maksimal 100 karakter).'],
      ['4. Nama Wali dan No. HP Wali boleh dikosongkan. No. HP hanya angka, maksimal 15 digit.'],
      ['5. Hapus 3 baris contoh sebelum mengisi data sebenarnya.'],
      ['6. Maksimal 500 baris per berkas. Siswa yang diimpor berstatus Baru.'],
      ['7. Format kolom NISN dan No. HP sebaiknya Teks agar angka 0 di depan tidak hilang.']
    ]);
    info['!cols'] = [{ wch: 90 }];
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Data Siswa');
    XLSX.utils.book_append_sheet(wb, info, 'Petunjuk');
    XLSX.writeFile(wb, 'contoh-impor-siswa.xlsx');
  }

  /* ---------- baca + validasi ---------- */
  var ALIAS = {
    nisn: ['nisn', 'nisn resmi', 'nomor induk siswa nasional'],
    nama: ['nama siswa', 'nama', 'nama lengkap'],
    wali: ['nama wali', 'wali', 'nama orang tua'],
    hp: ['no. hp wali', 'no hp wali', 'no hp', 'nomor hp wali', 'hp wali', 'telepon wali']
  };
  function norm(h) { return String(h || '').toLowerCase().replace(/\s+/g, ' ').trim(); }

  function proses(aoa) {
    var h = -1, idx = {};
    for (var i = 0; i < Math.min(aoa.length, 10); i++) {
      var tmp = {};
      aoa[i].forEach(function (c, j) { var n = norm(c); Object.keys(ALIAS).forEach(function (k) { if (ALIAS[k].indexOf(n) > -1) tmp[k] = j; }); });
      if (tmp.nisn !== undefined && tmp.nama !== undefined) { h = i; idx = tmp; break; }
    }
    if (h < 0) throw new Error('Judul kolom tidak ditemukan. Gunakan judul "NISN" dan "Nama Siswa" pada baris pertama (unduh contoh tabel).');
    var out = [], lihat = {};
    for (var r = h + 1; r < aoa.length; r++) {
      var b = aoa[r];
      var nisn = String(b[idx.nisn] == null ? '' : b[idx.nisn]).replace(/\s+/g, '');
      var nama = String(b[idx.nama] == null ? '' : b[idx.nama]).trim();
      var wali = idx.wali === undefined ? '' : String(b[idx.wali] == null ? '' : b[idx.wali]).trim();
      var hp = idx.hp === undefined ? '' : String(b[idx.hp] == null ? '' : b[idx.hp]).replace(/[\s-]+/g, '');
      if (!nisn && !nama && !wali && !hp) continue;                       // baris kosong
      if (/^\d{1,9}$/.test(nisn)) nisn = ('0000000000' + nisn).slice(-10); // Excel membuang angka 0 di depan
      var g = [];
      if (!/^\d{10}$/.test(nisn)) g.push('NISN harus 10 digit angka');
      if (!nama) g.push('Nama kosong'); else if (nama.length > 100) g.push('Nama > 100 karakter');
      if (hp && !/^\d{8,15}$/.test(hp)) g.push('No. HP tidak valid');
      if (nisn && lihat[nisn]) g.push('NISN ganda dengan baris ' + lihat[nisn]); else if (nisn) lihat[nisn] = r + 1;
      out.push({ no: r + 1, nisn: nisn, nama: nama, wali: wali, hp: hp, galat: g.join('; ') });
    }
    return out;
  }

  function tampil() {
    var ok = rows.filter(function (r) { return !r.galat; }).length;
    var bad = rows.length - ok;
    $('im-ringkas').textContent = rows.length + ' baris terbaca: ' + ok + ' siap diimpor' + (bad ? ', ' + bad + ' bermasalah (dilewati)' : '') + '.';
    $('im-body').innerHTML = rows.slice(0, 200).map(function (r) {
      return '<tr' + (r.galat ? ' style="background:#fdecea"' : '') + '><td>' + r.no + '</td><td>' + esc(r.nisn) + '</td><td>' + esc(r.nama) + '</td><td>' + esc(r.wali) + '</td><td>' + esc(r.hp) + '</td><td style="color:#a32d20">' + esc(r.galat || 'OK') + '</td></tr>';
    }).join('') + (rows.length > 200 ? '<tr><td colspan="6">... dan ' + (rows.length - 200) + ' baris lainnya</td></tr>' : '');
    $('im-preview').hidden = false;
    $('im-kirim').disabled = !ok;
    $('im-kirim').textContent = ok ? 'Impor ' + ok + ' Siswa' : 'Impor';
  }

  $('im-file').addEventListener('change', function (e) {
    pesan(''); $('im-preview').hidden = true; $('im-kirim').disabled = true; rows = [];
    var f = e.target.files && e.target.files[0];
    if (!f || !perluLib()) return;
    var fr = new FileReader();
    fr.onload = function () {
      try {
        var wb = XLSX.read(fr.result, { type: 'array' });
        var ws = wb.Sheets[wb.SheetNames[0]];
        var aoa = XLSX.utils.sheet_to_json(ws, { header: 1, raw: false, defval: '' });
        rows = proses(aoa);
        if (!rows.length) { pesan('Tidak ada baris data pada berkas.'); return; }
        if (rows.filter(function (r) { return !r.galat; }).length > 500) { pesan('Maksimal 500 baris valid per berkas. Pecah berkas menjadi beberapa bagian.'); rows = []; return; }
        tampil();
      } catch (err) { pesan(err.message || 'Berkas tidak dapat dibaca. Pastikan berformat .xlsx.'); }
    };
    fr.onerror = function () { pesan('Berkas tidak dapat dibaca.'); };
    fr.readAsArrayBuffer(f);
  });

  /* ---------- kirim ---------- */
  $('im-kirim').addEventListener('click', function () {
    if (sibuk) return;
    var valid = rows.filter(function (r) { return !r.galat; });
    if (!valid.length) return;
    sibuk = true; $('im-kirim').disabled = true; $('im-kirim').textContent = 'Mengimpor...'; pesan('');
    var payload = valid.map(function (r) { return { nisn_resmi: r.nisn, nama_siswa: r.nama, nama_wali: r.wali, no_hp_wali: r.hp }; });
    Api.post('/api/admin/siswa/impor', { rows: payload }).then(function (res) {
      var d = (res && res.data) || {};
      // tandai hasil per baris pada pratinjau
      (d.hasil || []).forEach(function (h) { var r = valid[h.baris - 1]; if (r && h.status === 'gagal') r.galat = h.pesan; else if (r) r.galat = ''; r && (r.selesai = h.status); });
      var gagal = (d.hasil || []).filter(function (h) { return h.status === 'gagal'; }).length;
      toast(d.berhasil + ' siswa berhasil diimpor' + (gagal ? ', ' + gagal + ' gagal.' : '.'), gagal && !d.berhasil ? 'error' : undefined);
      document.dispatchEvent(new CustomEvent('sikesa:siswa-diimpor'));
      if (gagal) {
        rows = rows.filter(function (r) { return r.galat; });            // sisakan baris yang gagal agar bisa diperbaiki
        tampil(); pesan(gagal + ' baris gagal diimpor (lihat keterangan). Perbaiki pada berkas lalu impor ulang baris tersebut.');
        $('im-kirim').disabled = true;
      } else { dlg.close(); reset(); }
    }, function (err) {
      if (err && err.status === 401) return;
      pesan((err && err.message) || 'Impor gagal. Coba lagi.');
      $('im-kirim').disabled = false; $('im-kirim').textContent = 'Impor ' + valid.length + ' Siswa';
    }).then(function () { sibuk = false; });
  });

  $('im-template').addEventListener('click', unduhTemplate);
  $('im-tutup').addEventListener('click', function () { if (!sibuk) { dlg.close(); reset(); } });
  dlg.addEventListener('cancel', function (e) { if (sibuk) e.preventDefault(); });

  window.SikesaImporSiswa = { buka: function () { reset(); if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open', ''); } };
})();
