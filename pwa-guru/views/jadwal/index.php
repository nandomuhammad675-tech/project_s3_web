<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'jadwal';
$page_title = 'Jadwal';
$page_scripts = ['jadwal.js', 'page-jadwal.js?v=3'];

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

    <!-- MODAL KONFIRMASI HAPUS JADWAL (TAILWIND MURNI - IDENTIK PWA ADMIN) -->
    <div id="dlg-hapus" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-[#18220f]/55 backdrop-blur-[2px] p-4">
        <div role="dialog" aria-modal="true" aria-labelledby="dlg-hapus-title"
             class="bg-white w-full max-w-[460px] rounded-[24px] px-7 py-6 shadow-[0_24px_60px_rgba(15,23,10,0.28)] text-left font-sans">
            
            <h2 id="dlg-hapus-title" class="text-[20px] font-bold text-[#1f2937] leading-snug mb-1.5">
                Hapus Jadwal
            </h2>
            
            <p class="text-[14px] text-[#4b5563] leading-relaxed mb-4">
                Yakin ingin menghapus? Tindakan ini tidak dapat dibatalkan.
            </p>
            
            <div id="dlg-hapus-target" class="bg-[#f7f6f0] border border-[#e5e4dc] rounded-xl px-4 py-3 text-[14px] text-[#374151] mb-6"></div>
            
            <div id="dlg-hapus-msg" hidden class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-3.5 py-2.5 text-[13px] mb-4"></div>
            
            <div class="flex items-center justify-end gap-2.5">
                <button type="button" id="dlg-hapus-cancel"
                        class="px-5 py-2.5 rounded-[10px] border-[1.5px] border-[#556b3c] bg-white hover:bg-[#f7f6f0] text-[#3f522b] text-[14px] font-semibold transition cursor-pointer">
                    Batal
                </button>
                <button type="button" id="dlg-hapus-ok"
                        class="px-5 py-2.5 rounded-[10px] bg-[#dc2626] hover:bg-[#b91c1c] disabled:opacity-60 text-white text-[14px] font-semibold transition cursor-pointer">
                    Hapus
                </button>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>