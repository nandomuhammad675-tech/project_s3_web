/* SI-KESA PWA Admin | Tahun Ajaran & Kenaikan Kelas (wizard 6 langkah)
 * Dokumen 01 bagian 3.6. Langkah: 1 Tahun Baru, 2 Kelas & Wali, 3 Kesiapan,
 * 4 Petakan Siswa, 5 Ringkasan & Jalankan, 6 Aktifkan.
 * WAJIB: tidak ada pilihan "tidak naik kelas". Server yang memutuskan; layar hanya membantu. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* ---- Alamat endpoint (SESUAIKAN dengan Dokumen 00) ---- */
  var EP = {
    tahun:    E.tahunAjaran,                                   // GET daftar, POST buat tahun baru
    kelas:    E.kelas,                                         // GET ?id_tahun_ajaran=
    kesiapan: '/api/admin/kenaikan-kelas/kesiapan',            // GET ?id_tahun_ajaran_tujuan=
    jalan:    '/api/admin/kenaikan-kelas',                     // POST jalankan kenaikan kelas
    log:      '/api/admin/kenaikan-kelas/log',                 // GET log (hanya lihat)
    aktifkan: function (id) { return E.tahunAjaran + '/' + id + '/aktifkan'; } // POST
  };

  var STEPS = ['Tahun Baru', 'Kelas & Wali', 'Kesiapan', 'Petakan Siswa', 'Ringkasan', 'Aktifkan'];
  var HEAD = [
    ['Siapkan Tahun Ajaran Baru', 'Buat tahun ajaran tujuan. Otomatis semester ganjil dan belum aktif.'],
    ['Siapkan Kelas dan Wali', 'Setiap kelas wajib punya wali yang akunnya aktif.'],
    ['Periksa Kesiapan', 'Tinjau data sebelum melakukan pemetaan.'],
    ['Petakan Siswa', 'Tentukan kelas tujuan bila diperlukan.'],
    ['Ringkasan dan Jalankan', 'Periksa sekali lagi sebelum proses dijalankan.'],
    ['Aktifkan Tahun Ajaran', 'Langkah terakhir agar tahun ajaran baru dipakai.']
  ];

  /* ---------- util ---------- */
  var $ = function (id) { return document.getElementById(id); };
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function url(p) { return (CFG.base || '') + '/' + p; }
  function pick(o, keys, fb) {
    for (var i = 0; i < keys.length; i++) if (o && o[keys[i]] !== undefined && o[keys[i]] !== null) return o[keys[i]];
    return fb;
  }
  function toArr(v) { return Array.isArray(v) ? v : []; }
  function toList(res) {
    if (Array.isArray(res)) return res;
    var d = res && res.data;
    if (Array.isArray(d)) return d;
    if (d && Array.isArray(d.items)) return d.items;
    if (res && Array.isArray(res.items)) return res.items;
    return [];
  }
  function count(v) { return Array.isArray(v) ? v.length : (Number(v) || 0); }
  function flag(v) {
    if (v === undefined || v === null || v === '') return null;
    if (typeof v === 'boolean') return v;
    if (typeof v === 'number') return v === 1;
    var s = String(v).toLowerCase();
    if (['1', 'true', 'aktif', 'active', 'ya'].indexOf(s) > -1) return true;
    if (['0', 'false', 'nonaktif', 'tidak', 'inactive', 'tidak aktif'].indexOf(s) > -1) return false;
    return null;
  }
  function num(v) { return /^\d+$/.test(String(v)) ? Number(v) : v; }
  function startYear(n) { var m = String(n).match(/^(\d{4})/); return m ? +m[1] : 0; }
  function icon(n, cls) { return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
  function toast(t, type) { if (window.SikesaUI) window.SikesaUI.toast(t, type); }
  function byNama(a, b) { return String(a.nama).localeCompare(String(b.nama), 'id', { numeric: true }); }
  function fromISO(s) { var p = s.split('-').map(Number); return new Date(p[0], p[1] - 1, p[2]); }

  /* ---------- normalisasi data server ---------- */
  function normTahun(r) {
    return {
      id: pick(r, ['id', 'id_tahun_ajaran']),
      nama: pick(r, ['nama_tahun', 'nama', 'tahun_ajaran', 'tahun'], '-'),
      aktif: flag(pick(r, ['status', 'status_aktif', 'is_aktif', 'aktif'], null)) === true
    };
  }
  function normKelas(r) {
    var nama = String(pick(r, ['nama_kelas', 'nama'], '-'));
    var t = parseInt(pick(r, ['tingkat'], ''), 10);
    if (isNaN(t)) { var m = nama.match(/\d/); t = m ? +m[0] : 0; }
    var wali = pick(r, ['nama_wali', 'wali_kelas', 'nama_guru', 'wali'], null);
    if (wali && typeof wali === 'object') wali = wali.nama_guru || wali.nama || null;
    var aktif = flag(pick(r, ['wali_aktif', 'status_akun_wali', 'akun_wali_aktif'], null));
    return {
      id: pick(r, ['id', 'id_kelas']),
      tahunId: pick(r, ['id_tahun_ajaran', 'tahun_ajaran_id'], null),
      nama: nama, tingkat: t, wali: wali,
      waliAktif: wali ? (aktif === null ? true : aktif) : false
    };
  }
  function normSiswa(r) {
    if (typeof r === 'string') return { id: r, nama: r, nisn: '', kelas: '' };
    return {
      id: pick(r, ['id_siswa', 'id']),
      nama: pick(r, ['nama_siswa', 'nama'], '-'),
      nisn: pick(r, ['nisn'], ''),
      kelas: pick(r, ['kelas', 'nama_kelas', 'kelas_asal'], '')
    };
  }
  function normKes(res) {
    var d = (res && res.data && !Array.isArray(res.data)) ? res.data : (res || {});
    var tanpaRaw = pick(d, ['siswa_tanpa_penempatan', 'tanpa_penempatan'], 0);
    var tanpaList = Array.isArray(tanpaRaw) ? tanpaRaw : toArr(pick(d, ['daftar_tanpa_penempatan'], []));
    var baruRaw = pick(d, ['siswa_baru', 'baru'], 0);
    var baruList = Array.isArray(baruRaw) ? baruRaw : toArr(pick(d, ['daftar_siswa_baru'], []));
    return {
      naik: count(pick(d, ['siswa_siap_naik', 'siswa_naik', 'naik'], 0)),
      lulus: count(pick(d, ['siswa_lulus', 'lulus'], 0)),
      baru: Array.isArray(baruRaw) ? baruRaw.length : (Number(baruRaw) || baruList.length),
      tanpa: Array.isArray(tanpaRaw) ? tanpaRaw.length : (Number(tanpaRaw) || tanpaList.length),
      tanpaList: tanpaList.map(normSiswa),
      perTingkat: toArr(pick(d, ['per_tingkat'], [])).map(function (x) {
        return { tingkat: pick(x, ['tingkat'], '-'), jumlah: count(pick(x, ['jumlah', 'total'], 0)), naik: count(pick(x, ['naik'], 0)), lulus: count(pick(x, ['lulus'], 0)) };
      }),
      siswa5: toArr(pick(d, ['siswa_tingkat_5'], [])).map(normSiswa),
      siswaBaru: baruList.map(normSiswa)
    };
  }

  /* ---------- keadaan ---------- */
  var S = {
    loading: true, error: null,
    aktif: null, target: null, tahunList: [],
    step: 1, maxStep: 1, executed: false, activated: false,
    kelas: [], kelasLoaded: false, kes: null, kesLoaded: false, dataErr: null, dataLoading: false,
    map6: {}, mapBaru: {},
    nameDraft: '', formMsg: '', busy: false,
    result: null, problems: null, actMsg: ''
  };

  function suggestName() {
    if (!S.aktif) return '';
    var y = startYear(S.aktif.nama);
    return y ? (y + 1) + '/' + (y + 2) : '';
  }
  function validName(v) {
    var m = String(v).trim().match(/^(\d{4})\/(\d{4})$/);
    return !!m && (+m[2]) === (+m[1]) + 1;
  }

  /* ---------- pemeriksaan kelas (langkah 2) ---------- */
  function kelas6() { return S.kelas.filter(function (k) { return k.tingkat === 6; }).sort(byNama); }
  function needMap6() { return kelas6().length >= 2; }
  function unmapped6() {
    if (!needMap6() || !S.kes) return 0;
    return S.kes.siswa5.filter(function (s) { return !S.map6[s.id]; }).length;
  }
  function mappedBaru() { return Object.keys(S.mapBaru).filter(function (k) { return S.mapBaru[k]; }).length; }

  function runChecks(kelas) {
    var by = {};
    kelas.forEach(function (k) { (by[k.tingkat] = by[k.tingkat] || []).push(k); });
    var out = [];

    var bad = [];
    [2, 3, 4, 5].forEach(function (t) { var n = (by[t] || []).length; if (n !== 1) bad.push('Tingkat ' + t + ': ' + n + ' kelas'); });
    out.push({ label: 'Tingkat 2-5 masing-masing tepat 1 kelas', ok: !bad.length, detail: bad.join('; ') });

    var bad2 = [];
    var n1 = (by[1] || []).length, n6 = (by[6] || []).length;
    if (n1 > 2) bad2.push('Tingkat 1: ' + n1 + ' kelas (maksimal 2)');
    if (n6 > 2) bad2.push('Tingkat 6: ' + n6 + ' kelas (maksimal 2)');
    if (n6 < 1) bad2.push('Tingkat 6: belum ada kelas');
    out.push({ label: 'Tingkat 1 dan 6 maksimal 2 kelas', ok: !bad2.length, detail: bad2.join('; ') });

    var tanpa = kelas.filter(function (k) { return !k.wali; }).map(function (k) { return k.nama; });
    out.push({ label: 'Setiap kelas punya wali', ok: kelas.length > 0 && !tanpa.length, detail: tanpa.length ? 'Belum ada wali: ' + tanpa.join(', ') : (kelas.length ? '' : 'Belum ada kelas') });

    var non = kelas.filter(function (k) { return k.wali && !k.waliAktif; }).map(function (k) { return k.nama + ' (' + k.wali + ')'; });
    out.push({ label: 'Setiap wali berakun aktif', ok: !non.length, detail: non.length ? 'Akun nonaktif: ' + non.join(', ') : '' });
    return out;
  }
  function checksOk() { return runChecks(S.kelas).every(function (c) { return c.ok; }); }

  /* =====================================================================
   * Muat data dasar & data per langkah
   * ===================================================================== */
  function loadBase() {
    S.loading = true; S.error = null; render();
    return Api.get(EP.tahun).then(function (res) {
      S.tahunList = toList(res).map(normTahun);
      S.aktif = S.tahunList.find(function (t) { return t.aktif; }) || null;
      S.target = null;
      if (S.aktif) {
        var base = startYear(S.aktif.nama);
        var next = S.tahunList.filter(function (t) { return !t.aktif && startYear(t.nama) > base; })
          .sort(function (a, b) { return startYear(a.nama) - startYear(b.nama); });
        S.target = next[0] || null;
      }
      S.executed = false;
      if (!S.target) return;
      // Sudah pernah dijalankan? (ada log untuk tahun tujuan ini)
      return Api.get(EP.log, { id_tahun_ajaran_tujuan: S.target.id, limit: 1 }).then(function (r) {
        S.executed = toList(r).length > 0;
      }).catch(function (err) { if (err && err.status === 401) throw err; S.executed = false; });
    }).then(function () {
      S.activated = false;
      S.kelasLoaded = false; S.kesLoaded = false;
      S.step = !S.aktif ? 1 : S.target ? (S.executed ? 6 : 2) : 1;
      S.maxStep = S.executed ? 6 : S.step;
      S.loading = false;
      render();
      loadStepData();
      loadLog(false);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      S.loading = false;
      S.error = err && err.message ? err.message : 'Terjadi gangguan, coba lagi';
      render();
    });
  }

  function sanitizeMaps() {
    var ids = {};
    S.kelas.forEach(function (k) { ids[k.id] = true; });
    [S.map6, S.mapBaru].forEach(function (m) {
      Object.keys(m).forEach(function (sid) { if (m[sid] && !ids[m[sid]] && !ids[Number(m[sid])]) delete m[sid]; });
    });
  }

  function loadStepData(force) {
    if (!S.target) return Promise.resolve();
    var needKelas = S.step >= 2 && S.step <= 5 && (force || !S.kelasLoaded);
    var needKes = S.step >= 3 && S.step <= 5 && (force || !S.kesLoaded);
    if (!needKelas && !needKes) return Promise.resolve();

    S.dataLoading = true; S.dataErr = null; render();
    var ps = [];
    if (needKelas) {
      ps.push(Api.get(EP.kelas, { id_tahun_ajaran: S.target.id }).then(function (res) {
        S.kelas = toList(res).map(normKelas).filter(function (k) {
          return k.tahunId === null || String(k.tahunId) === String(S.target.id);
        });
        S.kelasLoaded = true;
        sanitizeMaps();
      }));
    }
    if (needKes) {
      ps.push(Api.get(EP.kesiapan, { id_tahun_ajaran_tujuan: S.target.id }).then(function (res) {
        S.kes = normKes(res); S.kesLoaded = true;
      }));
    }
    return Promise.all(ps).catch(function (err) {
      if (err && err.status === 401) return;
      S.dataErr = err && err.message ? err.message : 'Terjadi gangguan, coba lagi';
    }).then(function () { S.dataLoading = false; render(); });
  }

  /* =====================================================================
   * Tampilan
   * ===================================================================== */
  function stepperHtml() {
    var current = S.activated ? 7 : S.step;
    return '<ol class="stepper" aria-label="Langkah kenaikan kelas">' + STEPS.map(function (t, i) {
      var n = i + 1, done = n < current, cur = n === current;
      var can = !S.executed && !S.activated && !S.busy && n <= S.maxStep && n !== S.step;
      var cls = 'stepper__dot' + (done ? ' is-done' : '') + (cur ? ' is-current' : '');
      return '<li class="stepper__item"><div class="stepper__track">' +
        '<button type="button" class="' + cls + '" data-act="goto" data-step="' + n + '"' + (can ? '' : ' disabled') +
        (cur ? ' aria-current="step"' : '') + ' aria-label="Langkah ' + n + ': ' + esc(t) + (done ? ' (selesai)' : '') + '">' +
        (done ? icon('check') : n) + '</button>' +
        (n < 6 ? '<span class="stepper__line' + (n < current ? ' is-done' : '') + '"></span>' : '') +
        '</div><div class="stepper__eyebrow">Langkah ' + n + '</div><div class="stepper__title">' + esc(t) + '</div></li>';
    }).join('') + '</ol>';
  }

  function head(n) {
    return '<div class="wiz-head"><span class="wiz-num">0' + n + '</span><div><h2>' + esc(HEAD[n - 1][0]) + '</h2><p>' + esc(HEAD[n - 1][1]) + '</p></div></div><hr class="divider">';
  }
  function foot(opts) {
    // opts: back(bool), nextLabel, nextAct, hint
    var h = '<div class="wiz-foot">';
    h += opts.back === false ? '<span></span>' :
      '<button type="button" class="btn btn--outline" data-act="back"' + (S.busy ? ' disabled' : '') + '>Kembali</button>';
    h += '<div class="wiz-foot__right"><span class="hint" id="next-hint"></span>';
    if (opts.nextLabel) {
      h += '<button type="button" class="btn btn--amber" id="btn-next" data-act="' + opts.nextAct + '">' +
        icon('chevron', 'ico--sm') + ' ' + esc(opts.nextLabel) + '</button>';
    }
    return h + '</div></div>';
  }
  function dataBox() {
    if (S.dataErr) {
      return '<div class="state-box"><strong>Gagal memuat data</strong>' + esc(S.dataErr) +
        '<br><button type="button" class="btn btn--outline" data-act="retry-data">' + icon('refresh', 'ico--sm') + ' Coba lagi</button></div>';
    }
    return '<span class="skeleton skeleton--row"></span><br><br><span class="skeleton skeleton--row"></span><br><br><span class="skeleton skeleton--row"></span>';
  }
  function dataReady(step) {
    if (step === 2) return S.kelasLoaded;
    if (step >= 3 && step <= 5) return S.kelasLoaded && S.kesLoaded;
    return true;
  }

  /* --- Langkah 1 --- */
  function step1() {
    var h = head(1);
    if (S.target) {
      return h + '<div class="callout" style="background:#f4f8ea;border-color:#d4e1b4;color:#3d5416">' + icon('check') +
        '<div><strong>Tahun ajaran ' + esc(S.target.nama) + ' sudah dibuat.</strong><br>Semester Ganjil, belum aktif. Lanjutkan menyiapkan kelas dan wali.</div></div>' +
        foot({ back: false, nextLabel: 'Simpan & Lanjutkan', nextAct: 'next' });
    }
    var val = S.nameDraft !== '' ? S.nameDraft : suggestName();
    S.nameDraft = val;
    return h + '<div class="field" style="max-width:360px"><label for="in-tahun">Nama tahun ajaran</label>' +
      '<input class="input" id="in-tahun" data-bind="name" type="text" inputmode="numeric" maxlength="9" placeholder="2027/2028" value="' + esc(val) + '" autocomplete="off">' +
      '<p class="hint" style="margin-top:6px">Format 2027/2028. Semester otomatis Ganjil dan tahun ajaran belum aktif.</p></div>' +
      (S.formMsg ? '<div class="notice notice--info" role="alert">' + esc(S.formMsg) + '</div>' : '') +
      foot({ back: false, nextLabel: S.busy ? 'Memproses...' : 'Simpan & Lanjutkan', nextAct: 'next' });
  }

  /* --- Langkah 2 --- */
  function step2() {
    var h = head(2);
    if (!dataReady(2)) return h + dataBox();
    var checks = runChecks(S.kelas);
    var rows = S.kelas.slice().sort(function (a, b) { return a.tingkat - b.tingkat || byNama(a, b); });

    var tools = '<div class="wiz-tools"><button type="button" class="btn btn--ghost btn--sm" data-act="reload-kelas">' + icon('refresh', 'ico--xs') + ' Muat ulang</button>' +
      '<a class="btn btn--ghost btn--sm" href="' + esc(url('kelas')) + '">Kelola Kelas</a></div>';

    var ck = '<div class="wiz-block"><div class="card__row"><h3>Pemeriksaan</h3>' + tools + '</div><div class="wiz-table-wrap"><table class="table table--plain"><thead><tr><th>Pemeriksaan</th><th>Tanda</th></tr></thead><tbody>' +
      checks.map(function (c) {
        return '<tr><td>' + esc(c.label) + '</td><td>' + (c.ok
          ? '<span class="tick tick--ok">' + icon('check') + ' Sesuai</span>'
          : '<span class="tick tick--warn">' + icon('alert') + ' <span>Perlu diperbaiki' + (c.detail ? '<br><small>' + esc(c.detail) + '</small>' : '') + '</span></span>') + '</td></tr>';
      }).join('') + '</tbody></table></div></div>';

    var list = '<div class="wiz-block"><h3>Kelas ' + esc(S.target.nama) + '</h3>' + (rows.length
      ? '<div class="wiz-table-wrap"><table class="table"><thead><tr><th>Tingkat</th><th>Kelas</th><th>Wali kelas</th><th>Akun wali</th></tr></thead><tbody>' +
        rows.map(function (k) {
          var akun = !k.wali ? '<span class="pill pill--neutral">Belum ada wali</span>'
            : k.waliAktif ? '<span class="pill pill--green">Aktif</span>' : '<span class="pill" style="background:#fde4e0;color:#a32d20">Nonaktif</span>';
          return '<tr><td>' + esc(k.tingkat) + '</td><td>' + esc(k.nama) + '</td><td>' + (k.wali ? esc(k.wali) : '<span class="is-muted">—</span>') + '</td><td>' + akun + '</td></tr>';
        }).join('') + '</tbody></table></div>'
      : '<div class="state-box" style="padding:16px"><strong>Belum ada kelas</strong>Buat kelas untuk tahun ajaran ' + esc(S.target.nama) + ' di Data Master.<br><a class="btn btn--outline" href="' + esc(url('kelas')) + '">Buka Data Kelas</a></div>') + '</div>';

    return h + ck + list + foot({ nextLabel: 'Simpan & Lanjutkan', nextAct: 'next' });
  }

  /* --- Langkah 3 (sesuai screenshot) --- */
  function step3() {
    var h = head(3);
    if (!dataReady(3)) return h + dataBox();
    var k = S.kes;
    var out = h + '<div class="stats">' +
      stat('green', k.naik, 'Siswa siap naik') + stat('purple', k.lulus, 'Siswa lulus') +
      stat('blue', k.baru, 'Siswa baru') + stat('amber', k.tanpa, 'Tanpa penempatan') + '</div>';

    if (k.tanpa > 0) {
      out += '<div class="callout" style="margin-top:16px">' + icon('alert') + '<div><strong>' + k.tanpa +
        ' siswa aktif belum punya kelas.</strong> Tempatkan mereka lebih dulu (harus 0) sebelum melanjutkan.' +
        (k.tanpaList.length ? '<ul style="margin:6px 0 0;padding-left:18px">' + k.tanpaList.map(function (s) {
          return '<li>' + esc(s.nama) + (s.nisn ? ' (' + esc(s.nisn) + ')' : '') + '</li>';
        }).join('') + '</ul>' : '') +
        '<br><a href="' + esc(url('riwayat-kelas')) + '"><b>Buka Penempatan Siswa</b></a></div></div>';
    }
    if (k.perTingkat.length) {
      out += '<details class="wiz-details"><summary>Rincian per tingkat</summary><div class="wiz-table-wrap"><table class="table table--plain"><thead><tr><th>Tingkat</th><th>Siswa aktif</th><th>Naik</th><th>Lulus</th></tr></thead><tbody>' +
        k.perTingkat.map(function (r) { return '<tr><td>' + esc(r.tingkat) + '</td><td>' + r.jumlah + '</td><td>' + r.naik + '</td><td>' + r.lulus + '</td></tr>'; }).join('') +
        '</tbody></table></div></details>';
    }
    out += '<p class="hint" style="margin-top:12px">Siswa baru hanya sebagai informasi dan tidak memblokir proses.</p>';
    return out + foot({ nextLabel: 'Simpan & Lanjutkan', nextAct: 'next' });
  }
  function stat(c, n, l) { return '<div class="stat stat--' + c + '"><div class="stat__n">' + n + '</div><div class="stat__l">' + l + '</div></div>'; }

  /* --- Langkah 4 --- */
  function step4() {
    var h = head(4);
    if (!dataReady(4)) return h + dataBox();
    var out = h;

    // 5 -> 6 hanya bila tahun tujuan punya DUA kelas tingkat 6. Jika satu, blok ini tidak ditampilkan
    // dan layar tidak mengirim penempatan_tingkat_6.
    if (needMap6()) {
      var opts6 = kelas6();
      out += '<div class="wiz-block"><h3>Siswa tingkat 5 ke tingkat 6</h3><p>Setiap siswa tingkat 5 wajib dipilihkan kelas tujuannya.</p>' +
        '<div class="map-bar"><span id="map6-count" class="pill pill--neutral"></span>' +
        '<label>Isi semua ke <select class="input input--sm" data-bind="bulk6"><option value="">Pilih kelas</option>' +
        opts6.map(function (k) { return '<option value="' + esc(k.id) + '">' + esc(k.nama) + '</option>'; }).join('') + '</select></label></div>' +
        (S.kes.siswa5.length
          ? '<div class="wiz-table-wrap"><table class="table"><thead><tr><th>Nama</th><th>NISN</th><th>Kelas asal</th><th>Kelas tujuan</th></tr></thead><tbody>' +
            S.kes.siswa5.map(function (s) {
              return '<tr><td>' + esc(s.nama) + '</td><td>' + esc(s.nisn || '—') + '</td><td>' + esc(s.kelas || '—') + '</td><td>' +
                '<select class="input input--sm" data-bind="map6" data-sid="' + esc(s.id) + '" aria-label="Kelas tujuan ' + esc(s.nama) + '"><option value="">Pilih kelas</option>' +
                opts6.map(function (k) { return '<option value="' + esc(k.id) + '"' + (String(S.map6[s.id]) === String(k.id) ? ' selected' : '') + '>' + esc(k.nama) + '</option>'; }).join('') +
                '</select></td></tr>';
            }).join('') + '</tbody></table></div>'
          : '<p class="hint">Tidak ada siswa tingkat 5 yang perlu dipetakan.</p>') + '</div>';
    }

    // Siswa baru (opsional, tingkat berapa pun; tingkat 1 hanya bila kelasnya sudah dibuat)
    var all = S.kelas.slice().sort(function (a, b) { return a.tingkat - b.tingkat || byNama(a, b); });
    out += '<div class="wiz-block"><h3>Siswa baru <span class="pill pill--neutral">Opsional</span></h3>' +
      '<p>Pilih kelas tujuan untuk siswa baru. Yang tidak dipilih tetap berstatus Baru.</p>' +
      (S.kes.siswaBaru.length
        ? '<div class="map-bar"><span id="mapbaru-count" class="pill pill--neutral"></span></div><div class="wiz-table-wrap"><table class="table"><thead><tr><th>Nama</th><th>NISN</th><th>Kelas tujuan</th></tr></thead><tbody>' +
          S.kes.siswaBaru.map(function (s) {
            return '<tr><td>' + esc(s.nama) + '</td><td>' + esc(s.nisn || '—') + '</td><td>' +
              '<select class="input input--sm" data-bind="mapbaru" data-sid="' + esc(s.id) + '" aria-label="Kelas tujuan ' + esc(s.nama) + '"><option value="">Belum ditempatkan</option>' +
              all.map(function (k) { return '<option value="' + esc(k.id) + '"' + (String(S.mapBaru[s.id]) === String(k.id) ? ' selected' : '') + '>' + esc(k.nama) + ' (tingkat ' + esc(k.tingkat) + ')</option>'; }).join('') +
              '</select></td></tr>';
          }).join('') + '</tbody></table></div>'
        : '<p class="hint">Tidak ada siswa baru.</p>') + '</div>';

    return out + foot({ nextLabel: 'Simpan & Lanjutkan', nextAct: 'next' });
  }

  /* --- Langkah 5 --- */
  function step5() {
    var h = head(5);
    if (!dataReady(5)) return h + dataBox();
    var k = S.kes, masuk = mappedBaru(), sisa = k.baru - masuk;
    var out = h;

    if (S.problems) {
      var g = S.problems.groups;
      out += '<div class="problems" role="alert"><div class="problems__head">' + icon('alert') + '<div><strong>Kenaikan kelas belum dapat dijalankan</strong>' +
        '<p>' + esc(S.problems.message) + '</p><p><b>Tidak ada data yang berubah.</b></p></div></div>' +
        g.map(function (grp) {
          return '<h3>' + esc(grp.label) + ' <span class="pill pill--neutral">' + grp.items.length + '</span></h3><ul>' +
            grp.items.map(function (i) { return '<li>' + esc(i) + '</li>'; }).join('') + '</ul>';
        }).join('') +
        '<button type="button" class="btn btn--primary" data-act="fix">Perbaiki lalu coba lagi</button></div>';
    }

    out += '<p class="sum-line"><b>' + k.naik + '</b> siswa naik, <b>' + k.lulus + '</b> siswa lulus, <b>' + masuk + '</b> siswa baru masuk.</p>' +
      '<ul class="sum-list"><li>Dari tahun ajaran <b>' + esc(S.aktif.nama) + '</b> ke <b>' + esc(S.target.nama) + '</b>.</li>' +
      '<li>Semua siswa aktif naik satu tingkat. Siswa tingkat 6 lulus.</li>';
    if (needMap6()) {
      var tally = {};
      S.kes.siswa5.forEach(function (s) { var kid = S.map6[s.id]; if (kid) tally[kid] = (tally[kid] || 0) + 1; });
      out += '<li>Tingkat 5 ke tingkat 6: ' + kelas6().map(function (kl) { return esc(kl.nama) + ' ' + (tally[kl.id] || 0) + ' siswa'; }).join(', ') + '.</li>';
    }
    if (sisa > 0) out += '<li>' + sisa + ' siswa baru belum dipetakan dan tetap berstatus Baru.</li>';
    out += '</ul>';

    if (S.problems) return out + '<div class="wiz-foot"><button type="button" class="btn btn--outline" data-act="back">Kembali</button><span></span></div>';
    return out + foot({ nextLabel: S.busy ? 'Memproses...' : 'Jalankan Kenaikan Kelas', nextAct: 'run' });
  }

  /* --- Langkah 6 --- */
  function step6() {
    var h = head(6), out = h;
    if (S.activated) {
      return out + '<div class="banner-ok">' + icon('check') + '<div><strong>Tahun ajaran ' + esc(S.target.nama) + ' kini aktif.</strong>Siswa lulus dan siswa baru hasil masuk sudah bisa dipakai di Wali dan daftar Guru.</div></div>' +
        '<a class="btn btn--primary" href="' + esc(url('dashboard')) + '">Kembali ke Dasbor</a>';
    }
    if (S.executed) {
      var r = S.result;
      out += '<div class="banner-ok">' + icon('check') + '<div><strong>' + esc((r && r.message) || 'Kenaikan kelas sudah dijalankan.') + '</strong>' +
        (r && (r.naik != null || r.lulus != null) ? (r.naik || 0) + ' siswa naik, ' + (r.lulus || 0) + ' siswa lulus, ' + (r.masuk || 0) + ' siswa baru masuk.' : 'Tinggal mengaktifkan tahun ajaran ' + esc(S.target.nama) + '.') + '</div></div>';
    }
    out += '<div class="callout">' + icon('alert') + '<div>Segera aktifkan tahun ajaran baru. Selama belum aktif, siswa lulus dan siswa baru hasil masuk belum bisa login Wali dan belum tampil di daftar Guru. Sebaiknya dilakukan di luar jam sekolah.</div></div>';
    if (S.actMsg) out += '<div class="notice notice--info" role="alert" style="margin-top:14px">' + esc(S.actMsg) + '</div>';
    return out + '<div class="wiz-foot"><span></span><div class="wiz-foot__right"><button type="button" class="btn btn--amber" data-act="activate"' + (S.busy ? ' disabled' : '') + '>' +
      icon('chevron', 'ico--sm') + ' ' + (S.busy ? 'Memproses...' : 'Aktifkan Tahun Ajaran') + '</button></div></div>';
  }

  function panel() {
    switch (S.step) {
      case 1: return step1();
      case 2: return step2();
      case 3: return step3();
      case 4: return step4();
      case 5: return step5();
      default: return step6();
    }
  }

  function render() {
    var host = $('wiz');
    if (S.loading) {
      host.innerHTML = '<section class="card"><span class="skeleton skeleton--row"></span></section><section class="card"><span class="skeleton skeleton--row"></span><br><br><span class="skeleton skeleton--row"></span></section>';
      return;
    }
    if (S.error) {
      host.innerHTML = '<section class="card"><div class="state-box"><strong>Gagal memuat halaman</strong>' + esc(S.error) +
        '<br><button type="button" class="btn btn--outline" data-act="retry-base">' + icon('refresh', 'ico--sm') + ' Coba lagi</button></div></section>';
      return;
    }
    $('wiz-sub').textContent = S.target ? 'Proses terpadu untuk menyiapkan tahun ajaran ' + S.target.nama + '.' : 'Proses terpadu untuk menyiapkan tahun ajaran berikutnya.';
    if (!S.aktif) {
      host.innerHTML = '<section class="card"><div class="state-box"><strong>Belum ada tahun ajaran aktif</strong>Aktifkan tahun ajaran pertama lebih dulu di Data Master, Tahun Ajaran.<br>' +
        '<a class="btn btn--outline" href="' + esc(url('tahun-ajaran')) + '">Buka Tahun Ajaran</a></div></section>';
      return;
    }
    host.innerHTML = '<section class="card wiz-stepper">' + stepperHtml() + '</section><section class="card wiz-panel">' + panel() + '</section>';
    refreshUi();
  }

  /* Perbarui tombol & penghitung tanpa merender ulang (agar fokus isian tidak hilang). */
  function nextEnabled() {
    if (S.busy) return false;
    switch (S.step) {
      case 1: return !!S.target || validName(S.nameDraft);
      case 2: return S.kelasLoaded && checksOk();
      case 3: return S.kesLoaded && S.kes.tanpa === 0;
      case 4: return S.kesLoaded && unmapped6() === 0;
      default: return true;
    }
  }
  function refreshUi() {
    var b = $('btn-next'), hint = $('next-hint');
    if (b) b.disabled = !nextEnabled();
    if (hint) {
      var t = '';
      if (S.step === 1 && !S.target && S.nameDraft && !validName(S.nameDraft)) t = 'Format harus seperti 2027/2028';
      if (S.step === 2 && S.kelasLoaded && !checksOk()) t = 'Perbaiki pemeriksaan yang bertanda peringatan';
      if (S.step === 3 && S.kesLoaded && S.kes.tanpa > 0) t = 'Tempatkan siswa tanpa kelas lebih dulu';
      if (S.step === 4 && unmapped6() > 0) t = 'Masih ada ' + unmapped6() + ' siswa tingkat 5 belum dipilihkan kelas 6';
      hint.textContent = t;
    }
    var c6 = $('map6-count');
    if (c6 && S.kes) c6.textContent = 'Terpetakan ' + (S.kes.siswa5.length - unmapped6()) + ' dari ' + S.kes.siswa5.length;
    var cb = $('mapbaru-count');
    if (cb && S.kes) cb.textContent = mappedBaru() + ' dari ' + S.kes.siswaBaru.length + ' siswa baru dipetakan';
  }

  /* =====================================================================
   * Aksi
   * ===================================================================== */
  function goStep(n) {
    S.step = n; S.maxStep = Math.max(S.maxStep, n); S.formMsg = '';
    render(); loadStepData();
    var w = $('wiz'); if (w && w.scrollIntoView) w.scrollIntoView({ block: 'start', behavior: 'smooth' });
  }

  function createYear() {
    var name = String(S.nameDraft).trim();
    if (!validName(name) || S.busy) return;
    S.busy = true; S.formMsg = ''; render();
    Api.post(EP.tahun, { nama_tahun: name }).then(function () {
      toast('Tahun ajaran ' + name + ' dibuat');
      S.nameDraft = '';
      return loadBase();
    }).catch(function (err) {
      if (err && err.status === 401) return;
      S.formMsg = err && err.message ? err.message : 'Terjadi gangguan, coba lagi'; // 409/422 apa adanya
    }).then(function () { S.busy = false; if (!S.loading) render(); });
  }

  function groupLabel(type) {
    var t = String(type).toLowerCase();
    if (t.indexOf('nonaktif') > -1 || t.indexOf('tidak_aktif') > -1) return 'Wali berakun nonaktif';
    if (t.indexOf('wali') > -1) return 'Kelas tanpa wali';
    if (t.indexOf('siswa') > -1 || t.indexOf('penempatan') > -1) return 'Siswa tanpa penempatan';
    return 'Masalah lain';
  }
  function parseProblems(body) {
    var raw = pick(body, ['masalah', 'problems', 'detail', 'details'], null);
    if (raw === null && body && body.data) raw = pick(body.data, ['masalah', 'problems'], null);
    if (raw === null) raw = pick(body, ['errors'], null);
    var items = [];
    function nameOf(v) {
      if (typeof v === 'string') return v;
      var n = pick(v, ['nama', 'nama_kelas', 'nama_siswa', 'kelas', 'siswa'], '');
      var p = pick(v, ['pesan', 'message', 'keterangan'], '');
      return n && p ? n + ': ' + p : (n || p || '');
    }
    if (Array.isArray(raw)) {
      raw.forEach(function (x) { items.push({ type: typeof x === 'string' ? 'lain' : pick(x, ['jenis', 'tipe', 'type'], 'lain'), text: nameOf(x) }); });
    } else if (raw && typeof raw === 'object') {
      Object.keys(raw).forEach(function (key) {
        (Array.isArray(raw[key]) ? raw[key] : [raw[key]]).forEach(function (v) { items.push({ type: key, text: nameOf(v) }); });
      });
    }
    var groups = {}, order = [];
    items.forEach(function (i) {
      if (!i.text) return;
      var l = groupLabel(i.type);
      if (!groups[l]) { groups[l] = { label: l, items: [] }; order.push(l); }
      groups[l].items.push(i.text);
    });
    return order.map(function (l) { return groups[l]; });
  }

  function runKenaikan() {
    if (S.busy) return;
    var payload = { id_tahun_ajaran_tujuan: S.target.id };
    // Hanya dikirim bila tahun tujuan punya dua kelas tingkat 6.
    if (needMap6()) {
      payload.penempatan_tingkat_6 = S.kes.siswa5.map(function (s) {
        return { id_siswa: num(s.id), id_kelas: num(S.map6[s.id]) };
      });
    }
    var baru = Object.keys(S.mapBaru).filter(function (k) { return S.mapBaru[k]; });
    if (baru.length) {
      payload.penempatan_siswa_baru = baru.map(function (k) { return { id_siswa: num(k), id_kelas: num(S.mapBaru[k]) }; });
    }
    S.busy = true; S.problems = null; render();
    Api.post(EP.jalan, payload).then(function (res) {
      var d = (res && res.data) || res || {};
      var r = d.ringkasan || d.summary || d;
      S.result = {
        message: pick(d, ['message'], pick(res, ['message'], '')),
        naik: pick(r, ['naik', 'siswa_naik'], null), lulus: pick(r, ['lulus', 'siswa_lulus'], null),
        masuk: pick(r, ['masuk', 'siswa_baru_masuk', 'baru_masuk'], null)
      };
      S.executed = true; S.step = 6; S.maxStep = 6;
      toast('Kenaikan kelas berhasil dijalankan');
      loadLog(false);
    }).catch(function (err) {
      if (err && err.status === 401) return;
      // 422: tampilkan daftar masalah lengkap dengan nama. Tidak ada data berubah.
      S.problems = { message: err && err.message ? err.message : 'Terjadi gangguan, coba lagi', groups: parseProblems(err && err.body) };
    }).then(function () { S.busy = false; render(); });
  }

  function activate() {
    if (S.busy) return;
    S.busy = true; S.actMsg = ''; render();
    Api.post(EP.aktifkan(S.target.id), {}).then(function () {
      S.activated = true; toast('Tahun ajaran ' + S.target.nama + ' aktif');
    }).catch(function (err) {
      if (err && err.status === 401) return;
      S.actMsg = err && err.message ? err.message : 'Terjadi gangguan, coba lagi'; // 422 apa adanya
    }).then(function () { S.busy = false; render(); });
  }

  /* ---------- delegasi peristiwa ---------- */
  var wiz = $('wiz');
  wiz.addEventListener('click', function (ev) {
    var el = ev.target.closest('[data-act]');
    if (!el || el.disabled) return;
    var act = el.getAttribute('data-act');
    if (act === 'goto') goStep(+el.getAttribute('data-step'));
    else if (act === 'back') goStep(Math.max(1, S.step - 1));
    else if (act === 'next') {
      if (!nextEnabled()) return;
      if (S.step === 1 && !S.target) createYear();
      else goStep(Math.min(5, S.step + 1));
    }
    else if (act === 'run') openRun();
    else if (act === 'activate') activate();
    else if (act === 'retry-base') loadBase();
    else if (act === 'retry-data') loadStepData(true);
    else if (act === 'reload-kelas') { S.kelasLoaded = false; loadStepData(true); }
    else if (act === 'fix') { S.problems = null; S.kelasLoaded = false; S.kesLoaded = false; goStep(2); }
  });
  wiz.addEventListener('input', function (ev) {
    if (ev.target.getAttribute('data-bind') === 'name') { S.nameDraft = ev.target.value; refreshUi(); }
  });
  wiz.addEventListener('change', function (ev) {
    var t = ev.target, b = t.getAttribute('data-bind');
    if (b === 'map6') { S.map6[t.getAttribute('data-sid')] = t.value; refreshUi(); }
    else if (b === 'mapbaru') { S.mapBaru[t.getAttribute('data-sid')] = t.value; refreshUi(); }
    else if (b === 'bulk6' && t.value) {
      S.kes.siswa5.forEach(function (s) { S.map6[s.id] = t.value; });
      document.querySelectorAll('select[data-bind="map6"]').forEach(function (sel) { sel.value = t.value; });
      t.value = ''; refreshUi();
    }
  });

  /* ---------- dialog konfirmasi ---------- */
  var dlg = $('dlg-run');
  function openRun() {
    if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
  }
  $('run-cancel').addEventListener('click', function () { dlg.close(); });
  $('form-run').addEventListener('submit', function (ev) { ev.preventDefault(); dlg.close(); runKenaikan(); });

  /* =====================================================================
   * Log Kenaikan Kelas (hanya lihat)
   * ===================================================================== */
  var logSeq = 0, logAll = false;
  function parseDT(v) {
    if (!v) return null;
    var s = String(v);
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return { d: fromISO(s), time: false };
    var d = new Date(s.replace(' ', 'T'));
    return isNaN(d) ? null : { d: d, time: true };
  }
  function fmtLogDate(p) {
    if (!p) return '—';
    var o = { day: 'numeric', month: 'long', year: 'numeric' };
    if (p.time) o.timeZone = 'Asia/Jakarta';
    return p.d.toLocaleDateString('id-ID', o);
  }
  function fmtLogTime(p) {
    if (!p || !p.time) return '';
    return p.d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta' }).replace(':', '.') + ' WIB';
  }
  function normLog(r) {
    var hasil = String(pick(r, ['hasil', 'status'], 'naik')).toLowerCase();
    hasil = hasil.indexOf('lulus') > -1 ? 'lulus' : hasil.indexOf('masuk') > -1 ? 'masuk' : 'naik';
    var dari = pick(r, ['kelas_asal', 'dari'], '');
    var ke = pick(r, ['kelas_tujuan', 'ke'], '');
    return {
      when: parseDT(pick(r, ['waktu', 'created_at', 'tanggal', 'diproses_pada'], null)),
      siswa: pick(r, ['siswa', 'nama_siswa', 'nama'], '-'),
      dari: dari || (hasil === 'masuk' ? 'Siswa Baru' : '—'),
      ke: ke || (hasil === 'lulus' ? 'Alumni' : '—'),
      hasil: hasil,
      tahun: pick(r, ['tahun_tujuan', 'nama_tahun_tujuan'], ''),
      oleh: pick(r, ['diproses_oleh', 'pemroses', 'oleh'], '')
    };
  }
  function loadLog(all) {
    logAll = !!all;
    var seq = ++logSeq;
    var body = $('log-body'), st = $('log-state'), tbl = $('log-table'), btn = $('log-toggle');
    st.hidden = true; tbl.hidden = false;
    body.innerHTML = '<tr><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr><tr><td colspan="5"><span class="skeleton skeleton--row"></span></td></tr>';
    Api.get(EP.log, { limit: all ? 100 : 5 }).then(function (res) {
      if (seq !== logSeq) return;
      var rows = toList(res).map(normLog);
      if (!rows.length) {
        tbl.hidden = true; btn.hidden = true; st.hidden = false;
        st.innerHTML = '<strong>Belum ada proses kenaikan kelas</strong>Riwayat akan tampil di sini setelah kenaikan kelas dijalankan.';
        return;
      }
      btn.hidden = false; btn.textContent = logAll ? 'Ringkas' : 'Lihat Semua';
      body.innerHTML = rows.map(function (r) {
        var meta = [r.tahun ? 'ke ' + esc(r.tahun) : '', r.oleh ? 'oleh ' + esc(r.oleh) : '', esc(fmtLogTime(r.when))].filter(Boolean).join(' · ');
        return '<tr><td>' + esc(fmtLogDate(r.when)) + (meta ? '<span class="log-meta">' + meta + '</span>' : '') + '</td><td>' + esc(r.siswa) + '</td><td>' + esc(r.dari) + '</td><td>' + esc(r.ke) +
          '</td><td><span class="badge badge--' + r.hasil + '">' + (r.hasil === 'lulus' ? 'Lulus' : r.hasil === 'masuk' ? 'Masuk' : 'Naik') + '</span></td></tr>';
      }).join('');
    }).catch(function (err) {
      if (seq !== logSeq || (err && err.status === 401)) return;
      tbl.hidden = true; btn.hidden = true; st.hidden = false;
      st.innerHTML = '<strong>Gagal memuat log</strong>' + esc(err && err.message ? err.message : 'Terjadi gangguan, coba lagi') +
        '<br><button type="button" class="btn btn--outline" id="log-retry">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>';
      $('log-retry').addEventListener('click', function () { loadLog(logAll); });
    });
  }
  $('log-toggle').addEventListener('click', function () { loadLog(!logAll); });

  /* ---------- mulai ---------- */
  loadBase();
})();