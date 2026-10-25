// Uji lapisan JS PWA Guru tanpa browser dan tanpa backend (fetch dipalsukan).
//   cd pwa-guru && node --test tests/
'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const path = require('node:path');

// ---- lingkungan palsu ----
const penyimpanan = new Map();
globalThis.localStorage = {
  getItem: (k) => (penyimpanan.has(k) ? penyimpanan.get(k) : null),
  setItem: (k, v) => penyimpanan.set(k, String(v)),
  removeItem: (k) => penyimpanan.delete(k),
};
globalThis.SIKESA_CONFIG = { apiBase: 'http://x/api-base/', tokenKey: 'tk' };

let panggilan = [];
let antrean = [];
function balas(status, badan, header) {
  const h = Object.assign({ 'Content-Type': 'application/json' }, header || {});
  antrean.push(() => new Response(typeof badan === 'string' ? badan : JSON.stringify(badan), { status, headers: h }));
}
const ok = (data, message) => balas(200, { success: true, message: message || 'OK', data });
globalThis.fetch = async (url, init) => {
  panggilan.push({ url, metode: init.method, header: init.headers, body: init.body ? JSON.parse(init.body) : undefined });
  const n = antrean.shift();
  if (!n) throw new Error('fetch tanpa balasan terjadwal: ' + url);
  const r = n();
  if (r instanceof Error) throw r;
  return r;
};
function ulang() { panggilan = []; antrean = []; penyimpanan.clear(); }

const muat = (f) => require(path.join('..', 'assets', 'js', f));
['apiClient.js', 'auth.js', 'profil.js', 'referensi.js', 'absensi.js', 'nilai.js', 'jadwal.js', 'piket.js', 'disiplin.js', 'laporan.js'].forEach(muat);
const S = globalThis.SIKESA;

const gagal = async (p, status) => {
  await assert.rejects(p, (e) => { assert.equal(e.name, 'ApiError'); if (status !== undefined) assert.equal(e.status, status); return true; });
};

// ---- apiClient ----
test('token dikirim di header dan query dibangun tanpa nilai kosong', async () => {
  ulang(); S.api.simpanToken('abc');
  ok({ items: [] });
  await S.api.get('/api/guru/nilai', { id_mapel: 3, semester: '', id_siswa: null, page: 1 });
  assert.equal(panggilan[0].url, 'http://x/api-base/api/guru/nilai?id_mapel=3&page=1');
  assert.equal(panggilan[0].header.Authorization, 'Bearer abc');
});

test('401 menghapus token dan memberi tahu pendengar sesi berakhir', async () => {
  ulang(); S.api.simpanToken('abc');
  let diberi = 0; S.api.onSesiBerakhir(() => diberi++);
  balas(401, { success: false, message: 'Unauthorized.' });
  await gagal(S.api.get('/api/guru/me'), 401);
  assert.equal(S.api.ambilToken(), null);
  assert.equal(diberi, 1);
});

test('401 pada login tidak dianggap sesi berakhir', async () => {
  ulang(); let diberi = 0; S.api.onSesiBerakhir(() => diberi++);
  balas(401, { success: false, message: 'Username atau password salah' });
  await gagal(S.auth.masuk('a', 'b'), 401);
  assert.equal(diberi, 0);
});

test('jaringan putus menjadi ApiError status 0', async () => {
  ulang(); antrean.push(() => new Error('putus'));
  await gagal(S.api.get('/api/guru/me'), 0);
});

test('respons 422 membawa pesan dan errors per kolom', async () => {
  ulang(); balas(422, { success: false, message: 'Nilai salah', errors: { nilai: 'x' } });
  await assert.rejects(S.api.post('/api/guru/nilai', {}), (e) => e.status === 422 && e.message === 'Nilai salah' && e.errors.nilai === 'x');
});

