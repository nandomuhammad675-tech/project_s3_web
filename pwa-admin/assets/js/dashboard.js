/* SI-KESA PWA Admin | Dasbor
 * - Status setup sistem, tahun ajaran aktif, kesiapan operasional
 * - Status absensi semua kelas (GET /api/admin/dasbor/absensi?date=YYYY-MM-DD)
 * - Ganti Semester (dialog dengan kotak centang "Saya mengerti")
 * Semua teks dari server disisipkan lewat esc() agar aman. */
(function () {
  'use strict';

  var Api = window.SikesaApi;
  var CFG = window.SIKESA;
  var E = CFG.endpoints;
  if (!Api || !window.SikesaAuth || !window.SikesaAuth.token()) return;

  /* ---------- util ---------- */
  var $ = function (id) { return document.getElementById(id); };
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function url(p) { return (CFG.base || '') + '/' + p; }
  function pad(n) { return String(n).padStart(2, '0'); }
  function toISO(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
  function fromISO(s) { var p = s.split('-').map(Number); return new Date(p[0], p[1] - 1, p[2]); }
  function fmtDate(d) { return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }); }
  function pick(o, keys, fb) {
    for (var i = 0; i < keys.length; i++) if (o && o[keys[i]] !== undefined && o[keys[i]] !== null) return o[keys[i]];
    return fb;
  }
  function toList(res) {
    if (Array.isArray(res)) return res;
    var d = res && res.data;
    if (Array.isArray(d)) return d;
    if (d && Array.isArray(d.items)) return d.items;
    if (res && Array.isArray(res.items)) return res.items;
    return [];
  }
  function toTotal(res, list) {
    var t = pick(res && res.meta, ['total'], pick(res, ['total'], pick(res && res.data, ['total'], null)));
    return typeof t === 'number' ? t : list.length;
  }
  function flag(v) { // true/false/null (tidak diketahui)
    if (v === undefined || v === null || v === '') return null;
    if (typeof v === 'boolean') return v;
    if (typeof v === 'number') return v === 1;
    var s = String(v).toLowerCase();
    if (['1', 'true', 'aktif', 'active', 'ya'].indexOf(s) > -1) return true;
    if (['0', 'false', 'nonaktif', 'tidak', 'inactive', 'tidak aktif'].indexOf(s) > -1) return false;
    return null;
  }
  function icon(n, cls) { return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
  function toast(t, type) { if (window.SikesaUI) window.SikesaUI.toast(t, type); }

  /* ---------- normalisasi data server ---------- */
  function normTahun(r) {
    return {
      id: pick(r, ['id', 'id_tahun_ajaran']),
      nama: pick(r, ['nama_tahun', 'nama', 'tahun_ajaran', 'tahun'], '-'),
      semester: String(pick(r, ['semester_berjalan', 'semester_aktif', 'semester'], '')).toLowerCase(),
      aktif: flag(pick(r, ['status', 'status_aktif', 'is_aktif', 'aktif'], null)) === true
    };
  }
  function normKelas(r) {
    var waliNama = pick(r, ['nama_wali', 'wali_kelas', 'nama_guru', 'wali'], null);
    if (waliNama && typeof waliNama === 'object') waliNama = waliNama.nama_guru || waliNama.nama || null;
    var aktif = flag(pick(r, ['wali_aktif', 'status_akun_wali', 'akun_wali_aktif'], null));
    return {
      id: pick(r, ['id', 'id_kelas']),
      tahunId: pick(r, ['id_tahun_ajaran', 'tahun_ajaran_id'], null),
      nama: pick(r, ['nama_kelas', 'nama'], '-'),
      wali: waliNama,
      waliAktif: waliNama ? (aktif === null ? true : aktif) : false
    };
  }
  function normAbsensi(r) {
    var raw = String(pick(r, ['status', 'status_absensi'], 'belum')).toLowerCase();
    var st = raw.indexOf('sudah') > -1 ? 'sudah' : raw.indexOf('libur') > -1 ? 'libur' : 'belum';
    var wali = pick(r, ['wali_kelas', 'nama_wali', 'nama_guru'], null);
    var aktif = flag(pick(r, ['wali_aktif', 'status_akun_wali'], null));
    return {
      kelas: pick(r, ['nama_kelas', 'kelas', 'nama'], '-'),
      wali: wali,
      waliAktif: wali ? (aktif === null ? true : aktif) : false,
      status: st,
      updated: pick(r, ['terakhir_diperbarui', 'updated_at', 'waktu_input', 'diperbarui_pada'], null),
      ket: pick(r, ['keterangan', 'keterangan_libur'], null)
    };
  }
  function fmtTime(v) {
    if (!v) return '—';
    var m = String(v).match(/(\d{1,2})[:.](\d{2})/);
    if (!m) return esc(v);
    return pad(+m[1]) + '.' + m[2] + ' WIB';
  }

  /* ---------- keadaan halaman ---------- */
  var S = {
    date: new Date(),
    ov: null,         // hasil ringkasan (setup, tahun ajaran, kesiapan)
    absRows: [],
    absLoaded: false
  };
  S.date.setHours(0, 0, 0, 0);

  function retryBox(title, text, id) {
    return '<strong>' + esc(title) + '</strong>' + esc(text) +
      '<br><button type="button" class="btn btn--outline" id="' + id + '">' + icon('refresh', 'ico--sm') + ' Coba lagi</button>';
  }

  /* =====================================================================
   * A. Ringkasan: setup, tahun ajaran aktif, kesiapan operasional
   * ===================================================================== */
  function loadOverview() {
    $('ov-error-a').hidden = true;
    $('ov-error-b').hidden = true;

    return Promise.allSettled([
      Api.get(E.tahunAjaran),
      Api.get(E.mapel),
      Api.get(E.guru),
      Api.get(E.kelas)
    ]).then(function (r) {
      var failed = r.some(function (x) { return x.status === 'rejected'; });
      if (failed) {
        var unauthorized = r.some(function (x) { return x.status === 'rejected' && x.reason && x.reason.status === 401; });
        if (unauthorized) return; // sudah diarahkan ke login
        var first = r.find(function (x) { return x.status === 'rejected'; }).reason;
        S.ov = null;
        showOverviewError(first && first.message ? first.message : 'Terjadi gangguan, coba lagi');
        return;
      }

      var ta = toList(r[0].value).map(normTahun);
      var mapelList = toList(r[1].value);
      var guruList = toList(r[2].value);
      var kelasAll = toList(r[3].value).map(normKelas);

      var aktif = ta.find(function (t) { return t.aktif; }) || null;
      var kelasAktif = aktif
        ? kelasAll.filter(function (k) { return k.tahunId === null || String(k.tahunId) === String(aktif.id); })
        : [];

      S.ov = {
        tahun: ta,
        aktif: aktif,
        mapelCount: toTotal(r[1].value, mapelList),
        guruCount: toTotal(r[2].value, guruList),
        kelas: kelasAktif,
        kelasTotal: kelasAll.length
      };
      renderSetup();
      renderTahun();
      renderKesiapan();
      renderAlerts();
    });
  }

  function showOverviewError(msg) {
    $('setup').hidden = true;
    ['a', 'b'].forEach(function (k) {
      var el = $('ov-error-' + k);
      el.hidden = false;
      el.innerHTML = '<div class="notice notice--info" style="margin:0 0 10px">' + esc(msg) + '</div>' +
        '<button type="button" class="btn btn--outline" data-retry-ov>' + icon('refresh', 'ico--sm') + ' Coba lagi</button>';
    });
    $('ta-name').textContent = '—';
    $('ta-sem').textContent = '—';
    $('ks-head').textContent = '—';
    $('ks-list').innerHTML = '';
    document.querySelectorAll('[data-retry-ov]').forEach(function (b) {
      b.addEventListener('click', function () { resetOverviewSkeleton(); loadOverview(); });
    });
  }
  function resetOverviewSkeleton() {
    $('ta-name').innerHTML = '<span class="skeleton skeleton--lg"></span>';
    $('ta-sem').innerHTML = '<span class="skeleton"></span>';
    $('ks-head').innerHTML = '<span class="skeleton"></span>';
    $('ks-list').innerHTML = '<li><span class="skeleton skeleton--row"></span></li><li><span class="skeleton skeleton--row"></span></li><li><span class="skeleton skeleton--row"></span></li>';
  }

  /* --- Status setup (5 langkah, sesuai gambar) --- */
  function renderSetup() {
    var o = S.ov;
    var kelasBelumWali = o.kelas.filter(function (k) { return !k.waliAktif; }).length;
    var steps = [
      { label: '1. Buat Mata Pelajaran',        done: o.mapelCount > 0,                                    href: 'mata-pelajaran' },
      { label: '2. Buat Tahun Ajaran',          done: o.tahun.length > 0,                                  href: 'tahun-ajaran' },
      { label: '3. Buat Guru & Akun',           done: o.guruCount > 0,                                     href: 'guru' },
      { label: '4. Buat Kelas & Tetapkan Wali', done: o.kelas.length > 0 && kelasBelumWali === 0,          href: 'kelas' },
      { label: '5. Aktifkan Tahun Ajaran',      done: !!o.aktif,                                           href: 'tahun-ajaran' }
    ];
    var done = steps.filter(function (s) { return s.done; }).length;
    var pct = Math.round(done / steps.length * 100);
    var next = steps.find(function (s) { return !s.done; });

    // Semua selesai: kartu prasyarat disembunyikan.
    $('setup').hidden = !next;
    if (!next) return;

    $('setup-count').textContent = done + ' dari ' + steps.length + ' selesai';
    $('setup-pct').textContent = pct + '% selesai';
    $('setup-fill').style.width = pct + '%';
    $('setup-bar').setAttribute('aria-valuenow', pct);
    $('setup-bar').setAttribute('aria-label', 'Kemajuan setup ' + pct + ' persen');
    $('setup-next').setAttribute('href', url(next.href));

    $('setup-steps').innerHTML = steps.map(function (s, i) {
      var cls = 'step' + (s.done ? ' is-done' : '') + (s === next ? ' is-current' : '');
      var mark = s.done ? icon('check') : (i + 1);
      var label = s.label.replace(/^\d+\.\s*/, '');
      return '<li><a class="' + cls + '" href="' + esc(url(s.href)) + '">' +
        '<span class="step__mark">' + mark + '</span><span>' + (i + 1) + '. ' + esc(label) + '</span></a></li>';
    }).join('');
  }

  /* --- Kartu tahun ajaran aktif --- */
  function renderTahun() {
    var a = S.ov.aktif;
    if (!a) {
      $('ta-name').textContent = 'Belum ada';
      $('ta-status').hidden = true;
      $('ta-sem').textContent = '—';
      $('btn-semester').hidden = true;
      return;
    }
    $('ta-name').textContent = a.nama;
    $('ta-status').hidden = false;
    var sem = a.semester === 'genap' ? 'Genap' : a.semester === 'ganjil' ? 'Ganjil' : '—';
    $('ta-sem').textContent = sem;
    // Tombol hanya tampil di tahun aktif yang semesternya ganjil (dokumen 01 bagian 3.4.5)
    $('btn-semester').hidden = a.semester !== 'ganjil';
  }

  /* --- Kartu kesiapan operasional --- */
  function renderKesiapan() {
    var o = S.ov;
    var rows = [];
    var tindakan = 0;

    if (o.kelas.length > 0) rows.push({ ok: true, text: o.kelas.length + ' kelas sudah dibuat' });
    else { rows.push({ ok: false, text: 'Belum ada kelas di tahun ajaran aktif', href: 'kelas' }); tindakan++; }

    var tanpaWali = o.kelas.filter(function (k) { return !k.waliAktif; });
    if (o.kelas.length > 0) {
      if (tanpaWali.length) { rows.push({ ok: false, text: tanpaWali.length + ' kelas belum memiliki wali aktif', href: 'kelas' }); tindakan++; }
      else rows.push({ ok: true, text: 'Semua kelas memiliki wali aktif' });
    }

    var berikutnya = o.tahun.some(function (t) { return !t.aktif; });
    if (berikutnya) rows.push({ ok: true, text: 'Tahun ajaran berikutnya sudah disiapkan' });
    else { rows.push({ ok: false, text: 'Tahun ajaran berikutnya belum disiapkan', href: 'kenaikan-kelas' }); tindakan++; }

    $('ks-head').textContent = tindakan ? 'Perlu ' + tindakan + ' tindakan' : 'Semua siap';
    $('ks-dot').className = 'dot' + (tindakan ? '' : ' dot--ok');
    $('ks-list').innerHTML = rows.map(function (r) {
      var ic = r.ok ? '<span class="ok">' + icon('check') + '</span>' : '<span class="warn">' + icon('alert') + '</span>';
      var txt = r.href ? '<a href="' + esc(url(r.href)) + '">' + esc(r.text) + '</a>' : esc(r.text);
      return '<li>' + ic + '<span>' + txt + '</span></li>';
    }).join('');
  }

  /* --- Peringatan (kotak kuning) --- */
  function renderAlerts() {
    var box = $('alerts');
    var out = [];

    // Wali berakun nonaktif (dari daftar kelas tahun aktif; cadangan dari tabel absensi)
    var nonaktif = [];
    if (S.ov) {
      S.ov.kelas.forEach(function (k) { if (k.wali && k.waliAktif === false) nonaktif.push(k.nama); });
    }
    if (!nonaktif.length) {
      S.absRows.forEach(function (r) { if (r.wali && r.waliAktif === false) nonaktif.push(r.kelas); });
    }
    nonaktif = nonaktif.filter(function (v, i, a) { return a.indexOf(v) === i; });
    if (nonaktif.length) {
      var many = nonaktif.length > 1;
      out.push({
        title: 'Perlu perhatian',
        text: (many ? 'Kelas ' + nonaktif.join(', ') + ' walinya' : 'Kelas ' + nonaktif[0] + ' walinya') +
          ' akun nonaktif. Kenaikan kelas berikutnya akan ditolak sebelum wali pengganti ditetapkan.'
      });
    }

    if (S.ov) {
      var kurang = [];
      if (S.ov.mapelCount === 0) kurang.push('Mata pelajaran belum ada. Tambahkan minimal 1 mapel sebelum input nilai.');
      if (S.ov.guruCount === 0) kurang.push('Guru belum ada. Tambahkan Guru agar bisa ditetapkan sebagai wali kelas.');
      if (kurang.length) out.push({ title: 'Data master belum lengkap', text: kurang.join(' ') });
    }

    box.innerHTML = out.map(function (a) {
      return '<div class="alert" role="status">' + icon('alert') + '<div><strong>' + esc(a.title) + '</strong><p>' + esc(a.text) + '</p></div></div>';
    }).join('');
    $('bell-dot').hidden = out.length === 0;
  }

  /* =====================================================================
   * B. Status absensi semua kelas
   * ===================================================================== */
  function isSunday(d) { return d.getDay() === 0; }

  function syncDateUi() {
    var today = new Date(); today.setHours(0, 0, 0, 0);
    var yest = new Date(today); yest.setDate(yest.getDate() - 1);
    $('abs-date').value = toISO(S.date);
    $('abs-date-label').textContent = fmtDate(S.date);
    $('btn-hariini').classList.toggle('is-active', toISO(S.date) === toISO(today));
    $('btn-kemarin').classList.toggle('is-active', toISO(S.date) === toISO(yest));
  }

  function dayWord() {
    var today = new Date(); today.setHours(0, 0, 0, 0);
    var yest = new Date(today); yest.setDate(yest.getDate() - 1);
    if (toISO(S.date) === toISO(today)) return 'hari ini';
    if (toISO(S.date) === toISO(yest)) return 'kemarin';
    return 'pada ' + fmtDate(S.date);
  }

  var absSeq = 0;
  function loadAbsensi() {
    var seq = ++absSeq;
    syncDateUi();
    $('abs-state').hidden = true;
    $('abs-summary').hidden = true;
    $('abs-table').hidden = false;
    $('abs-body').innerHTML = '<tr class="is-skeleton"><td colspan="4"><span class="skeleton skeleton--row"></span></td></tr>'.repeat(3);

    Api.get(E.absensi, { date: toISO(S.date) }).then(function (res) {
      if (seq !== absSeq) return; // permintaan lama diabaikan
      S.absRows = toList(res).map(normAbsensi);
      // Hari Minggu: semua kelas Libur (server juga menjamin; ini jaring pengaman tampilan)
      if (isSunday(S.date)) {
        S.absRows.forEach(function (r) { r.status = 'libur'; r.ket = r.ket || 'Hari Minggu'; });
      }
      S.absLoaded = true;
      renderAbsensi();
      renderAlerts();
    }).catch(function (err) {
      if (seq !== absSeq || (err && err.status === 401)) return;
      S.absRows = [];
      $('abs-table').hidden = true;
      var st = $('abs-state');
      st.hidden = false;
      st.innerHTML = retryBox('Gagal memuat status absensi', err && err.message ? err.message : 'Terjadi gangguan, coba lagi', 'abs-retry');
      $('abs-retry').addEventListener('click', loadAbsensi);
    });
  }

  function renderAbsensi() {
    var rows = S.absRows;
    var st = $('abs-state');

    if (!rows.length) {
      $('abs-table').hidden = true;
      $('abs-summary').hidden = true;
      st.hidden = false;
      st.innerHTML = '<strong>Belum ada kelas</strong>Kelas pada tahun ajaran aktif belum dibuat. Buat kelas lebih dulu di Data Master.<br>' +
        '<a class="btn btn--outline" href="' + esc(url('kelas')) + '">Buka Data Kelas</a>';
      return;
    }
    st.hidden = true;
    $('abs-table').hidden = false;

    // Ringkasan: libur tidak dihitung sebagai kelas yang wajib absen
    var libur = rows.filter(function (r) { return r.status === 'libur'; }).length;
    var wajib = rows.length - libur;
    var sudah = rows.filter(function (r) { return r.status === 'sudah'; }).length;
    var sum = $('abs-summary');
    sum.hidden = false;

    if (wajib === 0) {
      $('abs-sum-main').textContent = 'Semua kelas libur';
      $('abs-sum-sub').textContent = isSunday(S.date) ? 'Hari Minggu' : 'Tidak ada absensi yang perlu diisi';
      $('abs-fill').style.width = '0';
      $('abs-pct').textContent = 'Libur';
      $('abs-pct').className = 'pill';
      $('abs-pct').style.cssText = 'background:#ececea;color:#5d6668';
      $('abs-bar').hidden = true;
    } else {
      var pct = Math.round(sudah / wajib * 100);
      $('abs-bar').hidden = false;
      $('abs-sum-main').textContent = sudah + ' dari ' + wajib + ' kelas';
      $('abs-sum-sub').textContent = 'sudah mengisi absensi ' + dayWord();
      $('abs-fill').style.width = pct + '%';
      $('abs-bar').setAttribute('aria-valuenow', pct);
      $('abs-bar').setAttribute('aria-label', pct + ' persen kelas sudah mengisi absensi');
      $('abs-pct').textContent = pct + '% selesai';
      $('abs-pct').className = 'pill pill--green';
      $('abs-pct').style.cssText = '';
    }

    $('abs-body').innerHTML = rows.map(function (r) {
      var waliCell = r.wali
        ? (r.waliAktif === false ? esc(r.wali) + ' <span class="is-muted">(akun nonaktif)</span>' : esc(r.wali))
        : '<span class="is-muted">Wali nonaktif</span>';
      var label = r.status === 'sudah' ? 'Sudah' : r.status === 'libur' ? 'Libur' : 'Belum';
      var last = r.status === 'libur'
        ? esc(r.ket || 'Libur')
        : fmtTime(r.updated);
      if (r.status === 'libur' && r.ket && r.ket !== 'Hari Minggu') last = esc(r.ket);
      if (r.status === 'belum') last = '—';
      return '<tr><td>' + esc(r.kelas) + '</td><td>' + waliCell + '</td>' +
        '<td><span class="badge badge--' + r.status + '">' + label + '</span></td><td>' + last + '</td></tr>';
    }).join('');
  }

  /* =====================================================================
   * C. Ganti Semester (dokumen 01 bagian 3.6)
   * ===================================================================== */
  var dlg = $('dlg-semester');
  var chk = $('sem-confirm');
  var ok = $('sem-ok');
  var semBusy = false;

  function semMsg(text) { var m = $('sem-msg'); m.textContent = text || ''; m.hidden = !text; }

  $('btn-semester').addEventListener('click', function () {
    chk.checked = false; ok.disabled = true; semMsg('');
    if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
    chk.focus();
  });
  chk.addEventListener('change', function () { ok.disabled = !chk.checked || semBusy; });
  $('sem-cancel').addEventListener('click', function () { if (!semBusy) dlg.close(); });

  $('form-semester').addEventListener('submit', function (ev) {
    ev.preventDefault();
    if (semBusy || !chk.checked || !S.ov || !S.ov.aktif) return;
    semBusy = true; ok.disabled = true; ok.textContent = 'Memproses...'; semMsg('');

    Api.put(E.tahunAjaran + '/' + S.ov.aktif.id, { semester_aktif: 'genap', konfirmasi_semester: true })
      .then(function () {
        dlg.close();
        toast('Semester berhasil diganti ke Genap');
        resetOverviewSkeleton();
        return loadOverview();
      })
      .catch(function (err) {
        // 409/422 ditampilkan apa adanya dalam kotak informasi
        semMsg(err && err.message ? err.message : 'Terjadi gangguan, coba lagi');
      })
      .then(function () {
        semBusy = false; ok.textContent = 'Lanjutkan'; ok.disabled = !chk.checked;
      });
  });

  /* =====================================================================
   * D. Kontrol tanggal
   * ===================================================================== */
  $('btn-hariini').addEventListener('click', function () {
    S.date = new Date(); S.date.setHours(0, 0, 0, 0); loadAbsensi();
  });
  $('btn-kemarin').addEventListener('click', function () {
    S.date = new Date(); S.date.setHours(0, 0, 0, 0); S.date.setDate(S.date.getDate() - 1); loadAbsensi();
  });
  $('abs-date').addEventListener('change', function (ev) {
    if (!ev.target.value) return;
    S.date = fromISO(ev.target.value); loadAbsensi();
  });

  /* ---------- mulai ---------- */
  syncDateUi();
  loadOverview();
  loadAbsensi();
})();