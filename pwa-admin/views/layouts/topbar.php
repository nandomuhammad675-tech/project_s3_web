<header class="topbar">
  <button type="button" class="icon-btn topbar__menu" data-sidebar-open aria-label="Buka menu">
    <svg class="ico" aria-hidden="true"><use href="#i-menu"/></svg>
  </button>
  <?php if (!empty($breadcrumb)): ?>
  <nav class="crumb" aria-label="Breadcrumb">
    <?php foreach ($breadcrumb as $c): ?>
    <span class="crumb__parent"><?= e($c) ?></span>
    <svg class="ico ico--xs crumb__sep" aria-hidden="true"><use href="#i-chevron"/></svg>
    <?php endforeach; ?>
    <span class="crumb__current" aria-current="page"><?= e($pageTitle) ?></span>
  </nav>
  <?php else: ?>
  <div class="topbar__title"><?= e($pageTitle) ?></div>
  <?php endif; ?>

  <div class="topbar__right">
    <div class="search" role="search">
      <svg class="ico search__ico" aria-hidden="true"><use href="#i-search"/></svg>
      <input type="search" id="global-search" class="search__input" placeholder="Cari menu atau data..."
             autocomplete="off" aria-label="Cari menu" aria-controls="search-results" aria-expanded="false">
      <ul class="search__results" id="search-results" role="listbox" hidden></ul>
    </div>

    <button type="button" class="icon-btn bell" id="btn-bell" aria-label="Pemberitahuan">
      <svg class="ico" aria-hidden="true"><use href="#i-bell"/></svg>
      <span class="bell__dot" id="bell-dot" hidden></span>
    </button>

    <div class="avatar" id="avatar" title="Akun Anda" aria-label="Akun Anda">AD</div>
  </div>
</header>