<header class="bg-white px-8 py-4 flex justify-between items-center shadow-sm z-20">
    <div class="text-sm text-gray-500 font-medium flex gap-2">
        <span>Guru</span> 
        <span>&gt;</span> 
        <span class="text-gray-800 font-semibold"><?= $page_title ?? 'Beranda' ?></span>
    </div>
    <div class="flex items-center gap-4">
        <div class="relative">
        <button type="button" id="bellBtn" class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 relative" aria-label="Pemberitahuan" aria-haspopup="true">
            <i class="ph ph-bell text-xl"></i>
            <span id="bellDot" hidden class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
        </button>
        <div id="bellPanel" hidden class="absolute right-0 top-12 w-72 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden" role="dialog" aria-label="Pemberitahuan">
            <div class="px-4 py-3 font-bold text-sm text-gray-800 border-b border-gray-100">Pemberitahuan</div>
            <div id="bellIsi" class="p-4 text-sm text-gray-500">Memuat...</div>
        </div>
        </div>
        <a href="<?= sk_url('profil') ?>" id="topbarInisial" class="w-10 h-10 rounded-full bg-[#E5E0D8] text-[#455a2d] font-bold text-sm flex items-center justify-center" title="Profil akun">
            --
        </a>
    </div>
</header>
