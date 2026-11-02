<?php
/**
 * Halaman Formulir Pendaftaran PPDB - SD Negeri Kedungsari 1
 * SATU FILE. Letakkan di: website/views/ppdb/formulir.php
 * Dibuka lewat: ?page=ppdb&step=formulir  (diteruskan oleh ppdb/index.php)
 *
 * Alur: isi formulir -> validasi -> disimpan -> halaman "Berhasil" (bisa dicetak / disimpan PDF)
 * Data tersimpan di: website/storage/ppdb.csv  (di luar folder public, tidak bisa dibuka dari browser)
 *
 * Gambar latar (opsional, taruh di folder assets/):
 *   - assets/bg-formulir.png  -> ilustrasi daun untuk latar halaman
 *   - assets/bg-navbar.png    -> tekstur latar navbar & footer
 * Jika file tidak ada, tampilan memakai latar daun bawaan (SVG) sehingga tetap rapi.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== Fungsi bantu (dilewati jika sudah ada) =====
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        static $base = null;
        if ($base === null) {
            $base = '';
            $root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ''), '/');
            foreach ([__DIR__ . '/../../public', __DIR__ . '/public', __DIR__, __DIR__ . '/..'] as $dir) {
                $assets = realpath($dir . '/assets');
                if ($assets) {
                    $pub = str_replace('\\', '/', dirname($assets));
                    if ($root !== '' && stripos($pub, $root) === 0) {
                        $base = substr($pub, strlen($root)) . '/';
                        break;
                    }
                }
            }
            if ($base === '') {
                $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
            }
        }
        return $base . ltrim($path, '/');
    }
}
if (!function_exists('esc')) {
    function esc($text): string {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('page_url')) {
    function page_url(string $key): string {
        static $map = [
            'home'    => '',
            'profil'  => '?page=guru',
            'berita'  => '?page=kegiatan',
            'ekstra'  => '?page=ekstrakurikuler',
            'galeri'  => '?page=galeri',
            'kontak'  => '?page=kontak',
            'ppdb'    => '?page=ppdb',
        ];
        return base_url($map[$key] ?? $key);
    }
}
if (!function_exists('tanggal_id')) {
    function tanggal_id(string $ymd): string {
        static $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                         'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $t = strtotime($ymd);
        return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
    }
}

$current = 'ppdb';
$formulirUrl = page_url('ppdb') . '&step=formulir';

// ===== Penyimpanan data (CSV di website/storage/ppdb.csv) =====
const PPDB_KOLOM = ['kode', 'waktu', 'nama', 'nisn', 'no_kk', 'nik', 'tempat_lahir', 'tanggal_lahir', 'nama_ortu', 'alamat', 'whatsapp'];

function ppdb_file(): string {
    return dirname(__DIR__, 2) . '/storage/ppdb.csv';
}
// Cegah "CSV injection" jika file dibuka di Excel
function ppdb_csv_aman($v): string {
    $v = (string) $v;
    return ($v !== '' && strpos("=+-@\t\r", $v[0]) !== false) ? "'" . $v : $v;
}
function ppdb_simpan(array $row): bool {
    $file = ppdb_file();
    $dir  = dirname($file);
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        return false;
    }
    $baru = !file_exists($file);
    $fh = @fopen($file, 'ab');
    if (!$fh) {
        return false;
    }
    flock($fh, LOCK_EX);
    if ($baru) {
        fputcsv($fh, PPDB_KOLOM, ',', '"', '\\');
    }
    fputcsv($fh, array_map('ppdb_csv_aman', array_values($row)), ',', '"', '\\');
    flock($fh, LOCK_UN);
    fclose($fh);
    return true;
}
function ppdb_nik_sudah_ada(string $nik): bool {
    $file = ppdb_file();
    if (!is_file($file) || !($fh = @fopen($file, 'rb'))) {
        return false;
    }
    $idx = array_search('nik', PPDB_KOLOM, true);
    $ada = false;
    fgetcsv($fh, 0, ',', '"', '\\'); // lewati baris judul
    while (($r = fgetcsv($fh, 0, ',', '"', '\\')) !== false) {
        if (($r[$idx] ?? '') === $nik) { $ada = true; break; }
    }
    fclose($fh);
    return $ada;
}
function ppdb_samarkan(string $s): string {
    $n = strlen($s);
    return $n > 4 ? str_repeat('*', $n - 4) . substr($s, -4) : $s;
}

// ===== Token keamanan formulir (CSRF) =====
if (empty($_SESSION['ppdb_csrf'])) {
    $_SESSION['ppdb_csrf'] = bin2hex(random_bytes(32));
}

// ===== Proses formulir =====
$errors = [];
$old    = array_fill_keys(['nama', 'nisn', 'no_kk', 'nik', 'tempat_lahir', 'tanggal_lahir', 'nama_ortu', 'alamat', 'whatsapp'], '');
$berhasil = null;

if (($_GET['status'] ?? '') === 'berhasil' && !empty($_SESSION['ppdb_berhasil'])) {
    $berhasil = $_SESSION['ppdb_berhasil'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim(preg_replace('/\s+/u', ' ', (string) ($_POST[$k] ?? '')));
    }

    if (!hash_equals($_SESSION['ppdb_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $errors['_form'] = 'Sesi formulir sudah berakhir. Muat ulang halaman lalu isi kembali.';
    } else {
        // Nama calon peserta didik
        if (!preg_match('/^[\p{L}\s.\'-]{3,100}$/u', $old['nama'])) {
            $errors['nama'] = 'Isi nama lengkap (3-100 huruf) sesuai akta kelahiran.';
        }
        // NISN: opsional, jika diisi harus 10 digit
        if ($old['nisn'] !== '' && !preg_match('/^\d{10}$/', $old['nisn'])) {
            $errors['nisn'] = 'NISN harus 10 digit angka. Kosongkan jika belum punya.';
        }
        // No KK & NIK: wajib 16 digit angka
        if (!preg_match('/^\d{16}$/', $old['no_kk'])) {
            $errors['no_kk'] = 'Nomor KK harus tepat 16 digit angka, tanpa spasi atau karakter lain.';
        }
        if (!preg_match('/^\d{16}$/', $old['nik'])) {
            $errors['nik'] = 'NIK harus tepat 16 digit angka, tanpa spasi atau karakter lain.';
        } elseif (ppdb_nik_sudah_ada($old['nik'])) {
            $errors['nik'] = 'NIK ini sudah terdaftar. Hubungi sekolah jika ada kesalahan.';
        }
        // Tempat & tanggal lahir
        if (mb_strlen($old['tempat_lahir']) < 2 || mb_strlen($old['tempat_lahir']) > 60) {
            $errors['tempat_lahir'] = 'Isi kota/kabupaten tempat lahir.';
        }
        $tgl = DateTime::createFromFormat('Y-m-d', $old['tanggal_lahir']);
        if (!$tgl || $tgl->format('Y-m-d') !== $old['tanggal_lahir'] || $old['tanggal_lahir'] > date('Y-m-d')) {
            $errors['tanggal_lahir'] = 'Isi tanggal lahir yang valid.';
        }
        // Orang tua, alamat
        if (mb_strlen($old['nama_ortu']) < 3 || mb_strlen($old['nama_ortu']) > 100) {
            $errors['nama_ortu'] = 'Isi nama lengkap orang tua / wali.';
        }
        if (mb_strlen($old['alamat']) < 5 || mb_strlen($old['alamat']) > 255) {
            $errors['alamat'] = 'Isi alamat lengkap tempat tinggal.';
        }
        // WhatsApp: terima 08xx / +62xx / 62xx, simpan sebagai 08xx
        $wa = preg_replace('/[\s\-().]/', '', $old['whatsapp']);
        if (strpos($wa, '+62') === 0)      { $wa = '0' . substr($wa, 3); }
        elseif (strpos($wa, '62') === 0)   { $wa = '0' . substr($wa, 2); }
        if (!preg_match('/^08\d{8,11}$/', $wa)) {
            $errors['whatsapp'] = 'Isi nomor WhatsApp yang valid, contoh: 081234567890.';
        } else {
            $old['whatsapp'] = $wa;
        }
    }

    if (!$errors) {
        $data = [
            'kode'          => 'PPDB-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3))),
            'waktu'         => date('Y-m-d H:i:s'),
            'nama'          => $old['nama'],
            'nisn'          => $old['nisn'],
            'no_kk'         => $old['no_kk'],
            'nik'           => $old['nik'],
            'tempat_lahir'  => $old['tempat_lahir'],
            'tanggal_lahir' => $old['tanggal_lahir'],
            'nama_ortu'     => $old['nama_ortu'],
            'alamat'        => $old['alamat'],
            'whatsapp'      => $old['whatsapp'],
        ];

        if (ppdb_simpan($data)) {
            $_SESSION['ppdb_berhasil'] = $data;
            $_SESSION['ppdb_csrf'] = bin2hex(random_bytes(32));
            if (!headers_sent()) {
                header('Location: ' . $formulirUrl . '&status=berhasil');
                exit;
            }
            $berhasil = $data; // jika header sudah terkirim, tampilkan langsung
        } else {
            $errors['_form'] = 'Data belum bisa disimpan. Coba lagi, atau hubungi pihak sekolah.';
        }
    }
}

// Langkah aktif: 2 = Isi Formulir, 3 = Berhasil
$langkah = $berhasil ? 3 : 2;
$stepper = [1 => 'Informasi', 2 => 'Isi Formulir', 3 => 'Berhasil', 4 => 'Download'];

$title = 'Formulir Pendaftaran - SD Negeri Kedungsari 1';
$hariIni = date('Y-m-d');

// Helper tampilan field
function f_err(array $errors, string $k): string {
    return isset($errors[$k]) ? '<p class="field__error" id="err-' . esc($k) . '" role="alert">' . esc($errors[$k]) . '</p>' : '';
}
function f_cls(array $errors, string $k): string {
    return isset($errors[$k]) ? ' is-invalid' : '';
}
function f_aria(array $errors, string $k, string $extra = ''): string {
    $ids = trim(($extra !== '' ? $extra . ' ' : '') . (isset($errors[$k]) ? 'err-' . $k : ''));
    return ($ids !== '' ? ' aria-describedby="' . esc($ids) . '"' : '') . (isset($errors[$k]) ? ' aria-invalid="true"' : '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<meta name="description" content="Formulir pendaftaran peserta didik baru SD Negeri Kedungsari 1">
<meta name="robots" content="noindex">

<link rel="icon" href="<?= base_url('assets/bingkai.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
/* ===== Token ===== */
:root{
    --green:#5b7553;
    --green-dark:#46603f;
    --nav:#6f8a68;
    --nav-dark:#5f7d57;
    --sage:#eeecdc;
    --cream:#f6ecdc;
    --paper:#fbfaf4;
    --orange:#f08a1c;
    --amber:#e89500;
    --ok:#12b981;
    --danger:#d33a3a;
    --ink:#2f3a2c;
    --muted:#6b7566;
    --radius:14px;
    --bg-navbar:url('<?= base_url('assets/bg-navbar.png') ?>');
    --bg-formulir:url('<?= base_url('assets/bg-formulir.png') ?>');
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Poppins',sans-serif;color:var(--ink);background:var(--paper);line-height:1.6}
img{max-width:100%;display:block}
a{color:inherit;text-decoration:none}
ul,ol{list-style:none}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important;scroll-behavior:auto!important}}
:focus-visible{outline:3px solid var(--orange);outline-offset:2px}

