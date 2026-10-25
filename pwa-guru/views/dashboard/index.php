<?php 
// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- WRAPPER KONTEN UTAMA -->
<div class="flex-1 flex flex-col h-full relative">
    
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN DASHBOARD -->
    <main class="flex-1 overflow-y-auto">
        <!-- Hero Banner -->
        <div class="bg-[#455a2d] text-white px-10 pt-10 pb-24">
            <p class="text-sm text-gray-200 mb-2">Senin, 5 Oktober 2026</p>
            <h2 class="text-3xl font-bold mb-2">Selamat pagi, Bu Rina</h2>
            <p class="text-gray-200">Semoga kegiatan belajar hari ini berjalan lancar.</p>
        </div>

        <!-- Area Card -->
        <div class="px-10 -mt-14 pb-12 relative z-10 space-y-6 max-w-5xl">
            
            <!-- Card Info Kelas -->
            <div class="bg-white rounded-2xl p-6 shadow-sm flex justify-between items-center border border-gray-100">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 tracking-wider mb-1">KELAS YANG ANDA WALI</p>
                    <h3 class="text-2xl font-bold text-gray-800 mb-1">Kelas 4A</h3>
                    <p class="text-sm text-gray-500">Tahun Ajaran 2026/2027 &bull; Semester Ganjil</p>
                </div>
                <div class="bg-[#f7f6f0] px-6 py-4 rounded-xl flex items-center gap-4">
                    <i class="ph ph-users text-3xl text-gray-600"></i>
                    <div>
                        <p class="text-2xl font-bold text-gray-800 leading-none">32</p>
                        <p class="text-xs text-gray-500 mt-1">siswa aktif</p>
                    </div>
                </div>
            </div>

            <!-- Card Notifikasi Absensi -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-l-4 border-l-[#eba048] border-y border-r border-gray-100">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-[11px] font-bold text-gray-400 tracking-wider">ABSENSI HARI INI</p>
                    <span class="bg-[#fef3e5] text-[#eba048] text-xs font-bold px-3 py-1 rounded-full">Belum</span>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Senin, 5 Oktober 2026</h3>
                <p class="text-sm text-gray-500 mb-6">Absensi belum diisi. Catat kehadiran sebelum pelajaran dimulai.</p>
                <a href="../absensi/list.php" class="inline-flex bg-[#eba048] hover:bg-orange-500 text-white px-5 py-2.5 rounded-lg text-sm font-semibold items-center gap-2 transition cursor-pointer">
                    <i class="ph ph-check-square-offset text-lg"></i> Isi Absensi
                </a>
            </div>

            <!-- Grid Menu Bawah -->
            <div class="grid grid-cols-2 gap-4">
                <a href="../nilai/input.php" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:border-gray-300 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#f7f6f0] flex items-center justify-center text-[#455a2d] group-hover:bg-[#e6e4dc] transition">
                            <i class="ph ph-book-bookmark text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Nilai</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Input dan rekap nilai</p>
                        </div>
                    </div>
                    <i class="ph ph-caret-right text-gray-400"></i>
                </a>

                <a href="../jadwal/index.php" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:border-gray-300 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#f7f6f0] flex items-center justify-center text-[#455a2d] group-hover:bg-[#e6e4dc] transition">
                            <i class="ph ph-calendar-blank text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Jadwal</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Pelajaran hari ini</p>
                        </div>
                    </div>
                    <i class="ph ph-caret-right text-gray-400"></i>
                </a>
                
                <a href="../piket/index.php" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:border-gray-300 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#f7f6f0] flex items-center justify-center text-[#455a2d] group-hover:bg-[#e6e4dc] transition">
                            <i class="ph ph-broom text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Piket</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Petugas kebersihan</p>
                        </div>
                    </div>
                    <i class="ph ph-caret-right text-gray-400"></i>
                </a>
                
                <a href="../disiplin/index.php" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:border-gray-300 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#f7f6f0] flex items-center justify-center text-[#455a2d] group-hover:bg-[#e6e4dc] transition">
                            <i class="ph ph-shield-check text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Kedisiplinan</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Catatan siswa</p>
                        </div>
                    </div>
                    <i class="ph ph-caret-right text-gray-400"></i>
                </a>
                
                <a href="../laporan/index.php" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:border-gray-300 transition">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[#f7f6f0] flex items-center justify-center text-[#455a2d] group-hover:bg-[#e6e4dc] transition">
                            <i class="ph ph-file-text text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Laporan</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Unduh data kelas</p>
                        </div>
                    </div>
                    <i class="ph ph-caret-right text-gray-400"></i>
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>