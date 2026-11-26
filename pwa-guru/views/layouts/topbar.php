<header class="topbar">
  <nav class="crumb" aria-label="Breadcrumb">
    <span class="crumb__parent">Guru</span>
    <svg class="ico ico--xs crumb__sep" aria-hidden="true"><use href="#gi-chevron"/></svg>
    <span class="crumb__current" aria-current="page"><?= $page_title ?? 'Beranda' ?></span>
  </nav>

  <div class="topbar__right">
    <div class="relative">
      <button type="button" id="bellBtn" class="icon-btn bell" aria-label="Pemberitahuan" aria-haspopup="true">
        <svg class="ico" aria-hidden="true"><use href="#gi-bell"/></svg>
        <span id="bellDot" hidden class="bell__dot"></span>
      </button>
      <div id="bellPanel" hidden class="absolute right-0 top-12 w-72 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden" role="dialog" aria-label="Pemberitahuan">
        <div class="px-4 py-3 font-bold text-sm text-gray-800 border-b border-gray-100">Pemberitahuan</div>
        <div id="bellIsi" class="p-4 text-sm text-gray-500">Memuat...</div>
      </div>
    </div>
    <a class="avatar" id="topbarInisial" href="<?= sk_url('profil') ?>" title="Profil akun" aria-label="Profil akun">--</a>
  </div>
</header>