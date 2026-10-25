<?php 
// Tentukan menu aktif
$active_menu = 'absensi';
$page_title = 'Absensi';

// Simulasi Data Siswa dari Database
$students = [
    ['no' => '01', 'name' => 'Aditya Pratama', 'status' => 'H'],
    ['no' => '02', 'name' => 'Alya Rahmani', 'status' => 'H'],
    ['no' => '03', 'name' => 'Bagas Maulana', 'status' => 'S'],
    ['no' => '04', 'name' => 'Citra Lestari', 'status' => 'H'],
    ['no' => '05', 'name' => 'Dimas Saputra', 'status' => 'H'],
    ['no' => '06', 'name' => 'Fahri Ramadhan', 'status' => 'I'],
    ['no' => '07', 'name' => 'Gita Anindya', 'status' => 'H'],
    ['no' => '08', 'name' => 'Hanif Akbar', 'status' => 'H'],
    // Tambahkan 1 siswa lagi agar bisa di-scroll
    ['no' => '09', 'name' => 'Iqbal Ramadhan', 'status' => 'A'],
];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- WRAPPER KONTEN UTAMA (Tambahkan overflow-hidden disini) -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
   <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN ABSENSI -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Absensi Kelas 4A</h2>
            <p class="text-[13px] text-gray-500">Catat kehadiran seluruh siswa dalam satu kali simpan.</p>
        </div>

        <!-- Pemilih Tanggal -->
        <div class="px-10 mb-6 shrink-0">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
                <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-50 text-gray-600 transition">
                    <i class="ph ph-caret-left text-lg"></i>
                </button>
                <div class="flex items-center gap-3">
                    <i class="ph ph-calendar-blank text-2xl text-[#eba048]"></i>
                    <div>
                        <p class="text-sm font-bold text-gray-800 leading-tight">Senin, 5 Oktober 2026</p>
                        <p class="text-[10px] text-gray-500">Hari ini</p>
                    </div>
                </div>
                <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-50 text-gray-600 transition">
                    <i class="ph ph-caret-right text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Tombol Semua Hadir -->
        <div class="px-10 mb-4 shrink-0">
            <button class="border border-[#455a2d] text-[#455a2d] px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 hover:bg-[#e6e4dc] transition bg-transparent">
                <i class="ph ph-check-circle text-lg"></i> Semua Hadir
            </button>
        </div>

        <!-- Daftar Siswa (Looping) - padding bawah dikurangi jadi pb-6 -->
        <div class="px-10 pb-6 space-y-3 flex-1">
            <?php foreach($students as $index => $student): ?>
            <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-lg bg-[#f7f6f0] flex items-center justify-center text-xs font-bold text-[#455a2d]">
                        <?= $student['no'] ?>
                    </div>
                    <p class="font-semibold text-gray-800 text-sm"><?= $student['name'] ?></p>
                </div>
                
                <!-- Opsi Radio Absensi -->
                <div class="flex gap-2">
                    <!-- Hadir -->
                    <label class="cursor-pointer">
                        <input type="radio" name="status_<?= $index ?>" value="H" class="peer sr-only" <?= $student['status'] == 'H' ? 'checked' : '' ?>>
                        <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-sm font-bold text-gray-500 peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600 hover:bg-gray-50 transition">H</div>
                    </label>
                    <!-- Sakit -->
                    <label class="cursor-pointer">
                        <input type="radio" name="status_<?= $index ?>" value="S" class="peer sr-only" <?= $student['status'] == 'S' ? 'checked' : '' ?>>
                        <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-sm font-bold text-gray-500 peer-checked:bg-[#eba048] peer-checked:text-white peer-checked:border-[#eba048] hover:bg-gray-50 transition">S</div>
                    </label>
                    <!-- Izin -->
                    <label class="cursor-pointer">
                        <input type="radio" name="status_<?= $index ?>" value="I" class="peer sr-only" <?= $student['status'] == 'I' ? 'checked' : '' ?>>
                        <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-sm font-bold text-gray-500 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">I</div>
                    </label>
                    <!-- Alpa -->
                    <label class="cursor-pointer">
                        <input type="radio" name="status_<?= $index ?>" value="A" class="peer sr-only" <?= $student['status'] == 'A' ? 'checked' : '' ?>>
                        <div class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-sm font-bold text-gray-500 peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600 hover:bg-gray-50 transition">A</div>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- FLOATING BOTTOM BAR (Sekarang DILUAR tag main, jadi tidak akan ikut ke-scroll) -->
    <div class="shrink-0 bg-white border-t border-gray-200 px-10 py-4 flex items-center justify-between z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] relative">
        <div class="flex items-center gap-4 text-[11px] font-bold text-gray-600">
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-600"></span> H 28</div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#eba048]"></span> S 1</div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-600"></span> I 1</div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-600"></span> A 0</div>
        </div>
        <div class="text-right">
            <button class="bg-[#eba048] hover:bg-orange-500 text-white px-6 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 transition mb-1 ml-auto">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Absensi
            </button>
            <p class="text-[9px] text-gray-400">Satu permintaan batch untuk seluruh siswa</p>
        </div>
    </div>
    
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>