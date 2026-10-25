<?php
// Pastikan variabel ada agar tidak error
$active_menu = $active_menu ?? 'beranda';
?>
<!-- SIDEBAR UTAMA -->
<aside class="w-64 bg-[#455a2d] text-white flex flex-col h-full shrink-0 shadow-md z-30">
    <!-- Bagian Logo -->
    <div class="p-6 flex items-center gap-3">
        <div class="w-10 h-10 border-2 border-white rounded-full flex items-center justify-center">
            <i class="ph ph-graduation-cap text-2xl"></i>
        </div>
        <div>
            <h1 class="font-bold text-lg leading-tight">SI-KESA</h1>
            <p class="text-[10px] text-gray-300">SDN Kedungsari 1</p>
        </div>
    </div>

    <!-- Bagian Menu -->
    <div class="flex-1 overflow-y-auto px-4 py-2 space-y-6 scrollbar-hide">
        <div>
            <p class="text-[10px] font-bold text-gray-400 tracking-wider mb-2 ml-2">UTAMA</p>
            <nav class="space-y-1">
                <a href="../dashboard/index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'beranda' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-house text-xl"></i> Beranda
                </a>
                <a href="../absensi/list.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'absensi' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-check-square-offset text-xl"></i> Absensi
                </a>
                <a href="../nilai/input.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'nilai' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-book-bookmark text-xl"></i> Nilai
                </a>
                <a href="../jadwal/index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'jadwal' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-calendar-blank text-xl"></i> Jadwal
                </a>
            </nav>
        </div>
        <div>
            <p class="text-[10px] font-bold text-gray-400 tracking-wider mb-2 ml-2">KELAS</p>
            <nav class="space-y-1">
                <a href="../piket/index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'piket' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-broom text-xl"></i> Piket
                </a>
                <a href="../disiplin/index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'disiplin' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-shield-check text-xl"></i> Kedisiplinan
                </a>
                <a href="../laporan/index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'laporan' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-file-text text-xl"></i> Laporan
                </a>
            </nav>
        </div>
    </div>

    <!-- Bagian Profil Bawah -->
    <div class="p-4 border-t border-white/10">
        <a href="../profil/ubah-password.php" class="flex items-center justify-between hover:bg-white/10 p-2 rounded-xl cursor-pointer transition">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white text-[#455a2d] flex items-center justify-center font-bold text-xs">BR</div>
                <div>
                    <p class="text-sm font-semibold">Rina Wulandari</p>
                    <p class="text-[10px] text-gray-300">Wali Kelas 4A</p>
                </div>
            </div>
            <i class="ph ph-sign-out text-xl text-gray-300"></i>
        </a>
    </div>
</aside>