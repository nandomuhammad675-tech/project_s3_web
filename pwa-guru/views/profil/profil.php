<?php 
// 1. Konfigurasi Halaman & Menu Aktif
$active_menu = 'profil';
$page_title  = 'Profil Saya';

// Data Dummy Guru
$guru = [
    'nama'    => 'Rina Wulandari, S.Pd.',
    'nip'     => '19880412 201503 2 004',
    'email'   => 'rina.wulandari@sdnkedungsari1.sch.id',
    'telepon' => '081234567890',
    'peran'   => 'Wali Kelas 4A',
    'status'  => 'Guru Aktif'
];

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
                <div class="w-16 h-16 rounded-full bg-[#455a2d] text-white font-bold text-xl flex items-center justify-center shrink-0">
                    RW
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-lg font-bold text-[#1f2937]"><?= htmlspecialchars($guru['nama']) ?></h3>
                        <span class="bg-[#f7f6f0] text-[#455a2d] text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-[#455a2d]/20">
                            <?= htmlspecialchars($guru['status']) ?>
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium"><?= htmlspecialchars($guru['peran']) ?> &middot; NIP. <?= htmlspecialchars($guru['nip']) ?></p>
                </div>
            </div>

            <!-- 2. INFORMASI DATA DIRI -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center gap-2.5 mb-5 pb-3 border-b border-gray-100">
                    <i class="ph ph-user-circle text-xl text-[#eba048]"></i>
                    <h3 class="text-base font-bold text-[#1f2937]">Informasi Data Diri</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1.5 block">NAMA LENGKAP</label>
                        <input type="text" value="<?= htmlspecialchars($guru['nama']) ?>" class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-800 bg-[#f8f9fa] focus:outline-none focus:border-[#eba048] transition">
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1.5 block">NIP</label>
                        <input type="text" value="<?= htmlspecialchars($guru['nip']) ?>" class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-400 bg-gray-50 cursor-not-allowed" readonly>
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1.5 block">EMAIL</label>
                        <input type="email" value="<?= htmlspecialchars($guru['email']) ?>" class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-800 bg-[#f8f9fa] focus:outline-none focus:border-[#eba048] transition">
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1.5 block">NOMOR TELEPON / WA</label>
                        <input type="text" value="<?= htmlspecialchars($guru['telepon']) ?>" class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-800 bg-[#f8f9fa] focus:outline-none focus:border-[#eba048] transition">
                    </div>
                </div>

                <!-- Tombol Aksi: Ganti Password & Simpan Profil -->
                <div class="flex items-center justify-end gap-3 mt-6">
                    <a href="ubah-password.php" class="px-5 py-2.5 rounded-xl border border-[#455a2d] text-[#455a2d] font-bold text-xs hover:bg-[#f7f6f0] transition flex items-center gap-1.5">
                        <i class="ph ph-lock-key text-base"></i> Ganti Password
                    </a>
                    <button type="button" class="bg-[#eba048] hover:bg-orange-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                        <i class="ph ph-floppy-disk text-base"></i> Simpan Profil
                    </button>
                </div>
            </div>

        </div>

    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>