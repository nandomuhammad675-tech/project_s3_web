<?php
/**
 * Penghubung Website -> Backend SI-KESA (GET /api/public/*, tanpa token).
 * Pola: API gagal dijangkau => data.php lama dipakai (fallback; galeri, ekstrakurikuler, guru). API berhasil tetapi kosong => tampil kosong.
 * Kegiatan & Prestasi (event) tanpa data cadangan: API gagal => null => halaman menampilkan "gagal dimuat" + Coba Lagi.
 * Alamat API: environment SIKESA_API_URL, atau ubah nilai bawaan di bawah.
 */
if (defined('SIKESA_API_LOADED')) { return; }
define('SIKESA_API_LOADED', true);

function sikesa_api_base(): string
{
    $u = getenv('SIKESA_API_URL') ?: 'http://localhost/si-kesa/backend/public';
    return rtrim($u, '/');
}

/** @return mixed data dari respons sukses, atau null bila gagal/tidak terjangkau. */
function sikesa_api(string $path, array $query = [])
{
    static $cache = [];
    $url = sikesa_api_base() . '/api/public/' . ltrim($path, '/') . ($query ? '?' . http_build_query($query) : '');
    if (array_key_exists($url, $cache)) { return $cache[$url]; }
    $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true, 'header' => "Accept: application/json\r\n"]]);
    $raw = @file_get_contents($url, false, $ctx);
    $j = $raw === false ? null : json_decode($raw, true);
    return $cache[$url] = (is_array($j) && !empty($j['success'])) ? ($j['data'] ?? null) : null;
}

/** Path 'uploads/..' -> URL backend; URL http(s) apa adanya; selain itu aset lokal website. */
function sikesa_img(?string $p): string
{
    $p = (string) $p;
    if ($p === '') { return asset('img/home.jpg'); }
    if (preg_match('#^https?://#i', $p)) { return $p; }
    if (strpos($p, 'uploads/') === 0) { return sikesa_api_base() . '/' . $p; }
    return base_url($p);
}

function sikesa_slug(string $s): string
{
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-') ?: 'ekskul';
}

/** Profil guru: yang berjabatan "Kepala ..." menjadi kepsek, sisanya daftar guru. */
function sikesa_guru(array $data): array
{
    $rows = sikesa_api('guru');
    if ($rows === null) { return $data; }
    $kepsek = null; $guru = [];
    foreach ($rows as $r) {
        // Foto kosong => gambar bawaan (siluet). Email dan telepon memang tidak ada di API publik.
        // NIP sengaja tidak ditampilkan di website. 'ekskul' = nama ekstrakurikuler yang diajar (field opsional dari API; kosong bila tidak mengajar ekstra).
        $ekskul = [];
        foreach ((array) ($r['ekskul'] ?? []) as $x) {
            $n = trim((string) (is_array($x) ? ($x['nama_ekskul'] ?? '') : $x));
            if ($n !== '') { $ekskul[] = $n; }
        }
        $item = ['nama' => (string) $r['nama_guru'], 'ekskul' => $ekskul,
                 'jabatan' => trim((string) ($r['jabatan'] ?? '')),
                 'foto' => $r['foto_profil'] ?: 'assets/img/guru-default.svg'];
        if ($kepsek === null && preg_match('/kepala/i', (string) ($r['jabatan'] ?? ''))) {
            $kepsek = $item;
        } else {
            $guru[] = $item;
        }
    }
    $data['kepsek'] = $kepsek;   // API terjangkau: null bila belum ada Guru berjabatan "Kepala ..." di database
    $data['guru'] = $guru;
    return $data;
}

/** Galeri mandiri. API tidak punya tanggal => 'tanggal' null (view menyembunyikan). */
function sikesa_galeri(array $fallback): array
{
    $d = sikesa_api('galeri', ['per_page' => 100]);
    if ($d === null) { return $fallback; }
    return array_map(static fn($g) => [
        'judul' => (string) ($g['keterangan'] ?: 'Dokumentasi kegiatan'),
        'tanggal' => null,
        'foto' => $g['file_gambar'],
    ], $d['items']);
}

/** Respons berhalaman -> ['items' => [...], 'total' => n, 'page' => n, 'per_page' => n]; null bila gagal. */
function sikesa_halaman(string $path, array $query): ?array
{
    $d = sikesa_api($path, $query);
    if (!is_array($d) || !isset($d['items']) || !is_array($d['items'])) { return null; }
    return ['items' => $d['items'], 'total' => (int) ($d['total'] ?? count($d['items'])),
            'page' => (int) ($d['page'] ?? 1), 'per_page' => (int) ($d['per_page'] ?? count($d['items']))];
}

/** Kategori event (urutan tampil) = nilai ?kategori= pada /api/public/event. */
function sikesa_kategori(): array
{
    return ['kegiatan' => 'Kegiatan', 'event' => 'Event', 'prestasi' => 'Prestasi', 'kolaborasi' => 'Kolaborasi'];
}

/** Satu event dari API -> bentuk tampilan. slug = "e<id_event>". */
function sikesa_event_norm(array $e): array
{
    $desk = trim((string) ($e['deskripsi'] ?? ''));
    $isi  = $desk === '' ? [] : array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', $desk))));
    $galeri = array_values(array_filter(array_map(static fn($g) => (string) ($g['file_gambar'] ?? ''), $e['galeri'] ?? [])));
    return [
        'id'       => (int) $e['id_event'],
        'slug'     => 'e' . (int) $e['id_event'],
        'judul'    => (string) $e['nama_event'],
        'ringkas'  => mb_strimwidth(preg_replace('/\s+/', ' ', $desk), 0, 200, '…', 'UTF-8'),
        'tanggal'  => (string) ($e['tanggal'] ?? ''),
        'kategori' => (string) ($e['kategori'] ?? ''),
        'foto'     => (string) (($e['banner'] ?? '') ?: ($galeri[0] ?? '')),
        'galeri'   => $galeri,
        'isi'      => $isi,
    ];
}

