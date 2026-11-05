<?php
// Pastikan variabel ada agar tidak error
require_once __DIR__ . '/../../config.php';
$active_menu = $active_menu ?? 'beranda';
?>
<!-- SIDEBAR UTAMA -->
<aside class="w-64 bg-[#455a2d] text-white flex flex-col h-full shrink-0 shadow-md z-30">
    <!-- Bagian Logo -->
    <div class="p-6 flex items-center gap-3">
        <!-- FOTO LOGO SEKOLAH -->
        <img src="<?= sk_e($sk['base']) ?>/assets/icons/logosekolah.png" alt="Logo SI-KESA" class="w-12 h-12 object-contain shrink-0">
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
                <a href="<?= sk_url('dashboard') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'beranda' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-house text-xl"></i> Beranda
                </a>
                <a href="<?= sk_url('absensi') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'absensi' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-check-square-offset text-xl"></i> Absensi
                </a>
                <a href="<?= sk_url('nilai') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'nilai' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-book-bookmark text-xl"></i> Nilai
                </a>
                <a href="<?= sk_url('jadwal') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'jadwal' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-calendar-blank text-xl"></i> Jadwal
                </a>
            </nav>
        </div>

        <div>
            <p class="text-[10px] font-bold text-gray-400 tracking-wider mb-2 ml-2">KELAS</p>
            <nav class="space-y-1">
                <a href="<?= sk_url('piket') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'piket' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-broom text-xl"></i> Piket
                </a>
                <a href="<?= sk_url('disiplin') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'disiplin' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-shield-check text-xl"></i> Kedisiplinan
                </a>
                <a href="<?= sk_url('laporan') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= $active_menu == 'laporan' ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-file-text text-xl"></i> Laporan
                </a>
            </nav>
        </div>

        <div>
            <p class="text-[10px] font-bold text-gray-400 tracking-wider mb-2 ml-2">AKUN</p>
            <nav class="space-y-1">
                <a href="<?= sk_url('profil') ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition <?= in_array($active_menu, ['profil', 'akun']) ? 'bg-[#eba048] text-white font-medium' : 'text-gray-300 hover:bg-white/10' ?>">
                    <i class="ph ph-user text-xl"></i> Akun
                </a>
            </nav>
        </div>
    </div>

    <!-- Pasang sebagai aplikasi -->
    <div class="px-4 pb-2">
        <button type="button" data-pwa-install hidden class="w-full flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 rounded-xl py-2 text-xs font-bold transition">
            <i class="ph ph-download-simple text-base"></i> Pasang Aplikasi
        </button>
    </div>

    <!-- Bagian Profil Bawah -->
    <div class="p-4 border-t border-white/10">
        <div class="flex items-center justify-between gap-2">
            <a href="<?= sk_url('profil') ?>" class="flex items-center gap-3 p-2 rounded-xl cursor-pointer transition flex-1 min-w-0 <?= in_array($active_menu, ['profil', 'akun']) ? 'bg-white/10' : 'hover:bg-white/10' ?>" title="Profil Saya">
                <div id="profilInisial" class="w-8 h-8 rounded-full bg-white text-[#455a2d] flex items-center justify-center font-bold text-xs shrink-0">--</div>
                <div class="min-w-0">
                    <p id="profilNama" class="text-sm font-semibold truncate">Memuat...</p>
                    <p id="profilPeran" class="text-[10px] text-gray-300 truncate">&nbsp;</p>
                </div>
            </a>
            <button type="button" data-action="logout" class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white/10 transition shrink-0" title="Keluar" aria-label="Keluar">
                <i class="ph ph-sign-out text-xl text-gray-300"></i>
            </button>
        </div>
    </div>
</aside>