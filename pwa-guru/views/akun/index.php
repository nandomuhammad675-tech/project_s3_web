<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'akun';
$page_title = 'Akun';

// Simulasi Data Pengguna
$user = [
    'nama' => 'Rina Wulandari, S.Pd.',
    'nip' => '198605122010012018',
    'inisial' => 'BR',
    'peran' => 'Guru Kelas · Wali 4A'
];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- WRAPPER KONTEN UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN AKUN -->
    <main class="flex-1 overflow-y-auto px-10 py-5 flex flex-col relative">
        
        <!-- Header Halaman -->
        <div class="shrink-0 mb-4">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-0.5 leading-tight">Akun</h2>
            <p class="text-[13px] text-gray-500">Profil dan pengaturan akun Guru.</p>
        </div>

        <!-- CONTAINER UTAMA (Lebar Penuh & Jarak Rapat) -->
        <div class="w-full space-y-3">
            
            <!-- CARD PROFIL GURU (Padding dirapatkan) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                <!-- Avatar Inisial -->
                <div class="w-16 h-16 rounded-full bg-[#e3e8d8] flex items-center justify-center text-[#455a2d] font-bold text-xl mb-3 shadow-inner">
                    <?= $user['inisial'] ?>
                </div>

                <!-- Nama Lengkap -->
                <h3 class="text-lg font-bold text-[#1f2937] mb-0.5">
                    <?= $user['nama'] ?>
                </h3>

                <!-- NIP -->
                <p class="text-[11px] text-gray-400 font-mono mb-2.5">
                    NIP <?= $user['nip'] ?>
                </p>

                <!-- Badge Peran -->
                <span class="bg-[#f2f5eb] text-[#455a2d] px-3.5 py-1 rounded-full text-xs font-bold">
                    <?= $user['peran'] ?>
                </span>
            </div>

            <!-- CARD PENGATURAN & AKSI -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100 overflow-hidden">
                
                <!-- Opsi: Ganti Password -->
                <a href="ganti-password.php" class="p-3.5 flex items-center justify-between hover:bg-gray-50/80 transition group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-[#f2f5eb] flex items-center justify-center text-[#455a2d] group-hover:scale-105 transition-transform">
                            <i class="ph ph-lock-key text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-xs mb-0.5">Ganti Password</h4>
                            <p class="text-[11px] text-gray-400">Perbarui kata sandi akun</p>
                        </div>
                    </div>
                    <i class="ph ph-caret-right text-gray-400 text-xs group-hover:text-gray-600 group-hover:translate-x-0.5 transition-all"></i>
                </a>

                <!-- Opsi: Keluar -->
                <a href="../auth/logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="p-3.5 flex items-center justify-between hover:bg-red-50/50 transition group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center text-red-500 group-hover:scale-105 transition-transform">
                            <i class="ph ph-sign-out text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-red-600 text-xs mb-0.5">Keluar</h4>
                            <p class="text-[11px] text-gray-400">Akhiri sesi di perangkat ini</p>
                        </div>
                    </div>
                    <i class="ph ph-caret-right text-red-400 text-xs group-hover:text-red-600 group-hover:translate-x-0.5 transition-all"></i>
                </a>

            </div>

            <!-- FOOTER VERSI APLIKASI -->
            <p class="text-center text-[10px] text-gray-400 pt-1 font-medium">
                SI-KESA PWA Guru · Versi 1.0.0
            </p>

        </div>

    </main>
    
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>