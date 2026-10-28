<?php 
// 1. Konfigurasi Halaman & Menu Aktif
$active_menu = 'nilai';
$page_title = 'Nilai';

// 2. Simulasi Data Siswa & Nilainya (Bisa diganti query database)
$students = [
    ['no' => '01', 'name' => 'Aditya Pratama', 'score' => 88],
    ['no' => '02', 'name' => 'Alya Rahmani', 'score' => 92],
    ['no' => '03', 'name' => 'Bagas Maulana', 'score' => 78],
    ['no' => '04', 'name' => 'Citra Lestari', 'score' => 85],
    ['no' => '05', 'name' => 'Dimas Saputra', 'score' => 90],
    ['no' => '06', 'name' => 'Fahri Ramadhan', 'score' => 82],
];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- WRAPPER KONTEN UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
       <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Nilai Kelas 4A</h2>
            <p class="text-[13px] text-gray-500">Input cepat dan rekap nilai siswa.</p>
        </div>

        <!-- Tab Navigasi -->
        <div class="flex gap-8 border-b border-gray-200 mb-6 shrink-0">
            <a href="#" class="pb-3 border-b-2 border-[#eba048] text-[#eba048] font-bold text-sm">Input Nilai</a>
            <a href="#" class="pb-3 text-gray-500 hover:text-gray-800 font-semibold text-sm transition border-b-2 border-transparent">Rekap</a>
        </div>

        <!-- Panggil Komponen Pilihan Mata Pelajaran -->
        <div class="shrink-0">
            <?php require_once 'pilih-mapel.php'; ?>
        </div>

        <!-- Daftar Input Nilai Siswa (Looping) -->
        <div class="space-y-3 shrink-0">
            <?php foreach($students as $student): ?>
            <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 flex items-center justify-between">
                
                <!-- Info Siswa -->
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-lg bg-[#f7f6f0] flex items-center justify-center text-xs font-bold text-[#455a2d]">
                        <?= $student['no'] ?>
                    </div>
                    <p class="font-bold text-gray-800 text-sm"><?= $student['name'] ?></p>
                </div>
                
                <!-- Aksi Input & Hapus -->
                <div class="flex items-center gap-3">
                    <input type="text" value="<?= $student['score'] ?>" class="w-16 h-10 border border-gray-200 rounded-lg text-center font-bold text-gray-800 focus:outline-none focus:border-[#eba048] focus:ring-1 focus:ring-[#eba048] transition">
                    
                    <button type="button" class="w-10 h-10 rounded-lg bg-[#fef2f2] text-red-500 flex items-center justify-center hover:bg-red-100 transition" title="Hapus Nilai">
                        <i class="ph ph-trash text-lg"></i>
                    </button>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tombol Simpan (Scroll bersama konten) -->
        <div class="mt-8 mb-10 shrink-0">
            <button class="bg-[#eba048] hover:bg-orange-500 text-white px-6 py-3 rounded-lg text-sm font-semibold flex items-center gap-2 transition shadow-sm">
                <i class="ph ph-floppy-disk text-xl"></i> Simpan Semua Nilai
            </button>
        </div>

    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>