// ---- auth & profil ----
test('login Guru menyimpan token; login ber-role admin ditolak dan token tidak disimpan', async () => {
  ulang(); ok({ token: 'T1', user: { id_user: 1, username: 'g', role: 'guru' } });
  const u = await S.auth.masuk(' g ', 'rahasia123');
  assert.equal(u.role, 'guru'); assert.equal(S.api.ambilToken(), 'T1');
  assert.equal(panggilan[0].body.username, 'g');
  assert.equal(panggilan[0].header.Authorization, undefined);

  ulang(); ok({ token: 'T2', user: { id_user: 2, username: 'a', role: 'admin' } });
  await gagal(S.auth.masuk('a', 'rahasia123'), 403);
  assert.equal(S.api.ambilToken(), null);
});

test('keluar selalu menghapus token walau server gagal', async () => {
  ulang(); S.api.simpanToken('abc'); antrean.push(() => new Error('putus'));
  await S.auth.keluar();
  assert.equal(S.api.ambilToken(), null);
});

test('adalahMasuk memeriksa exp token', () => {
  ulang();
  const b64 = (o) => Buffer.from(JSON.stringify(o)).toString('base64url');
  S.api.simpanToken('h.' + b64({ exp: Math.floor(Date.now() / 1000) + 3600 }) + '.s');
  assert.equal(S.auth.adalahMasuk(), true);
  S.api.simpanToken('h.' + b64({ exp: Math.floor(Date.now() / 1000) - 10 }) + '.s');
  assert.equal(S.auth.adalahMasuk(), false);
  S.api.hapusToken();
  assert.equal(S.auth.adalahMasuk(), false);
});

test('ganti password: validasi lokal, lalu token baru menggantikan token lama', async () => {
  ulang(); S.api.simpanToken('lama');
  await gagal(S.profil.gantiPassword('x', 'pendek', 'pendek'), 422);
  await gagal(S.profil.gantiPassword('x', 'passwordbaru', 'beda'), 422);
  assert.equal(panggilan.length, 0);
  ok({ token: 'BARU', user: {} });
  await S.profil.gantiPassword('lama123', 'passwordbaru', 'passwordbaru');
  assert.equal(S.api.ambilToken(), 'BARU');
  assert.deepEqual(panggilan[0].body, { password_lama: 'lama123', password_baru: 'passwordbaru' });
});

// ---- absensi ----
test('hari Minggu terdeteksi; Sabtu tidak', () => {
  assert.equal(S.absensi.adalahMinggu('2026-10-04'), true);
  assert.equal(S.absensi.adalahMinggu('2026-10-10'), false);
});

test('absensi.simpan: siswa baru satu POST, perubahan PUT, yang sama dilewati; tanpa id_kelas', async () => {
  ulang(); S.api.simpanToken('t');
  const awal = [
    { id_siswa: 1, id_absensi: null, status: null },
    { id_siswa: 2, id_absensi: 20, status: 'hadir' },
    { id_siswa: 3, id_absensi: 30, status: 'hadir' },
    { id_siswa: 4, id_absensi: null, status: null },
  ];
  ok({ jumlah: 2 }, 'Absensi berhasil disimpan.'); ok({});
  const h = await S.absensi.simpan('2026-10-05', awal, { 1: 'hadir', 2: 'sakit', 3: 'hadir', 4: 'izin' });
  assert.deepEqual(h, { baru: 2, diperbarui: 1, tanpaPerubahan: 1, gagal: [] });
  assert.equal(panggilan.length, 2);
  assert.equal(panggilan[0].metode, 'POST');
  assert.deepEqual(panggilan[0].body, { tanggal: '2026-10-05', items: [{ id_siswa: 1, status: 'hadir' }, { id_siswa: 4, status: 'izin' }] });
  assert.equal(panggilan[1].url, 'http://x/api-base/api/guru/absensi/20');
  assert.deepEqual(panggilan[1].body, { status: 'sakit' });
  assert.ok(!JSON.stringify(panggilan).includes('id_kelas'));
});