/* ===== Navbar ===== */
.navbar{position:sticky;top:0;z-index:50;display:flex;align-items:center;justify-content:space-between;gap:24px;
    padding:14px 5vw;color:#fff;
    background:var(--bg-navbar) center/cover no-repeat, linear-gradient(90deg,#6a8662,#738d6b 60%,#7d9170)}
.navbar__brand{display:flex;align-items:center;gap:14px;font-weight:600;font-size:1.05rem;letter-spacing:.2px}
.navbar__brand img{width:64px;height:64px;object-fit:contain;filter:drop-shadow(0 2px 4px rgba(0,0,0,.25))}
.navbar__menu{display:flex;align-items:center;gap:24px;font-size:1rem;font-weight:500}
.navbar__menu a{padding:6px 2px;border-bottom:2px solid transparent;transition:border-color .2s}
.navbar__menu a:hover,.navbar__menu a.is-active{border-color:#fff}
.navbar__menu a.btn-ppdb.is-active{border-color:transparent}
.btn-ppdb{display:inline-block;background:var(--orange);color:#fff!important;padding:6px 16px!important;
    border-radius:999px;border:0!important;font-weight:600;transition:background .2s}
.btn-ppdb:hover{background:#d9780f}
.navbar__toggle{display:none;background:none;border:0;cursor:pointer;flex-direction:column;gap:5px}
.navbar__toggle span{width:24px;height:2px;background:#fff;display:block}

/* ===== Section + latar daun ===== */
.section{position:relative;padding:72px 6vw 110px;text-align:center;overflow:hidden;
    background:var(--bg-formulir) center top/100% auto repeat-y, linear-gradient(180deg,#fbfaf4 0%,#f4f3e6 55%,#eeecdc 100%)}
/* Daun cadangan (tampil di atas gradien, di bawah konten) */
.leaf{position:absolute;pointer-events:none;z-index:0;opacity:.34;
    background:no-repeat center/contain url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 220'><path d='M60 4C112 56 112 150 60 216 8 150 8 56 60 4Z' fill='%23869a7a'/><path d='M60 20V206' stroke='%23fbfaf4' stroke-width='2' opacity='.7'/><path d='M60 70 32 52M60 100 28 78M60 130 30 106M60 70 88 52M60 100 92 78M60 130 90 106' stroke='%23fbfaf4' stroke-width='1.5' opacity='.5'/></svg>")}
.leaf--1{width:230px;height:420px;left:-70px;top:60px;transform:rotate(-18deg)}
.leaf--2{width:210px;height:380px;right:-60px;top:30px;transform:rotate(24deg) scaleX(-1)}
.leaf--3{width:260px;height:470px;left:-110px;top:46%;transform:rotate(14deg);opacity:.26}
.leaf--4{width:240px;height:440px;right:-90px;top:58%;transform:rotate(-20deg) scaleX(-1);opacity:.26}
.leaf--5{width:200px;height:360px;left:-70px;bottom:-60px;transform:rotate(-8deg);opacity:.22}
.leaf--6{width:200px;height:360px;right:-70px;bottom:-70px;transform:rotate(10deg) scaleX(-1);opacity:.22}
.section > *:not(.leaf){position:relative;z-index:1}

.title{position:relative;display:inline-block;font-size:clamp(2rem,4.5vw,3.1rem);font-weight:700;color:var(--green);margin-bottom:22px;padding-bottom:10px;line-height:1.2}
.title::after{content:"";position:absolute;left:50%;bottom:0;width:100%;height:4px;background:var(--green-dark);transform:translateX(-50%);border-radius:3px}
.title--tight{margin-bottom:14px}
.subtitle{color:#4b5a47;font-size:clamp(.9rem,1.7vw,1.1rem);margin-bottom:44px}

/* ===== Tombol ===== */
.pill{display:inline-block;font:inherit;font-size:.9rem;font-weight:600;padding:8px 24px;border-radius:999px;cursor:pointer;
    background:#fff;color:var(--green-dark);border:1.5px solid var(--green);transition:background .2s,color .2s}
.pill:hover{background:var(--green-dark);color:#fff}
.pill--solid{background:var(--green);color:#fff}

/* ===== Stepper ===== */
.stepper{display:flex;max-width:760px;margin:0 auto 52px}
.stepper li{position:relative;flex:1;text-align:center;font-size:1rem;font-weight:600;color:#9aa3b2}
.stepper__dot{display:grid;place-items:center;width:56px;height:56px;margin:0 auto 10px;border-radius:50%;
    background:#fff;border:2px solid #d5dbe6;font-weight:500;font-size:1.15rem;color:#9aa3b2;position:relative;z-index:1}
.stepper li:not(:last-child)::after{content:"";position:absolute;top:27px;left:calc(50% + 38px);right:calc(-50% + 38px);
    height:3px;border-radius:3px;background:#dfe5ee}
.stepper li.is-done{color:var(--ok)}
.stepper li.is-done .stepper__dot{background:var(--ok);border-color:var(--ok);color:#fff}
.stepper li.is-done::after{background:var(--ok)!important}
.stepper li.is-active{color:#1d4ed8}
.stepper li.is-active .stepper__dot{background:var(--amber);border-color:var(--amber);color:#fff;font-weight:600;
    box-shadow:0 0 0 6px rgba(232,149,0,.2)}
.stepper svg{width:22px;height:22px}

/* ===== Kotak info ===== */
.panduan{max-width:900px;margin:0 auto 76px;padding:22px clamp(18px,4vw,34px) 20px;text-align:left;
    background:rgba(255,247,230,.92);border:1.5px solid #f1d9a0;border-radius:18px}
.panduan h2{display:flex;align-items:center;gap:12px;font-size:1.1rem;font-weight:600;color:#92560a;margin-bottom:12px}
.panduan h2 svg{width:20px;height:20px;flex:none}
.panduan li{display:flex;align-items:center;gap:12px;font-size:.9rem;color:#4a3a1a;padding:6px 0}
.panduan li svg{width:14px;height:14px;flex:none;color:var(--amber)}

/* ===== Kartu formulir ===== */
.form-card{max-width:900px;margin:0 auto;text-align:left;background:#fff;border-radius:24px;overflow:hidden;
    border-top:14px solid var(--amber);padding:clamp(28px,5vw,48px) clamp(24px,5vw,50px) clamp(32px,5vw,52px);
    box-shadow:0 12px 40px rgba(60,80,55,.16)}
.form-card__org{font-size:.78rem;font-weight:600;letter-spacing:.3px;color:#8a93a6;text-transform:uppercase}
.form-card h2{font-size:clamp(1.6rem,3.4vw,2.15rem);font-weight:700;color:#141c33;line-height:1.25;margin:6px 0 4px}
.form-card__hint{font-size:.88rem;font-style:italic;color:#6b7280;margin-bottom:28px}
.form-error{padding:12px 16px;margin-bottom:22px;border-radius:10px;background:#fdecec;border:1.5px solid #f0b4b4;color:#8a1f1f;font-size:.88rem}
.form-error strong{display:block;margin-bottom:2px}

.field{margin-bottom:22px}
.field label{display:block;font-size:1rem;font-weight:600;color:#141c33;margin-bottom:9px}
.field label .req{color:var(--danger)}
.field label .opt{font-weight:400;color:#8a93a6;font-size:.82rem}
.field input,.field textarea{display:block;width:100%;font:inherit;font-size:.98rem;color:#141c33;background:#f8f9fb;
    border:1.5px solid #e6e9f0;border-radius:12px;padding:14px 20px;box-shadow:0 3px 6px rgba(20,28,51,.1);transition:border-color .2s,background .2s}
.field textarea{resize:vertical;min-height:84px}
.field input::placeholder,.field textarea::placeholder{color:#c2c8d4}
.field input:focus,.field textarea:focus{outline:none;border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(232,149,0,.2)}
.field .is-invalid{border-color:var(--danger);background:#fff8f8}
.field__hint{margin-top:7px;font-size:.78rem;color:#7a8394}
.field__hint.is-ok{color:#0f8f66;font-weight:500}
.field__error{margin-top:6px;font-size:.82rem;font-weight:500;color:var(--danger)}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:24px}

.btn-submit{display:flex;align-items:center;justify-content:center;gap:12px;width:100%;margin-top:34px;padding:16px;border:0;
    border-radius:12px;background:var(--amber);color:#fff;font:inherit;font-size:1.05rem;font-weight:600;cursor:pointer;
    box-shadow:0 4px 12px rgba(232,149,0,.38);transition:background .2s}
.btn-submit:hover{background:#cf8400}
.btn-submit svg{width:20px;height:20px}
.form-note{text-align:center;margin-top:14px;font-size:.8rem;color:#7a8394}

/* ===== Halaman berhasil ===== */
.sukses{max-width:760px;margin:0 auto;text-align:left;background:#fff;border-radius:24px;
    border-top:14px solid var(--ok);padding:clamp(24px,5vw,44px);box-shadow:0 12px 40px rgba(60,80,55,.16)}
.sukses__ikon{display:grid;place-items:center;width:64px;height:64px;margin:0 auto 14px;border-radius:50%;background:var(--ok);color:#fff}
.sukses__ikon svg{width:34px;height:34px}
.sukses h2{text-align:center;font-size:clamp(1.5rem,3.2vw,2rem);font-weight:700;color:#141c33}
.sukses__sub{text-align:center;font-size:.9rem;color:#5b6475;margin:6px 0 20px}
.sukses__kode{text-align:center;margin-bottom:22px}
.sukses__kode span{display:inline-block;padding:8px 22px;border-radius:999px;background:#e5f8f0;border:1.5px solid var(--ok);
    font-weight:700;letter-spacing:.8px;color:#0b6b4b}
.ringkasan{display:grid;grid-template-columns:minmax(120px,190px) 1fr;gap:0;border-top:1.5px solid #e6eaf0;font-size:.92rem}
.ringkasan dt,.ringkasan dd{padding:10px 4px;border-bottom:1.5px solid #e6eaf0}
.ringkasan dt{color:#6b7280;font-weight:500}
.ringkasan dd{color:#141c33;font-weight:600;word-break:break-word}
.sukses__aksi{display:flex;justify-content:center;gap:12px;flex-wrap:wrap;margin-top:28px}
.sukses__catatan{text-align:center;font-size:.8rem;color:#7a8394;margin-top:16px}

/* ===== Footer ===== */
.footer{color:#fff;font-size:.82rem;
    background:var(--bg-navbar) center/cover no-repeat, linear-gradient(90deg,#6a8662,#738d6b 60%,#7d9170)}
.footer__inner{display:grid;grid-template-columns:1.5fr 1fr .8fr 1.5fr;gap:34px;padding:44px 5vw 30px;align-items:start}
.footer__logo{display:flex;align-items:center;gap:10px;font-size:1.45rem;font-weight:600;margin-bottom:8px;line-height:1.2}
.footer__logo img{width:72px;height:72px;object-fit:contain}
.footer__brand p{max-width:340px;opacity:.95}
.footer h4{font-size:1.25rem;font-weight:600;margin-bottom:12px}
.footer li{margin-bottom:8px;opacity:.92}
.footer a:hover{text-decoration:underline}
.footer__contact li{display:flex;gap:12px;align-items:flex-start}
.footer__contact svg{width:20px;height:20px;flex:none;margin-top:2px;opacity:.85}
.footer__map{display:block;width:100%;height:110px;border:0;border-radius:12px;margin-top:6px}
.footer__copy{text-align:center;padding:12px;border-top:1px solid rgba(255,255,255,.25);font-size:.72rem}

/* ===== Responsif ===== */
@media (max-width:980px){
    .navbar__menu{gap:16px;font-size:.92rem}
    .navbar__brand{font-size:.95rem}
}
@media (max-width:860px){
    .navbar__toggle{display:flex}
    .navbar__menu{position:absolute;top:100%;left:0;right:0;flex-direction:column;align-items:flex-start;gap:0;
        background:#5f7d57;padding:10px 6vw 18px;display:none}
    .navbar__menu.is-open{display:flex}
    .navbar__menu li{width:100%;padding:8px 0}
    .footer__inner{grid-template-columns:1fr 1fr}
}
@media (max-width:620px){
    .field-row{grid-template-columns:1fr;gap:0}
    .stepper li{font-size:.75rem}
    .stepper__dot{width:42px;height:42px;font-size:.9rem}
    .stepper li:not(:last-child)::after{top:20px;left:calc(50% + 28px);right:calc(-50% + 28px)}
    .ringkasan{grid-template-columns:1fr}
    .ringkasan dt{border-bottom:0;padding-bottom:0}
    .navbar__brand img{width:48px;height:48px}
    .panduan{margin-bottom:48px}
}
@media (max-width:520px){
    .section{padding:56px 5vw 80px}
    .footer__inner{grid-template-columns:1fr}
    .footer__logo{font-size:1.15rem}
}

/* ===== Cetak / simpan PDF (halaman berhasil) ===== */
@media print{
    .navbar,.footer,.stepper,.subtitle,.title,.sukses__aksi,.leaf{display:none!important}
    body{background:#fff}
    .section{padding:0;background:#fff}
    .sukses{box-shadow:none;border:1.5px solid #ccc;max-width:100%}
}
    </style>
</head>
<body>
<?php
$menuLain = [
    'berita' => 'Berita',
    'ekstra' => 'Ekstra',
    'galeri' => 'Galeri',
    'kontak' => 'Hubungi kami',
];
?>
    <nav class="navbar" id="navbar">
        <a href="<?= page_url('home') ?>" class="navbar__brand">
            <img src="<?= base_url('assets/bingkai.png') ?>" alt="Logo SD Negeri Kedungsari 1">
            <span>SD NEGERI KEDUNGSARI 1</span>
        </a>

        <button class="navbar__toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navMenu">
            <span></span><span></span><span></span>
        </button>

        <ul class="navbar__menu" id="navMenu">
            <li><a href="<?= page_url('home') ?>" class="<?= $current === 'home' ? 'is-active' : '' ?>">Home</a></li>
            <li><a href="<?= page_url('profil') ?>" class="<?= $current === 'profil' ? 'is-active' : '' ?>">Profil</a></li>

            <?php foreach ($menuLain as $key => $label): ?>
                <li><a href="<?= page_url($key) ?>" class="<?= $current === $key ? 'is-active' : '' ?>"><?= esc($label) ?></a></li>
            <?php endforeach; ?>

            <li><a href="<?= page_url('ppdb') ?>" class="btn-ppdb">Daftar PPDB</a></li>
        </ul>
    </nav>

<main>
    <section class="section" id="formulir">
        <span class="leaf leaf--1" aria-hidden="true"></span>
        <span class="leaf leaf--2" aria-hidden="true"></span>
        <span class="leaf leaf--3" aria-hidden="true"></span>
        <span class="leaf leaf--4" aria-hidden="true"></span>
        <span class="leaf leaf--5" aria-hidden="true"></span>
        <span class="leaf leaf--6" aria-hidden="true"></span>

        <h1 class="title title--tight">Formulir Pendaftaran</h1>
        <p class="subtitle">Lengkapi data calon peserta didik dengan benar sebelum mengirimkan formulir.</p>

        <!-- ===== Langkah pendaftaran ===== -->
        <ol class="stepper" aria-label="Langkah pendaftaran">
            <?php foreach ($stepper as $no => $label):
                $kelas = $no < $langkah ? 'is-done' : ($no === $langkah ? 'is-active' : ''); ?>
                <li class="<?= $kelas ?>" <?= $no === $langkah ? 'aria-current="step"' : '' ?>>
                    <span class="stepper__dot">
                        <?php if ($no < $langkah): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5.5 12.5 4.5 4.5 8.5-9.5"/></svg>
                            <span style="position:absolute;left:-9999px">Selesai:</span>
                        <?php else: ?>
                            <?= sprintf('%02d', $no) ?>
                        <?php endif; ?>
                    </span>
                    <?= esc($label) ?>
                </li>
            <?php endforeach; ?>
        </ol>

<?php if ($berhasil): ?>

        <!-- ================= PENDAFTARAN BERHASIL ================= -->
        <article class="sukses">
            <div class="sukses__ikon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5.5 12.5 4.5 4.5 8.5-9.5"/></svg>
            </div>
            <h2>Pendaftaran Berhasil</h2>
            <p class="sukses__sub">Data calon peserta didik sudah kami terima. Simpan kode pendaftaran berikut.</p>
            <p class="sukses__kode"><span><?= esc($berhasil['kode']) ?></span></p>

            <dl class="ringkasan">
                <dt>Nama</dt>            <dd><?= esc($berhasil['nama']) ?></dd>
                <dt>NISN</dt>            <dd><?= $berhasil['nisn'] !== '' ? esc($berhasil['nisn']) : '-' ?></dd>
                <dt>No. KK</dt>          <dd><?= esc(ppdb_samarkan($berhasil['no_kk'])) ?></dd>
                <dt>NIK</dt>             <dd><?= esc(ppdb_samarkan($berhasil['nik'])) ?></dd>
                <dt>Tempat, Tgl. Lahir</dt><dd><?= esc($berhasil['tempat_lahir']) ?>, <?= esc(tanggal_id($berhasil['tanggal_lahir'])) ?></dd>
                <dt>Orang Tua / Wali</dt><dd><?= esc($berhasil['nama_ortu']) ?></dd>
                <dt>Alamat</dt>          <dd><?= esc($berhasil['alamat']) ?></dd>
                <dt>WhatsApp</dt>        <dd><?= esc($berhasil['whatsapp']) ?></dd>
                <dt>Waktu Daftar</dt>    <dd><?= esc(tanggal_id(substr($berhasil['waktu'], 0, 10))) ?>, <?= esc(substr($berhasil['waktu'], 11, 5)) ?></dd>
            </dl>

            <div class="sukses__aksi">
                <button type="button" class="pill pill--solid" onclick="window.print()">Download Formulir</button>
                <a class="pill" href="<?= page_url('ppdb') ?>">Kembali ke PPDB</a>
            </div>
            <p class="sukses__catatan">Pada jendela cetak, pilih “Simpan sebagai PDF” untuk mengunduh formulir.</p>
        </article>

<?php else: ?>

        <!-- ===== Sebelum mengisi ===== -->
        <aside class="panduan" aria-labelledby="panduan-judul">
            <h2 id="panduan-judul">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5 2.5 20h19z"/><path d="M12 10v4.5M12 17.3v.2"/></svg>
                Sebelum Mengisi Formulir
            </h2>
            <ul>
                <?php foreach ([
                    'Siapkan semua data dan dokumen yang diperlukan',
                    'Pastikan seluruh data yang dimasukkan sudah benar',
                    'NISN, No. KK, dan NIK harus berupa angka tanpa spasi atau karakter lain',
                    'No. KK dan NIK harus tepat 16 karakter sesuai dokumen resmi',
                ] as $t): ?>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5.5 12.5 4.5 4.5 8.5-9.5"/></svg>
                        <?= esc($t) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- ================= FORMULIR ================= -->
        <form class="form-card" method="post" action="<?= esc($formulirUrl) ?>#formulir" autocomplete="off">
            <p class="form-card__org">SD Negeri Kedungsari 1</p>
            <h2>Formulir PPDB Online</h2>
            <p class="form-card__hint">Kolom bertanda <span style="color:var(--danger)">*</span> wajib diisi.</p>

            <?php if ($errors): ?>
                <div class="form-error" role="alert">
                    <strong><?= isset($errors['_form']) ? esc($errors['_form']) : 'Formulir belum bisa dikirim.' ?></strong>
                    <?php if (count(array_diff_key($errors, ['_form' => 1]))): ?>
                        Periksa kolom yang diberi tanda merah di bawah.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="csrf" value="<?= esc($_SESSION['ppdb_csrf']) ?>">

            <div class="field">
                <label for="nama">Nama Calon Peserta Didik <span class="req">*</span></label>
                <input type="text" id="nama" name="nama" required maxlength="100" autocomplete="name"
                       placeholder="Nama lengkap sesuai akta kelahiran"
                       value="<?= esc($old['nama']) ?>" class="<?= trim(f_cls($errors, 'nama')) ?>"<?= f_aria($errors, 'nama') ?>>
                <?= f_err($errors, 'nama') ?>
            </div>

            <div class="field">
                <label for="nisn">NISN (Nomor Induk Siswa Nasional) <span class="opt">— isi jika sudah punya</span></label>
                <input type="text" id="nisn" name="nisn" inputmode="numeric" pattern="\d{10}" maxlength="10"
                       placeholder="10 digit NISN"
                       data-digits="10" data-hint="hint-nisn" data-label="digit — hanya angka"
                       value="<?= esc($old['nisn']) ?>" class="<?= trim(f_cls($errors, 'nisn')) ?>"<?= f_aria($errors, 'nisn', 'hint-nisn') ?>>
                <p class="field__hint" id="hint-nisn">0/10 digit — hanya angka</p>
                <?= f_err($errors, 'nisn') ?>
            </div>

            <div class="field">
                <label for="no_kk">Nomor KK (Kartu Keluarga) <span class="req">*</span></label>
                <input type="text" id="no_kk" name="no_kk" required inputmode="numeric" pattern="\d{16}" maxlength="16"
                       placeholder="16 digit nomor KK"
                       data-digits="16" data-hint="hint-kk" data-label="karakter — hanya angka"
                       value="<?= esc($old['no_kk']) ?>" class="<?= trim(f_cls($errors, 'no_kk')) ?>"<?= f_aria($errors, 'no_kk', 'hint-kk') ?>>
                <p class="field__hint" id="hint-kk">0/16 karakter — hanya angka</p>
                <?= f_err($errors, 'no_kk') ?>
            </div>

            <div class="field">
                <label for="nik">NIK (Nomor Induk Kependudukan) <span class="req">*</span></label>
                <input type="text" id="nik" name="nik" required inputmode="numeric" pattern="\d{16}" maxlength="16"
                       placeholder="16 digit NIK"
                       data-digits="16" data-hint="hint-nik" data-label="karakter — hanya angka"
                       value="<?= esc($old['nik']) ?>" class="<?= trim(f_cls($errors, 'nik')) ?>"<?= f_aria($errors, 'nik', 'hint-nik') ?>>
                <p class="field__hint" id="hint-nik">0/16 karakter — hanya angka</p>
                <?= f_err($errors, 'nik') ?>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="tempat_lahir">Tempat Lahir <span class="req">*</span></label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" required maxlength="60"
                           placeholder="Kota/kabupaten"
                           value="<?= esc($old['tempat_lahir']) ?>" class="<?= trim(f_cls($errors, 'tempat_lahir')) ?>"<?= f_aria($errors, 'tempat_lahir') ?>>
                    <?= f_err($errors, 'tempat_lahir') ?>
                </div>
                <div class="field">
                    <label for="tanggal_lahir">Tanggal Lahir <span class="req">*</span></label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" required max="<?= esc($hariIni) ?>"
                           value="<?= esc($old['tanggal_lahir']) ?>" class="<?= trim(f_cls($errors, 'tanggal_lahir')) ?>"<?= f_aria($errors, 'tanggal_lahir') ?>>
                    <?= f_err($errors, 'tanggal_lahir') ?>
                </div>
            </div>

            <div class="field">
                <label for="nama_ortu">Nama Orang Tua / Wali <span class="req">*</span></label>
                <input type="text" id="nama_ortu" name="nama_ortu" required maxlength="100"
                       placeholder="Nama lengkap orang tua / wali"
                       value="<?= esc($old['nama_ortu']) ?>" class="<?= trim(f_cls($errors, 'nama_ortu')) ?>"<?= f_aria($errors, 'nama_ortu') ?>>
                <?= f_err($errors, 'nama_ortu') ?>
            </div>

            <div class="field">
                <label for="alamat">Alamat <span class="req">*</span></label>
                <textarea id="alamat" name="alamat" required maxlength="255" rows="2" autocomplete="street-address"
                          placeholder="Alamat lengkap tempat tinggal"
                          class="<?= trim(f_cls($errors, 'alamat')) ?>"<?= f_aria($errors, 'alamat') ?>><?= esc($old['alamat']) ?></textarea>
                <?= f_err($errors, 'alamat') ?>
            </div>

            <div class="field">
                <label for="whatsapp">Nomor Kontak (WhatsApp) <span class="req">*</span></label>
                <input type="tel" id="whatsapp" name="whatsapp" required inputmode="tel" maxlength="16" autocomplete="tel"
                       placeholder="08xxxxxxxxxx"
                       value="<?= esc($old['whatsapp']) ?>" class="<?= trim(f_cls($errors, 'whatsapp')) ?>"<?= f_aria($errors, 'whatsapp') ?>>
                <?= f_err($errors, 'whatsapp') ?>
            </div>

            <button type="submit" class="btn-submit">
                Kirim Formulir Pendaftaran
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
            <p class="form-note">Pastikan semua data yang diinputkan sesuai dengan berkas kependudukan resmi.</p>
        </form>

<?php endif; ?>
    </section>
</main>

<footer class="footer">
    <div class="footer__inner">
        <div class="footer__brand">
            <div class="footer__logo">
                <img src="<?= base_url('assets/bingkai.png') ?>" alt="Logo">
                <strong>SD NEGERI KEDUNGSARI 1</strong>
            </div>
            <p>Situs resmi SD Negeri Kedungsari 1 untuk informasi akademik dan kesiswaan.</p>
        </div>

        <div class="footer__col">
            <h4>Informasi Sekolah</h4>
            <ul>
                <li><a href="<?= page_url('profil') ?>#visi-misi">Visi &amp; Misi</a></li>
                <li><a href="<?= page_url('galeri') ?>">Prestasi Siswa Siswi</a></li>
                <li><a href="<?= page_url('berita') ?>">Artikel &amp; Berita Terkini</a></li>
                <li><a href="<?= page_url('ekstra') ?>">Unit Unit</a></li>
                <li><a href="<?= page_url('profil') ?>#guru">Guru &amp; Karyawan</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h4>Sosial media</h4>
            <ul>
                <li><a href="#" target="_blank" rel="noopener">Instagram</a></li>
                <li><a href="#" target="_blank" rel="noopener">Facebook</a></li>
            </ul>
        </div>

        <div class="footer__col footer__contact">
            <h4>Kontak &amp; Lokasi</h4>
            <ul>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    <span>Jl. Raya Gringging, Kedungsari, Kec. Tarokan, Kabupaten Kediri, Jawa Timur 64152</span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg>
                    <span>0000-0000-0000</span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    <span>sdnegerikedungsari1@gmail.com</span>
                </li>
            </ul>
            <iframe class="footer__map" loading="lazy" title="Lokasi sekolah"
                    src="https://www.google.com/maps?q=Kediri&output=embed"></iframe>
        </div>
    </div>
    <div class="footer__copy">&copy; <?= date('Y') ?> SD Negeri Kedungsari 1. Semua hak dilindungi.</div>
</footer>

<script>
/* ===== Menu mobile ===== */
function initNavbar() {
    const toggle = document.getElementById('navToggle');
    const menu   = document.getElementById('navMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open);
    });
}

/* ===== Kolom angka (NISN, No. KK, NIK): hanya angka + penghitung karakter ===== */
function initKolomAngka() {
    document.querySelectorAll('input[data-digits]').forEach((input) => {
        const max   = parseInt(input.dataset.digits, 10);
        const hint  = document.getElementById(input.dataset.hint);
        const label = input.dataset.label || '';

        const update = () => {
            input.value = input.value.replace(/\D/g, '').slice(0, max);
            if (!hint) return;
            hint.textContent = `${input.value.length}/${max} ${label}`;
            hint.classList.toggle('is-ok', input.value.length === max);
        };

        input.addEventListener('input', update);
        update();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initKolomAngka();
});
</script>
</body>
</html>