/** Event tampil (status_tampil = 1), terbaru di atas; urutan & halaman dari server. null = API gagal. */
function sikesa_event(?string $kategori = null, int $page = 1, int $perPage = 6): ?array
{
    $q = ['page' => $page, 'per_page' => $perPage];
    if ($kategori) { $q['kategori'] = $kategori; }
    $h = sikesa_halaman('event', $q);
    if ($h === null) { return null; }
    $h['items'] = array_map('sikesa_event_norm', $h['items']);
    return $h;
}

/** Cari satu event menurut slug "e<id>". Hasil: array event | false (tidak ada) | null (API gagal). */
function sikesa_event_cari(string $slug)
{
    if (!preg_match('/^e(\d{1,9})$/', $slug, $m)) { return false; }
    $id = (int) $m[1];
    for ($page = 1; $page <= 5; $page++) {
        $h = sikesa_event(null, $page, 100);
        if ($h === null) { return null; }
        foreach ($h['items'] as $e) { if ($e['id'] === $id) { return $e; } }
        if ($page * 100 >= $h['total']) { break; }
    }
    return false;
}

/** Hybrid ekskul: daftar+nama+deskripsi+foto dari API; konten detail (tentang/kegiatan/materi/manfaat) dari data.php. */
function sikesa_ekskul(array $local): array
{
    $rows = sikesa_api('ekstrakurikuler');
    if ($rows === null) { return $local; }
    $peta = [];
    foreach ($local as $l) { $peta[strtolower(trim($l['nama']))] = $l; }
    $out = [];
    foreach ($rows as $r) {
        $nama = (string) $r['nama_ekskul'];
        $desk = trim((string) ($r['deskripsi'] ?? ''));
        $base = $peta[strtolower(trim($nama))] ?? [
            'slug' => sikesa_slug($nama), 'tentang' => $desk,
            'kegiatan' => [], 'materi_judul' => 'Materi yang Dipelajari', 'materi' => [], 'manfaat' => [],
        ];
        $base['nama'] = $nama;
        $base['ringkas'] = $desk !== '' ? $desk : ($base['ringkas'] ?? '');
        $base['foto'] = $r['ikon'] ?: ($base['foto'] ?? '');
        if (empty($base['tentang'])) { $base['tentang'] = $desk; }
        $out[] = $base;
    }
    return $out;
}

/** ID video YouTube dari pengaturan video_profil_url; null bila kosong/bukan YouTube. */
function sikesa_youtube_id(): ?string
{
    $p = sikesa_api('pengaturan');
    $u = (string) ($p['video_profil_url'] ?? '');
    if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/))([A-Za-z0-9_-]{11})#', $u, $m)) {
        return $m[1];
    }
    return null;
}


/** Pengaturan publik (hanya ppdb_form_url dan video_profil_url); null bila API tidak terjangkau. */
function sikesa_pengaturan(): ?array
{
    $p = sikesa_api('pengaturan');
    return is_array($p) ? $p : null;
}

/**
 * Status formulir PPDB dari database (pengaturan_sistem.ppdb_form_url):
 *   ['status' => 'ada',    'url' => ..., 'embed' => ...]  URL terisi
 *   ['status' => 'kosong']                                  URL belum diisi Admin
 *   ['status' => 'gagal']                                   API tidak terjangkau
 * Jawaban formulir tetap di Google Drive sekolah; SI-KESA tidak menerima/menyimpannya.
 */
function sikesa_ppdb(): array
{
    $p = sikesa_pengaturan();
    if ($p === null) { return ['status' => 'gagal']; }
    $u = trim((string) ($p['ppdb_form_url'] ?? ''));
    if ($u === '' || !preg_match('#^https?://#i', $u)) { return ['status' => 'kosong']; }
    $embed = null;
    if (preg_match('#^https://docs\.google\.com/forms/#i', $u)) {
        $embed = $u . (strpos($u, '?') === false ? '?' : '&') . 'embedded=true';
    }
    return ['status' => 'ada', 'url' => $u, 'embed' => $embed];
}

/** Enam program pembiasaan (Senin-Sabtu); null bila API tidak terjangkau. */
function sikesa_program(): ?array
{
    $rows = sikesa_api('program-pembiasaan');
    return is_array($rows) ? $rows : null;
}

/** Karya mading yang tampil (status_tampil = 1) + info halaman; null bila API tidak terjangkau. */
function sikesa_mading(int $page = 1, int $perPage = 9, ?string $kategori = null): ?array
{
    $q = ['page' => $page, 'per_page' => $perPage];
    if ($kategori !== null && $kategori !== '') { $q['kategori'] = $kategori; }
    $d = sikesa_api('mading', $q);
    return is_array($d) && isset($d['items']) ? $d : null;
}


/** Kepala Sekolah dari database (Guru berjabatan "Kepala ..."). null = belum ada / API tidak terjangkau. */
function sikesa_kepsek(): ?array
{
    $d = sikesa_guru(['kepsek' => null, 'guru' => []]);
    return $d['kepsek'] ?? null;
}