test('absensi.simpan: kegagalan PUT dikumpulkan; POST ditolak melempar galat', async () => {
  ulang(); S.api.simpanToken('t');
  const awal = [{ id_siswa: 2, id_absensi: 20, status: 'hadir' }, { id_siswa: 3, id_absensi: 30, status: 'hadir' }];
  balas(422, { success: false, message: 'Siswa bukan siswa aktif' }); ok({});
  const h = await S.absensi.simpan('2026-10-05', awal, { 2: 'sakit', 3: 'izin' });
  assert.equal(h.diperbarui, 1); assert.deepEqual(h.gagal, [{ id_siswa: 2, pesan: 'Siswa bukan siswa aktif' }]);

  ulang(); S.api.simpanToken('t');
  balas(422, { success: false, message: 'Tanggal ini hari libur' });
  await gagal(S.absensi.simpan('2026-10-04', [{ id_siswa: 1, id_absensi: null, status: null }], { 1: 'hadir' }), 422);
});

test('absensi.simpan: status atau siswa tidak valid ditolak sebelum request', async () => {
  ulang();
  await gagal(S.absensi.simpan('2026-10-05', [{ id_siswa: 1, id_absensi: null }], { 1: 'bolos' }), 422);
  await gagal(S.absensi.simpan('2026-10-05', [{ id_siswa: 1, id_absensi: null }], { 99: 'hadir' }), 422);
  assert.equal(panggilan.length, 0);
});

test('ringkasan dan semuaHadir', () => {
  const p = S.absensi.semuaHadir([{ id_siswa: 1 }, { id_siswa: 2 }, { id_siswa: 3 }]);
  assert.deepEqual(p, { 1: 'hadir', 2: 'hadir', 3: 'hadir' });
  p[2] = 'sakit';
  assert.deepEqual(S.absensi.ringkasan(p, 4), { hadir: 2, sakit: 1, izin: 0, alpa: 0, belumDiisi: 1 });
});

// ---- nilai ----
test('nilai.buat: ASTS tanpa nomor_uh, UH wajib nomor, rentang 0-100', async () => {
  ulang(); S.api.simpanToken('t'); ok({ id_nilai: 1 });
  await S.nilai.buat({ id_siswa: 1, id_mapel: 3, semester: 'ganjil', jenis: 'ASTS', nomor_uh: 5, nilai: 80 });
  assert.equal('nomor_uh' in panggilan[0].body, false);
  await gagal(S.nilai.buat({ id_siswa: 1, id_mapel: 3, semester: 'ganjil', jenis: 'UH', nilai: 80 }), 422);
  await gagal(S.nilai.buat({ id_siswa: 1, id_mapel: 3, semester: 'ganjil', jenis: 'UH', nomor_uh: 1, nilai: 101 }), 422);
  await gagal(S.nilai.buat({ id_siswa: 1, id_mapel: 3, semester: 'ganjil', jenis: 'UH', nomor_uh: 1, nilai: -1 }), 422);
  await gagal(S.nilai.buat({ id_siswa: 1, id_mapel: 3, semester: 'ganjil', jenis: 'UH', nomor_uh: 1, nilai: 87.5 }), 422);
  assert.equal(panggilan.length, 1);
});

test('nilai.semua menelusuri halaman; bisaDihapus hanya UH; rekap dan nomor berikutnya', async () => {
  ulang(); S.api.simpanToken('t');
  const baris = (id, sid, nama, jenis, no, n) => ({ id_nilai: id, id_siswa: sid, nama_siswa: nama, jenis, nomor_uh: no, nilai: n });
  const h1 = [baris(1, 1, 'A', 'UH', 1, 80), baris(2, 1, 'A', 'UH', 2, 90)];
  const h2 = [baris(3, 1, 'A', 'ASTS', null, 85), baris(4, 2, 'B', 'UH', 1, 70)];
  ok({ items: h1, total: 4 }); ok({ items: h2, total: 4 });
  const semua = await S.nilai.semua({ id_mapel: 3, semester: 'ganjil' });
  assert.equal(semua.length, 4); assert.equal(panggilan.length, 2);
  assert.match(panggilan[1].url, /page=2/); assert.match(panggilan[1].url, /per_page=200/);

  assert.equal(S.nilai.bisaDihapus(semua[0]), true);
  assert.equal(S.nilai.bisaDihapus(semua[2]), false);
  assert.equal(S.nilai.nomorUhBerikutnya(semua), 3);
  const r = S.nilai.rekapPerSiswa(semua);
  assert.deepEqual(r.kolomUh, [1, 2]);
  assert.equal(r.baris[0].uh[2].nilai, 90); assert.equal(r.baris[0].asts.nilai, 85); assert.equal(r.baris[0].asas, null);
  assert.equal(r.baris[1].nama_siswa, 'B');
});

