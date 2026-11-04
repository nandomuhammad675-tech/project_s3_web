<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'disiplin';
$page_title = 'Kedisiplinan';
$page_scripts = ['disiplin.js', 'page-disiplin.js'];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>
<script>window.SIKESA_PAGE = { butuhKelas: true };</script>

<!-- WRAPPER UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN KEDISIPLINAN -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
       <!-- Header Halaman (Standar Seragam) -->
        <div class="flex justify-between items-start mb-5 shrink-0">
            <div>
                <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight" data-kelas-awalan="Kedisiplinan Kelas">Kedisiplinan Kelas</h2>
                <p class="text-[13px] text-gray-500">Catatan perkembangan kedisiplinan siswa.</p>
            </div>
            <button type="button" id="btnTambahDisiplin" class="bg-[#eba048] hover:bg-orange-500 text-white px-5 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 transition shadow-sm">
                <i class="ph ph-plus text-lg font-bold"></i> Tambah Catatan
            </button>
        </div>

        <!-- Filter Bar (Padding & Margin direnggangkan secukupnya) -->
        <div class="bg-white rounded-xl px-4 py-3 shadow-sm border border-gray-100 mb-5 flex flex-wrap gap-3 items-center shrink-0">
            
            <!-- Filter Siswa -->
            <div class="relative w-full md:w-48">
                <select id="fltSiswa" class="w-full border border-gray-200 rounded-lg py-1.5 pl-3 pr-8 text-sm text-[#1f2937] font-medium bg-white focus:outline-none focus:border-[#eba048] appearance-none cursor-pointer" aria-label="Filter siswa">
                    <option value="">Semua Siswa</option>
                </select>
                <i class="ph ph-caret-down absolute right-3 top-2 text-gray-400 pointer-events-none text-xs"></i>
            </div>

            <!-- Filter Bulan -->
            <div class="relative w-full md:w-52">
                <input id="fltBulan" type="month" class="w-full border border-gray-200 rounded-lg py-1.5 px-3 text-sm text-[#1f2937] font-medium bg-white focus:outline-none focus:border-[#eba048]" aria-label="Filter bulan">
            </div>
            <button type="button" id="fltReset" class="text-xs font-bold text-gray-500 hover:text-gray-800">Atur ulang</button>
        </div>

        <!-- Grid Catatan (diisi page-disiplin.js) -->
        <div id="gridDisiplin" class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-12 items-start" aria-live="polite">
            <div class="skel h-28"></div><div class="skel h-28"></div>
        </div>

    </main>

    <!-- PANGGIL FILE FORM MODAL DISINI -->
    <?php include __DIR__ . '/form.php'; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>