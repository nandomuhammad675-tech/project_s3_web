/**
 * dashboard.js - Beranda Guru. Memanggil GET {apiBase}/guru/dashboard. Balasan yang dibaca (semua opsional):
 * { guru:{nama_guru,jabatan,sapaan}, tahun_ajaran:{nama_tahun,semester_aktif},
 *   kelas:{id_kelas,nama_kelas,jumlah_siswa}|null,
 *   absensi_hari_ini:{sudah_absen,ringkasan:{hadir,sakit,izin,alpa}}, libur:{keterangan}|null }
 */
(function () {
    'use strict';

    var S = window.SIKESA;
    var TZ = 'Asia/Jakarta';
    var $ = function (id) { return document.getElementById(id); };

    /* ---------- Tanggal & sapaan ---------- */
    function tanggalPanjang(d) {
        return new Intl.DateTimeFormat('id-ID', {
            timeZone: TZ, weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        }).format(d);
    }
    function jamSekarang(d) {
        return parseInt(new Intl.DateTimeFormat('en-GB', { timeZone: TZ, hour: '2-digit', hour12: false }).format(d), 10) % 24;
    }
    function salam(jam) {
        if (jam < 11) { return 'Selamat pagi'; }
        if (jam < 15) { return 'Selamat siang'; }
        if (jam < 18) { return 'Selamat sore'; }
        return 'Selamat malam';
    }
    function namaPanggilan(nama) {
        return String(nama || '').trim().split(/\s+/)[0] || '';
    }
    function cap(s) { s = String(s || ''); return s.charAt(0).toUpperCase() + s.slice(1); }

    /* ---------- Render ---------- */
    function renderSapaan(d, guru) {
        var depan = namaPanggilan(guru.nama_guru);
        var sapaan = guru.sapaan || 'Bapak/Ibu';   // belum ada kolom jenis kelamin; boleh dikirim API sebagai "sapaan"
        $('tanggalHariIni').textContent = tanggalPanjang(d);
        $('sapaan').textContent = salam(jamSekarang(d)) + (depan ? ', ' + sapaan + ' ' + depan : '');
    }

    function renderKelas(kelas, ta) {
        var kartu = $('kartuKelas');
        kartu.setAttribute('aria-busy', 'false');
        if (!kelas) {
            $('kelasNama').textContent = 'Belum ada kelas';
            $('kelasMeta').textContent = 'Anda belum menjadi wali kelas pada tahun ajaran ini.';
            $('kelasStat').hidden = true;
            return;
        }
        $('kelasStat').hidden = false;
        $('kelasNama').textContent = 'Kelas ' + (kelas.nama_kelas || '-');
        var meta = [];
        if (ta && ta.nama_tahun) { meta.push('Tahun Ajaran ' + ta.nama_tahun); }
        if (ta && ta.semester_aktif) { meta.push('Semester ' + cap(ta.semester_aktif)); }
        $('kelasMeta').textContent = meta.join(' \u00b7 ') || '\u00a0';
        var n = kelas.jumlah_siswa;
        $('jumlahSiswa').textContent = (n === undefined || n === null) ? '-' : String(n);
    }

    function setChip(teks, jenis) {
        var c = $('absenChip');
        c.textContent = teks;
        c.className = 'chip chip--' + jenis;
    }

    function renderAbsensi(d, kelas, absensi, libur) {
        var kartu = $('kartuAbsen');
        kartu.setAttribute('aria-busy', 'false');

        if (!kelas) { kartu.hidden = true; return; }
        kartu.hidden = false;

        var tombol = $('absenTombol');
        var rekap = $('absenRekap');
        $('absenTanggal').textContent = tanggalPanjang(d);
        rekap.hidden = true;
        tombol.hidden = true;

        if (libur) {
            setChip('Libur', 'muted');
            $('absenPesan').textContent = (libur.keterangan ? libur.keterangan + '. ' : '') + 'Tidak ada absensi hari ini.';
            return;
        }

        var label = tombol.querySelector('span');
        if (absensi && absensi.sudah_absen) {
            setChip('Sudah', 'ok');
            $('absenPesan').textContent = 'Absensi hari ini sudah tercatat.';
            var r = absensi.ringkasan || null;
            if (r) {
                ['hadir', 'sakit', 'izin', 'alpa'].forEach(function (k) {
                    var el = rekap.querySelector('[data-rekap="' + k + '"]');
                    if (el) { el.textContent = String(r[k] || 0); }
                });
                rekap.hidden = false;
            }
            label.textContent = 'Lihat / Ubah Absensi';
        } else {
            setChip('Belum', 'warn');
            $('absenPesan').textContent = 'Absensi belum diisi. Catat kehadiran sebelum pelajaran dimulai.';
            label.textContent = 'Isi Absensi';
        }
        tombol.hidden = false;
    }

    /* ---------- Muat data ---------- */
    var errorBox = $('dashError');
    var kartuKelas = $('kartuKelas');
    var kartuAbsen = $('kartuAbsen');
    var sedangMuat = false;

    function tampilSapaanDariSesi() {
        var u = S.getUser() || {};
        renderSapaan(new Date(), { nama_guru: u.nama_guru || u.nama || '', sapaan: u.sapaan });
    }

    function tampilError(pesan) {
        errorBox.querySelector('[data-error-message]').textContent = pesan;
        errorBox.hidden = false;
        kartuKelas.hidden = true;
        kartuAbsen.hidden = true;
    }

    function muat() {
        if (sedangMuat) { return; }
        sedangMuat = true;
        errorBox.hidden = true;
        [kartuKelas, kartuAbsen].forEach(function (k) { k.hidden = false; k.setAttribute('aria-busy', 'true'); });

        S.api.get('/guru/dashboard').then(function (data) {
            data = data || {};
            var now = new Date();
            var guru = data.guru || {};
            var kelas = data.kelas || null;

            var u = S.getUser() || {};
            renderSapaan(now, {
                nama_guru: guru.nama_guru || u.nama_guru || u.nama || '',
                sapaan: guru.sapaan || u.sapaan
            });
            S.layout.setProfil({
                nama: guru.nama_guru,
                peran: guru.jabatan || (kelas ? 'Wali Kelas ' + kelas.nama_kelas : 'Guru')
            });
            renderKelas(kelas, data.tahun_ajaran);
            renderAbsensi(now, kelas, data.absensi_hari_ini, data.libur || data.hari_libur);
        }).catch(function (err) {
            tampilError(err && err.message ? err.message : 'Data belum bisa dimuat.');
        }).then(function () { sedangMuat = false; });
    }

    errorBox.querySelector('[data-retry]').addEventListener('click', muat);

    tampilSapaanDariSesi();
    muat();
})();