<?php 
// 1. Konfigurasi Halaman & Menu Aktif
$active_menu = 'profil';
$page_title  = 'Profil Saya';

$page_scripts = ['page-akun.js'];

// Panggil Layout Header & Sidebar (Sesuaikan path jika perlu)
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- WRAPPER KONTEN UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman -->
        <div class="shrink-0 mb-4">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Profil Saya</h2>
            <p class="text-[13px] text-gray-500">Kelola informasi data diri Anda.</p>
        </div>

        <div class="space-y-3.5 shrink-0 mb-10 max-w-4xl">
            
            <!-- 1. KARTU HEADER PROFIL -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-5">
                <div id="akInisial" class="w-16 h-16 rounded-full bg-[#455a2d] text-white font-bold text-xl flex items-center justify-center shrink-0">
                    --
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 id="akNama" class="text-lg font-bold text-[#1f2937]">Memuat...</h3>
                        <span class="bg-[#f7f6f0] text-[#455a2d] text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-[#455a2d]/20">Guru Aktif</span>
                    </div>
                    <p id="akPeran" class="text-xs text-gray-500 font-medium">&nbsp;</p>
                </div>
            </div>

            <!-- 2. INFORMASI DATA DIRI -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center gap-2.5 mb-5 pb-3 border-b border-gray-100">
                    <i class="ph ph-user-circle text-xl text-[#eba048]"></i>
                    <h3 class="text-base font-bold text-[#1f2937]">Informasi Data Diri</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1.5 block" for="akNamaIn">NAMA LENGKAP</label><input id="akNamaIn" type="text" readonly class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-800 bg-gray-50 cursor-default"></div>
                    <div><label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1.5 block" for="akUser">USERNAME</label><input id="akUser" type="text" readonly class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-800 bg-gray-50 cursor-default"></div>
                    <div><label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1.5 block" for="akKelas">WALI KELAS</label><input id="akKelas" type="text" readonly class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-800 bg-gray-50 cursor-default"></div>
                    <div><label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1.5 block" for="akTa">TAHUN AJARAN / SEMESTER</label><input id="akTa" type="text" readonly class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-800 bg-gray-50 cursor-default"></div>
                </div>
                <p class="text-[11px] text-gray-400 mt-3">NIP, email, dan nomor telepon dikelola oleh Admin sekolah.</p>

                <!-- Tombol Aksi: Ganti Password -->
                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="<?= sk_url('password') ?>" class="px-5 py-2.5 rounded-xl border border-[#455a2d] text-[#455a2d] font-bold text-xs hover:bg-[#f7f6f0] transition flex items-center gap-1.5">
                        <i class="ph ph-lock-key text-base"></i> Ganti Password
                    </a>
                </div>
            </div>

        </div>

    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>