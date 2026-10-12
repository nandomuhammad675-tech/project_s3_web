<?php
/**
 * Layout: topbar — breadcrumb, lonceng, avatar + menu akun.
 */
?>
    <header class="topbar">
      <a class="topbar-brand" href="<?= e($root) ?>index.php?page=beranda" aria-label="SI-KESA">
        <img src="<?= e(asset('img/logo.png')) ?>" alt="" width="28" height="28">
      </a>

      <nav class="breadcrumb" aria-label="Lokasi halaman">
        <span>Guru</span>
        <?= icon('chevron-right', 'i--xs') ?>
        <strong aria-current="page"><?= e($pageTitle) ?></strong>
      </nav>

      <div class="topbar-actions">
        <button type="button" class="icon-btn" id="btnNotif" aria-label="Notifikasi">
          <?= icon('bell') ?>
          <span class="dot" id="notifDot" hidden></span>
        </button>

        <div class="menu-wrap">
          <button type="button" class="avatar avatar--btn" id="btnAvatar" aria-haspopup="true" aria-expanded="false" aria-controls="menuAvatar" aria-label="Menu akun">
            <span data-user-initials>-</span>
          </button>
          <div class="menu" id="menuAvatar" role="menu" hidden>
            <div class="menu-head">
              <strong data-user-name>Guru</strong>
              <small data-user-role>Guru</small>
            </div>
            <a role="menuitem" href="<?= e($root) ?>index.php?page=akun"><?= icon('user-round') ?>Akun saya</a>
            <button role="menuitem" type="button" data-logout><?= icon('log-out') ?>Keluar</button>
          </div>
        </div>
      </div>
    </header>