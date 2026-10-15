<?php
/**
 * Penghubung Website -> Backend SI-KESA (GET /api/public/*, tanpa token).
 * Pola: API gagal dijangkau => data.php lama dipakai (fallback). API berhasil tetapi kosong => tampil kosong.
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
        $item = ['nama' => (string) $r['nama_guru'], 'nip' => trim((string) ($r['nip'] ?? '')),
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

/** Event -> daftar berita. Tanggal event boleh kosong (view menyembunyikannya). */
function sikesa_berita(array $fallback): array
{
    $d = sikesa_api('event', ['per_page' => 100]);
    if ($d === null) { return $fallback; }
    $out = [];
    foreach ($d['items'] as $e) {
        $desk = trim((string) ($e['deskripsi'] ?? ''));
        $isi = $desk === '' ? [] : array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', $desk))));
        $out[] = [
            'slug' => 'e' . $e['id_event'],
            'judul' => (string) $e['nama_event'],
            'ringkas' => mb_strimwidth(preg_replace('/\s+/', ' ', $desk), 0, 220, '…', 'UTF-8'),
            'tanggal' => $e['tanggal'] ?? '',
            'kategori' => (string) ($e['kategori'] ?? ''),
            'galeri' => array_values(array_filter(array_map(static fn($g) => $g['file_gambar'] ?? '', $e['galeri'] ?? []))),
            'foto' => $e['banner'] ?: ($e['galeri'][0]['file_gambar'] ?? ''),
            'isi' => $isi,
        ];
    }
    return $out;
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
function sikesa_mading(int $page = 1, int $perPage = 9): ?array
{
    $d = sikesa_api('mading', ['page' => $page, 'per_page' => $perPage]);
    return is_array($d) && isset($d['items']) ? $d : null;
}


/** Kepala Sekolah dari database (Guru berjabatan "Kepala ..."). null = belum ada / API tidak terjangkau. */
function sikesa_kepsek(): ?array
{
    $d = sikesa_guru(['kepsek' => null, 'guru' => []]);
    return $d['kepsek'] ?? null;
}
