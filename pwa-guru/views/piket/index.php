<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'piket';
$page_title = 'Piket';
$page_scripts = ['piket.js', 'page-piket.js'];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>
<script>window.SIKESA_PAGE = { butuhKelas: true };</script>

<!-- WRAPPER UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN PIKET -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight" data-kelas-awalan="Piket Kelas">Piket Kelas</h2>
            <p class="text-[13px] text-gray-500">Piket berulang setiap hari, bukan per tanggal.</p>
        </div>

        <!-- Info Alert Biru -->
        <div class="bg-[#eff6ff] border border-[#dbeafe] rounded-xl px-4 py-2.5 flex items-center gap-2.5 mb-5 shrink-0">
            <i class="ph ph-info text-[#3b82f6] text-lg"></i>
            <p class="text-[13px] text-[#2563eb] font-medium">Atur siswa bertugas dari Senin sampai Sabtu.</p>
        </div>

        <!-- Grid Piket (diisi page-piket.js) -->
        <div id="gridPiket" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 pb-12 items-start" aria-live="polite">
            <div class="skel h-36"></div><div class="skel h-36"></div><div class="skel h-36"></div>
        </div>

    </main>

    <!-- INJECT FILE FORM MODAL DISINI -->
    <?php include __DIR__ . '/form.php'; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>