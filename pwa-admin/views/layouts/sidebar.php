<?php
/**
 * Sidebar. WAJIB: tidak ada menu Jadwal dan Piket di PWA Admin.
 * Menyembunyikan menu hanya kenyamanan; server tetap yang menolak akses.
 */
$nav = [
    ['label' => 'Setup Awal', 'items' => [
        ['key' => 'dashboard',      'label' => 'Dasbor',                 'icon' => 'home',         'href' => 'dashboard'],
        ['key' => 'kenaikan-kelas', 'label' => 'Tahun Ajaran & Kenaikan', 'icon' => 'calendar-cog', 'href' => 'kenaikan-kelas'],
        ['key' => 'data-master',    'label' => 'Data Master',            'icon' => 'database', 'children' => [
            ['key' => 'mata-pelajaran',  'label' => 'Mata Pelajaran'],
            ['key' => 'tahun-ajaran',    'label' => 'Tahun Ajaran'],
            ['key' => 'guru',            'label' => 'Guru'],
            ['key' => 'siswa',           'label' => 'Siswa'],
            ['key' => 'kelas',           'label' => 'Kelas'],
            ['key' => 'hari-libur',      'label' => 'Hari Libur'],
            ['key' => 'ekstrakurikuler', 'label' => 'Ekstrakurikuler'],
        ]],
    ]],
    ['label' => 'Operasional', 'items' => [
        ['key' => 'riwayat-kelas', 'label' => 'Penempatan Siswa', 'icon' => 'user-plus', 'href' => 'riwayat-kelas'],
        ['key' => 'konten',        'label' => 'Konten Website',   'icon' => 'layout', 'children' => [
            ['key' => 'event',              'label' => 'Event & Galeri'],
            ['key' => 'galeri-mading',      'label' => 'Mading'],
            ['key' => 'program-pembiasaan', 'label' => 'Program Pembiasaan'],
            ['key' => 'pengaturan',         'label' => 'Pengaturan'],
        ]],
        ['key' => 'akademik', 'label' => 'Data Akademik', 'icon' => 'eye', 'badge' => 'Hanya lihat', 'children' => [
            ['key' => 'absensi',  'label' => 'Absensi'],
            ['key' => 'nilai',    'label' => 'Nilai'],
            ['key' => 'disiplin', 'label' => 'Kedisiplinan'],
        ]],
        ['key' => 'laporan', 'label' => 'Laporan Excel', 'icon' => 'file', 'href' => 'laporan'],
    ]],
    ['label' => 'Akun & Pengaturan', 'items' => [
        ['key' => 'akun',          'label' => 'Kelola Akun',         'icon' => 'user-cog', 'href' => 'akun'],
        ['key' => 'ubah-password', 'label' => 'Ganti Password Saya', 'icon' => 'key',      'href' => 'ubah-password'],
        ['key' => '__logout',      'label' => 'Keluar',              'icon' => 'logout',   'action' => 'logout'],
    ]],
];

$icon = fn(string $n) => '<svg class="ico" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
?>
<div class="sidebar-backdrop" data-sidebar-close hidden></div>
<aside class="sidebar" id="sidebar" aria-label="Menu utama">
  <div class="sidebar__brand">
    <img class="sidebar__logo" src="<?= e(url('assets/icons/icon-192.png')) ?>" alt="" width="40" height="40">
    <div>
      <div class="sidebar__app"><?= e($config['appName']) ?></div>
      <div class="sidebar__school"><?= e($config['schoolName']) ?></div>
    </div>
    <button type="button" class="sidebar__close" data-sidebar-close aria-label="Tutup menu"><?= $icon('x') ?></button>
  </div>

  <nav class="sidebar__nav">
  <?php foreach ($nav as $group): ?>
    <div class="nav-group">
      <div class="nav-group__label"><?= e($group['label']) ?></div>
      <?php foreach ($group['items'] as $item): ?>
        <?php if (!empty($item['children'])):
            $childKeys = array_column($item['children'], 'key');
            $open = in_array($activeRoute, $childKeys, true);
            $id = 'sub-' . $item['key']; ?>
          <button type="button" class="nav-item nav-item--parent<?= $open ? ' is-open is-current' : '' ?>"
                  data-toggle-sub="<?= e($id) ?>" aria-expanded="<?= $open ? 'true' : 'false' ?>" aria-controls="<?= e($id) ?>">
            <?= $icon($item['icon']) ?>
            <span class="nav-item__text"><?= e($item['label']) ?></span>
            <?php if (!empty($item['badge'])): ?><span class="nav-badge"><?= e($item['badge']) ?></span><?php endif; ?>
            <svg class="ico ico--chev" aria-hidden="true"><use href="#i-chevron"/></svg>
          </button>
          <div class="nav-sub<?= $open ? ' is-open' : '' ?>" id="<?= e($id) ?>">
            <?php foreach ($item['children'] as $c): ?>
              <a class="nav-sub__link<?= $activeRoute === $c['key'] ? ' is-active' : '' ?>" href="<?= e(url($c['key'])) ?>"
                 <?= $activeRoute === $c['key'] ? 'aria-current="page"' : '' ?>><?= e($c['label']) ?></a>
            <?php endforeach; ?>
          </div>
        <?php elseif (($item['action'] ?? '') === 'logout'): ?>
          <button type="button" class="nav-item" data-action="logout">
            <?= $icon($item['icon']) ?><span class="nav-item__text"><?= e($item['label']) ?></span>
          </button>
        <?php else: $active = $activeRoute === $item['key']; ?>
          <a class="nav-item<?= $active ? ' is-active' : '' ?>" href="<?= e(url($item['href'])) ?>" <?= $active ? 'aria-current="page"' : '' ?>>
            <?= $icon($item['icon']) ?><span class="nav-item__text"><?= e($item['label']) ?></span>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  </nav>
</aside>