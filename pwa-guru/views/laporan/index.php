<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'laporan';
$page_title = 'Laporan';

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- WRAPPER UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN LAPORAN -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Laporan Kelas 4A</h2>
            <p class="text-[13px] text-gray-500">Pratinjau dan unduh data kelas tahun aktif.</p>
        </div>

        <!-- Container Laporan (Max Width agar tidak melebar penuh) -->
        <div class="max-w-3xl w-full">
            
            <!-- Kartu Laporan Akademik -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-3.5">
                
                <!-- Header Kartu -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-[#f7f6f0] text-[#455a2d] flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-file-text"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-[#1f2937] leading-none mb-1">Laporan Akademik</h3>
                        <p class="text-xs text-gray-500">File Excel berisi absensi, nilai, dan kedisiplinan.</p>
                    </div>
                </div>

                <!-- Input: Tahun Ajaran (Readonly) -->
                <div class="mb-3">
                    <label class="text-xs font-bold text-[#1f2937] mb-1 block">Tahun Ajaran</label>
                    <div class="w-full border border-gray-200 rounded-xl px-3 py-2 text-[13px] text-[#1f2937] bg-gray-50/50 flex items-center justify-between">
                        <span>2026/2027 &middot; Tahun aktif</span>
                        <span class="bg-[#eff6ff] text-[#3b82f6] px-2 py-0.5 rounded-[5px] text-[10px] font-bold tracking-wide">Otomatis</span>
                    </div>
                </div>

                <!-- Input: Semester (Toggle) -->
                <div class="mb-3">
                    <label class="text-xs font-bold text-[#1f2937] mb-1 block">Semester</label>
                    <div class="inline-flex bg-[#f7f6f0] rounded-full p-1 items-center">
                        <button type="button" class="bg-[#eba048] text-white px-5 py-1 rounded-full text-xs font-bold shadow-sm transition">
                            Ganjil
                        </button>
                        <button type="button" class="text-gray-500 hover:text-[#1f2937] px-5 py-1 rounded-full text-xs font-bold transition">
                            Genap
                        </button>
                    </div>
                </div>

                <!-- Input: Kelas (Readonly) -->
                <div class="mb-4">
                    <label class="text-xs font-bold text-[#1f2937] mb-1 block">Kelas</label>
                    <div class="w-full border border-gray-200 rounded-xl px-3 py-2 text-[13px] text-[#1f2937] bg-gray-50/50 flex items-center justify-between">
                        <span>Kelas 4A</span>
                        <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-[5px] text-[10px] font-bold tracking-wide">Otomatis</span>
                    </div>
                </div>

                <div class="w-full h-[1px] bg-gray-100 mb-4"></div> <!-- Garis Pembatas -->

                <!-- Aksi Tombol -->
                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" class="px-4 py-2 rounded-xl border border-[#455a2d] text-[#455a2d] font-bold text-xs hover:bg-[#f7f6f0] transition flex items-center gap-1.5">
                        <i class="ph ph-eye text-sm"></i> Lihat Pratinjau
                    </button>
                    <button type="button" class="px-4 py-2 rounded-xl bg-[#eba048] text-white font-bold text-xs hover:bg-orange-500 transition shadow-sm flex items-center gap-1.5">
                        <i class="ph ph-download-simple text-sm"></i> Unduh Excel
                    </button>
                </div>
                
            </div>

            <!-- Alert Peringatan (Kuning) -->
            <div class="bg-[#fffbeb] border border-[#fef08a] rounded-xl p-3 flex items-center gap-2.5 shadow-sm">
                <i class="ph ph-warning text-[#d97706] text-lg mb-0.5"></i>
                <p class="text-xs text-[#d97706] font-medium">Anda hanya dapat melihat laporan tahun ajaran aktif.</p>
            </div>

        </div>

    </main>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>