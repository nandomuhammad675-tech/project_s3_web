<?php
/**
 * Layout: header — <head>, penjaga login, sprite ikon, sidebar (desktop), pembuka <div class="main">.
 * Ditutup oleh footer.php.
 */
$loginUrl = $root . 'index.php?page=login';
?>
<!DOCTYPE html>
<html lang="id" class="auth-pending">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($pageTitle) ?> - SI-KESA Guru</title>
<meta name="theme-color" content="#475a1f">
<meta name="robots" content="noindex">
<link rel="manifest" href="<?= e($root) ?>manifest.json">
<link rel="icon" href="<?= e(asset('img/logo.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/bottomnav.css')) ?>">
<script>
/* Penjaga login: tanpa token guru yang masih berlaku -> ke halaman login. */
(function () {
    var LOGIN = <?= json_encode($loginUrl, JSON_UNESCAPED_SLASHES) ?>;
    try {
        var t = localStorage.getItem('sikesa_token');
        var u = JSON.parse(localStorage.getItem('sikesa_user') || 'null');
        var p = JSON.parse(atob(t.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')));
        if (!u || u.role !== 'guru' || (p.exp && p.exp * 1000 < Date.now() + 30000)) { throw 0; }
        document.documentElement.classList.remove('auth-pending');
    } catch (err) {
        window.location.replace(LOGIN);
    }
})();
window.SIKESA_CONFIG = <?= json_encode([
    'apiBase'  => $apiBase,
    'loginUrl' => $loginUrl,
    'homeUrl'  => $root . 'index.php?page=beranda',
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
</head>
<body>

<!-- Sprite ikon (gaya garis, 24x24) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <defs>
    <symbol id="i-home" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></symbol>
    <symbol id="i-clipboard-check" viewBox="0 0 24 24"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></symbol>
    <symbol id="i-book-open" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/></symbol>
    <symbol id="i-key-round" viewBox="0 0 24 24"><path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"/><circle cx="16.5" cy="7.5" r=".5" fill="currentColor"/></symbol>
    <symbol id="i-shield-check" viewBox="0 0 24 24"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="i-file-text" viewBox="0 0 24 24"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></symbol>
    <symbol id="i-user-round" viewBox="0 0 24 24"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></symbol>
    <symbol id="i-log-out" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></symbol>
    <symbol id="i-circle-alert" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></symbol>
    <symbol id="i-inbox" viewBox="0 0 24 24"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></symbol>
  </defs>
</svg>

<a class="skip-link" href="#konten">Lewati ke konten</a>

<div class="app">

  <!-- ===== Sidebar (desktop >= 900px) ===== -->
  <aside class="sidebar" aria-label="Menu utama">
    <a class="sidebar-brand" href="<?= e($root) ?>index.php?page=beranda">
      <span class="brand-mark"><img src="<?= e(asset('img/logo.png')) ?>" alt="" width="28" height="28"></span>
      <span class="brand-text"><strong>SI-KESA</strong><small>SDN Kedungsari 1</small></span>
    </a>

    <nav class="sidebar-nav">
<?php
$grupSekarang = null;
foreach ($nav as $m):
    if ($m['grup'] !== $grupSekarang):
        $grupSekarang = $m['grup'];
?>
      <p class="nav-group"><?= e($grupSekarang) ?></p>
<?php endif; ?>
      <a class="nav-link<?= $m['key'] === $activePage ? ' is-active' : '' ?>"
         href="<?= e($root) ?>index.php?page=<?= e($m['key']) ?>"
         <?= $m['key'] === $activePage ? 'aria-current="page"' : '' ?>>
        <?= icon($m['icon']) ?><span><?= e($m['label']) ?></span>
      </a>
<?php endforeach; ?>
    </nav>

    <div class="sidebar-user">
      <span class="avatar" data-user-initials>-</span>
      <span class="user-text">
        <strong data-user-name>Memuat...</strong>
        <small data-user-role>Guru</small>
      </span>
      <button type="button" class="icon-btn icon-btn--ghost" data-logout aria-label="Keluar" title="Keluar"><?= icon('log-out') ?></button>
    </div>
  </aside>

  <div class="main">