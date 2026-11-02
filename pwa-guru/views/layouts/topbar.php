<?php
// Dummy state untuk UI (ganti true/false manual untuk tes tampilan)
$absensi_belum_diisi = true; 
?>

<header class="bg-white px-8 py-4 flex justify-between items-center shadow-sm z-20">
    <div class="text-sm text-gray-500 font-medium flex gap-2">
        <span>Guru</span> 
        <span>&gt;</span> 
        <span class="text-gray-800 font-semibold"><?= $page_title ?? 'Beranda' ?></span>
    </div>
    
    <div class="flex items-center gap-4">
        
        <!-- Wrapper Notifikasi -->
        <div class="relative">
            <!-- Tombol Lonceng -->
            <button id="btn-notif" type="button" class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 relative transition-colors focus:outline-none">
                <i class="ph ph-bell text-xl"></i>
                
                <?php if ($absensi_belum_diisi): ?>
                    <!-- Indikator Merah Berkedip -->
                    <span class="absolute top-2 right-2 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 border border-white"></span>
                    </span>
                <?php endif; ?>
            </button>

            <!-- Panel Dropdown Notifikasi -->
            <div id="dropdown-notif" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-semibold text-gray-700 text-sm">Notifikasi</h3>
                    <?php if ($absensi_belum_diisi): ?>
                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-full">1 Baru</span>
                    <?php endif; ?>
                </div>
                
                <div class="max-h-80 overflow-y-auto">
                    <?php if ($absensi_belum_diisi): ?>
                        <!-- Card Peringatan Absensi -->
                        <a href="#" class="block p-4 hover:bg-gray-50 transition border-l-4 border-orange-500">
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-full bg-orange-100 text-orange-600 flex-shrink-0">
                                    <i class="ph ph-warning-circle text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm text-gray-800">Absensi Belum Diisi</p>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Anda belum mengisi absensi hari ini. Catat kehadiran sebelum pelajaran dimulai.</p>
                                </div>
                            </div>
                        </a>
                    <?php else: ?>
                        <!-- State Jika Sudah Absen -->
                        <div class="p-6 text-center flex flex-col items-center justify-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 mb-2">
                                <i class="ph ph-check-circle text-2xl"></i>
                            </div>
                            <p class="text-sm text-gray-500 font-medium">Semua tugas sudah selesai!</p>
                            <p class="text-xs text-gray-400 mt-1">Tidak ada notifikasi baru.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profil Guru -->
        <button type="button" class="w-10 h-10 rounded-full bg-[#E5E0D8] text-[#455a2d] font-bold text-sm flex items-center justify-center">
            BR
        </button>
    </div>
</header>

<!-- JavaScript Toggle Dropdown (Klik Luar untuk Nutup) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnNotif = document.getElementById('btn-notif');
    const dropdownNotif = document.getElementById('dropdown-notif');

    if (btnNotif && dropdownNotif) {
        btnNotif.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownNotif.classList.toggle('hidden');
        });

        document.addEventListener('click', function(e) {
            if (!btnNotif.contains(e.target) && !dropdownNotif.contains(e.target)) {
                dropdownNotif.classList.add('hidden');
            }
        });
    }
});
</script>