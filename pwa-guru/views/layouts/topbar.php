<header class="bg-white px-8 py-4 flex justify-between items-center shadow-sm z-20">
    <div class="text-sm text-gray-500 font-medium flex gap-2">
        <span>Guru</span> 
        <span>&gt;</span> 
        <span class="text-gray-800 font-semibold"><?= $page_title ?? 'Beranda' ?></span>
    </div>
    <div class="flex items-center gap-4">
        <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 relative">
            <i class="ph ph-bell text-xl"></i>
            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
        </button>
        <button class="w-10 h-10 rounded-full bg-[#E5E0D8] text-[#455a2d] font-bold text-sm flex items-center justify-center">
            BR
        </button>
    </div>
</header>