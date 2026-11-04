<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'jadwal';
$page_title = 'Jadwal';
$page_scripts = ['jadwal.js', 'page-jadwal.js'];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>
<script>window.SIKESA_PAGE = { butuhKelas: true };</script>

<!-- WRAPPER UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN JADWAL -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight" data-kelas-awalan="Jadwal Pelajaran Kelas">Jadwal Pelajaran Kelas</h2>
            <p class="text-[13px] text-gray-500">Susun jadwal belajar semester berjalan.</p>
        </div>

        <!-- Tab Semester -->
        <div class="flex gap-8 border-b border-gray-200 mb-6 shrink-0">
            <button type="button" id="tabGanjil" data-sem="ganjil" class="pb-2.5 text-[13px] font-semibold border-b-2">Ganjil</button>
            <button type="button" id="tabGenap" data-sem="genap" class="pb-2.5 text-[13px] font-semibold border-b-2">Genap</button>
        </div>
        <p class="text-xs text-gray-400 mb-4">Jadwal tidak disalin otomatis ke tahun ajaran baru; susun ulang setiap tahun ajaran.</p>

        <!-- Grid jadwal (diisi page-jadwal.js) -->
        <div id="gridJadwal" class="grid grid-cols-1 lg:grid-cols-2 gap-4 pb-24" aria-live="polite">
            <div class="skel h-32"></div><div class="skel h-32"></div>
        </div>

        <!-- FAB Tambah Jadwal -->
        <button type="button" id="fabJadwal" class="fixed bottom-8 right-8 w-14 h-14 rounded-full bg-[#eba048] hover:bg-orange-500 text-white flex items-center justify-center shadow-lg transition z-30" aria-label="Tambah jadwal">
            <i class="ph ph-plus text-2xl"></i>
        </button>
    </main>

    <!-- PANGGIL FORM MODAL MELAYANG -->
    <?php include __DIR__ . '/form.php'; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>