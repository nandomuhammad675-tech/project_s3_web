<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'disiplin';
$page_title = 'Kedisiplinan';

// Simulasi Data Kedisiplinan dari Database
$catatan_disiplin = [
    [
        'nama' => 'Dimas Saputra',
        'tanggal' => '5 Oktober 2026',
        'kategori' => 'Terlambat',
        'keterangan' => 'Datang 15 menit setelah bel masuk tanpa keterangan.'
    ],
    [
        'nama' => 'Bagas Maulana',
        'tanggal' => '2 Oktober 2026',
        'kategori' => 'Tidak mengerjakan PR',
        'keterangan' => 'Belum mengumpulkan tugas Matematika yang diberikan.'
    ],
    [
        'nama' => 'Fahri Ramadhan',
        'tanggal' => '28 September 2026',
        'kategori' => 'Atribut tidak lengkap',
        'keterangan' => 'Tidak memakai dasi saat upacara hari Senin.'
    ]
];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- WRAPPER UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN KEDISIPLINAN -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
       <!-- Header Halaman (Standar Seragam) -->
        <div class="flex justify-between items-start mb-5 shrink-0">
            <div>
                <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Kedisiplinan Kelas 4A</h2>
                <p class="text-[13px] text-gray-500">Catatan perkembangan kedisiplinan siswa.</p>
            </div>
            <button type="button" onclick="bukaModalDisiplin('Tambah')" class="bg-[#eba048] hover:bg-orange-500 text-white px-5 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 transition shadow-sm">
                <i class="ph ph-plus text-lg font-bold"></i> Tambah Catatan
            </button>
        </div>

        <!-- Filter Bar (Padding & Margin direnggangkan secukupnya) -->
        <div class="bg-white rounded-xl px-4 py-3 shadow-sm border border-gray-100 mb-5 flex flex-wrap gap-3 items-center shrink-0">
            
            <!-- Filter Siswa -->
            <div class="relative w-full md:w-48">
                <select class="w-full border border-gray-200 rounded-lg py-1.5 pl-3 pr-8 text-sm text-[#1f2937] font-medium bg-white focus:outline-none focus:border-[#eba048] appearance-none cursor-pointer">
                    <option>Semua Siswa</option>
                    <option>Dimas Saputra</option>
                    <option>Bagas Maulana</option>
                </select>
                <i class="ph ph-caret-down absolute right-3 top-2 text-gray-400 pointer-events-none text-xs"></i>
            </div>
            
            <!-- Filter Rentang Tanggal -->
            <div class="relative w-full md:w-52">
                <div class="w-full border border-gray-200 rounded-lg py-1.5 px-3 text-sm text-[#1f2937] font-medium bg-white flex items-center justify-between cursor-pointer">
                    <span>1-31 Oktober 2026</span>
                    <i class="ph ph-calendar-blank text-gray-500 text-[15px]"></i>
                </div>
            </div>
        </div>

        <!-- Grid Catatan (Gap dikurangi jadi gap-4) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-12 items-start">
            
            <?php foreach($catatan_disiplin as $catatan): ?>
            <!-- Card (Padding diperkecil ke p-4) -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col h-full">
                
                <!-- Nama & Tanggal -->
                <div class="flex justify-between items-center mb-1.5">
                    <h4 class="font-bold text-[#1f2937] text-[15px] leading-tight"><?= $catatan['nama'] ?></h4>
                    <span class="text-[10px] font-semibold text-gray-400"><?= $catatan['tanggal'] ?></span>
                </div>
                
                <!-- Badge Kategori Merah -->
                <div class="mb-2">
                    <span class="bg-[#fef2f2] text-red-500 px-2 py-0.5 rounded text-[10px] font-bold inline-block">
                        <?= $catatan['kategori'] ?>
                    </span>
                </div>
                
                <!-- Keterangan Detail & Tombol Ubah -->
                <div class="flex justify-between items-end gap-4 mt-auto">
                    <p class="text-[13px] text-gray-500 leading-snug flex-1">
                        <?= $catatan['keterangan'] ?>
                    </p>
                    <button type="button" onclick="bukaModalDisiplin('Ubah', '<?= $catatan['nama'] ?>')" class="shrink-0 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 px-3.5 py-1.5 rounded-lg text-[11px] font-bold flex items-center gap-1.5 transition">
                        <i class="ph ph-pencil-simple text-sm"></i> Ubah
                    </button>
                </div>
                
            </div>
            <?php endforeach; ?>

        </div>

    </main>

    <!-- PANGGIL FILE FORM MODAL DISINI -->
    <?php include 'form.php'; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>