// ---- jadwal, piket, disiplin ----
test('jadwal: jam selesai harus setelah mulai; perHari mengurutkan jam', async () => {
  ulang();
  await gagal(S.jadwal.buat({ semester: 'ganjil', id_mapel: 1, hari: 'senin', jam_mulai: '08:00', jam_selesai: '07:00' }), 422);
  await gagal(S.jadwal.buat({ semester: 'ganjil', id_mapel: 1, hari: 'minggu', jam_mulai: '07:00', jam_selesai: '08:00' }), 422);
  assert.equal(panggilan.length, 0);
  const h = S.jadwal.perHari([{ hari: 'senin', jam_mulai: '09:00' }, { hari: 'senin', jam_mulai: '07:00' }, { hari: 'sabtu', jam_mulai: '07:00' }]);
  assert.deepEqual(h.senin.map((x) => x.jam_mulai), ['07:00', '09:00']);
  assert.deepEqual(h.selasa, []); assert.equal(h.sabtu.length, 1);
  S.api.simpanToken('t'); ok({ id_jadwal: 1 });
  await S.jadwal.buat({ semester: 'ganjil', id_mapel: 1, hari: 'senin', jam_mulai: '07:00', jam_selesai: '07:35' });
  assert.equal(panggilan[0].metode, 'POST');
});

test('piket.perHari menyediakan enam hari', () => {
  const h = S.piket.perHari([{ hari: 'rabu', id_piket: 1 }]);
  assert.deepEqual(Object.keys(h), ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
  assert.equal(h.rabu.id_piket, 1); assert.equal(h.senin, null);
});

test('disiplin: tidak ada fungsi hapus; validasi lokal', async () => {
  ulang();
  assert.equal(S.disiplin.hapus, undefined);
  await gagal(S.disiplin.buat({ id_siswa: 1, tanggal: '2026-10-05', jenis_pelanggaran: '', catatan: 'x' }), 422);
  assert.equal(panggilan.length, 0);
  S.api.simpanToken('t'); ok({ id_catatan: 1 });
  await S.disiplin.ubah(7, { tanggal: '2026-10-05', jenis_pelanggaran: 'Terlambat', catatan: 'Datang 07.30' });
  assert.equal(panggilan[0].metode, 'PUT'); assert.equal(panggilan[0].url, 'http://x/api-base/api/guru/disiplin/7');
});

// ---- laporan ----
test('laporan.unduh membaca nama berkas; galat JSON tetap menjadi ApiError', async () => {
  ulang(); S.api.simpanToken('t');
  balas(200, 'PKxx', { 'Content-Type': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Content-Disposition': 'attachment; filename="laporan-ganjil-2026-2027-3A.xlsx"' });
  const r = await S.laporan.unduh({ semester: 'ganjil', tahunAjaran: 1, jenis: 'semua' });
  assert.equal(r.namaFile, 'laporan-ganjil-2026-2027-3A.xlsx');
  assert.equal(r.blob.size, 4);
  assert.match(panggilan[0].url, /laporan\/excel\?semester=ganjil&tahun_ajaran=1&jenis=semua$/);
  assert.equal(panggilan[0].header.Authorization, 'Bearer t');

  balas(403, { success: false, message: 'Guru hanya dapat melihat laporan tahun ajaran aktif.' });
  await gagal(S.laporan.unduh({ semester: 'ganjil', tahunAjaran: 9 }), 403);
  await gagal(S.laporan.preview({ semester: 'x', tahunAjaran: 1 }), 422);
});

test('referensi.mapel mengambil items', async () => {
  ulang(); S.api.simpanToken('t'); ok({ items: [{ id_mapel: 1, nama_mapel: 'IPA' }], total: 1 });
  const m = await S.referensi.mapel();
  assert.equal(m[0].nama_mapel, 'IPA'); assert.match(panggilan[0].url, /\/api\/guru\/mapel\?per_page=200$/);
});
