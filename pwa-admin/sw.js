/* SI-KESA PWA Admin | Service Worker
 * - File statis (css/js/ikon): cache dulu, perbarui di latar belakang.
 * - Halaman: jaringan dulu. Jika offline, tampilkan cadangan.
 * - /api/* TIDAK PERNAH di-cache (data selalu dari server). */
const VERSION = 'sikesa-admin-v5';
const SHELL = [
  'assets/css/style.css',
  'assets/css/sidebar.css',
  'assets/css/table.css',
  'assets/css/form.css',
  'assets/css/master.css',
  'assets/js/apiClient.js',
  'assets/js/auth.js',
  'assets/js/app.js',
  'assets/js/dashboard.js',
  'assets/js/mata-pelajaran.js',
  'assets/js/tahun-ajaran.js',
  'assets/js/guru.js',
  'assets/js/siswa.js',
  'assets/js/kelas.js',
  'assets/icons/icon-192.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(VERSION).then((c) => c.addAll(SHELL.map((p) => new URL(p, self.registration.scope).href))).catch(() => {})
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (url.pathname.includes('/api/')) return;

  if (url.pathname.includes('/assets/')) {
    event.respondWith(
      caches.match(req).then((hit) => {
        const fresh = fetch(req).then((res) => {
          if (res.ok) caches.open(VERSION).then((c) => c.put(req, res.clone()));
          return res;
        }).catch(() => hit);
        return hit || fresh;
      })
    );
    return;
  }

  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(() =>
        new Response(
          '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' +
          '<title>Tidak ada koneksi</title><body style="font-family:system-ui;padding:2rem;max-width:32rem;margin:auto">' +
          '<h1>Tidak ada koneksi</h1><p>Terjadi gangguan, coba lagi setelah internet tersambung.</p>' +
          '<button onclick="location.reload()" style="padding:.6rem 1rem">Coba lagi</button></body>',
          { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        )
      )
    );
  }
});