<?php
// Sidebar guru, tampilannya disamakan dengan sidebar PWA Admin.
require_once __DIR__ . '/../../config.php';
$active_menu = $active_menu ?? 'beranda';

$nav = [
    ['label' => 'Utama', 'items' => [
        ['key' => 'beranda', 'label' => 'Beranda', 'icon' => 'home',         'href' => 'dashboard'],
        ['key' => 'absensi', 'label' => 'Absensi', 'icon' => 'check-square', 'href' => 'absensi'],
        ['key' => 'nilai',   'label' => 'Nilai',   'icon' => 'book-open',    'href' => 'nilai'],
        ['key' => 'jadwal',  'label' => 'Jadwal',  'icon' => 'calendar',     'href' => 'jadwal'],
    ]],
    ['label' => 'Kelas', 'items' => [
        ['key' => 'piket',    'label' => 'Piket',        'icon' => 'clipboard', 'href' => 'piket'],
        ['key' => 'disiplin', 'label' => 'Kedisiplinan', 'icon' => 'shield',    'href' => 'disiplin'],
        ['key' => 'laporan',  'label' => 'Laporan',      'icon' => 'file',      'href' => 'laporan'],
    ]],
    ['label' => 'Akun & Pengaturan', 'items' => [
        ['key' => 'profil',   'label' => 'Akun Saya', 'icon' => 'user-cog', 'href' => 'profil'],
        ['key' => '__logout', 'label' => 'Keluar',    'icon' => 'logout',   'action' => 'logout'],
    ]],
];

$icon = fn(string $n) => '<svg class="ico" aria-hidden="true"><use href="#gi-' . $n . '"/></svg>';
?>
<!-- Sprite ikon (garis, 24px) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <defs>
    <symbol id="gi-home" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></symbol>
    <symbol id="gi-check-square" viewBox="0 0 24 24"><path d="M21 10.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12.5"/><path d="m9 11 3 3L22 4"/></symbol>
    <symbol id="gi-book-open" viewBox="0 0 24 24"><path d="M12 7c-1.5-1.6-4-2.5-8-2.5V18c4 0 6.5.9 8 2.5 1.5-1.6 4-2.5 8-2.5V4.5C16 4.5 13.5 5.4 12 7zM12 7v13.5"/></symbol>
    <symbol id="gi-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></symbol>
    <symbol id="gi-clipboard" viewBox="0 0 24 24"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2M12 11h4M12 16h4M8 11h.01M8 16h.01"/></symbol>
    <symbol id="gi-shield" viewBox="0 0 24 24"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="gi-file" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></symbol>
    <symbol id="gi-user-cog" viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h2"/><circle cx="18" cy="17" r="2.5"/><path d="M18 12.5v2M18 19.5v2M13.5 17h2M20.5 17h2"/></symbol>
    <symbol id="gi-logout" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></symbol>
    <symbol id="gi-chevron" viewBox="0 0 24 24"><path d="m9 6 6 6-6 6"/></symbol>
    <symbol id="gi-bell" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a2 2 0 0 0 3.4 0"/></symbol>
  </defs>
</svg>

<aside class="sidebar" id="sidebar" aria-label="Menu utama">
  <div class="sidebar__brand">
    <img class="sidebar__logo" src="<?= sk_e($sk['base']) ?>/assets/img/logo-sekolah.png" alt="Logo SD Negeri Kedungsari 1" height="40" style="height:40px;width:auto">
    <div>
      <div class="sidebar__app">SI-KESA</div>
      <div class="sidebar__school">SDN Kedungsari 1</div>
    </div>
  </div>

  <nav class="sidebar__nav">
  <?php foreach ($nav as $group): ?>
    <div class="nav-group">
      <div class="nav-group__label"><?= sk_e($group['label']) ?></div>
      <?php foreach ($group['items'] as $item): ?>
        <?php if (($item['action'] ?? '') === 'logout'): ?>
          <button type="button" class="nav-item" data-action="logout">
            <?= $icon($item['icon']) ?><span class="nav-item__text"><?= sk_e($item['label']) ?></span>
          </button>
        <?php else:
            $active = $item['key'] === 'profil'
                ? in_array($active_menu, ['profil', 'akun'], true)
                : $active_menu === $item['key']; ?>
          <a class="nav-item<?= $active ? ' is-active' : '' ?>" href="<?= sk_url($item['href']) ?>" <?= $active ? 'aria-current="page"' : '' ?>>
            <?= $icon($item['icon']) ?><span class="nav-item__text"><?= sk_e($item['label']) ?></span>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  </nav>

  <div style="padding:12px"><button type="button" class="nav-item" data-pwa-install hidden><span class="nav-item__text">Pasang Aplikasi</span></button></div>
</aside>