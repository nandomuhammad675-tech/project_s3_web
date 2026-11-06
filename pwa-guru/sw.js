/* SI-KESA PWA Guru | Service Worker
 * - Berkas statis (assets/): cache dulu, perbarui di latar belakang.
 * - Halaman: jaringan dulu; offline => halaman cadangan.
 * - /api/* TIDAK PERNAH di-cache. */
const VERSION = 'sikesa-guru-v1';

self.addEventListener('install', (event) => { self.skipWaiting(); });

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

  if (url.pathname.includes('/assets/') && !url.pathname.includes('/vidio/')) {
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
