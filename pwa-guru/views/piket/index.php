<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'piket';
$page_title = 'Piket';

// Simulasi Data Piket dari Database
$piket_mingguan = [
    'Senin' => [
        ['inisial' => 'AP', 'nama' => 'Aditya Pratama'],
        ['inisial' => 'AR', 'nama' => 'Alya Rahmani'],
    ],
    'Selasa' => [
        ['inisial' => 'AR', 'nama' => 'Alya Rahmani'],
        ['inisial' => 'BM', 'nama' => 'Bagas Maulana'],
    ],
    'Rabu' => [
        ['inisial' => 'BM', 'nama' => 'Bagas Maulana'],
        ['inisial' => 'CL', 'nama' => 'Citra Lestari'],
    ],
    'Kamis' => [], // Kosong 
    'Jumat' => [
        ['inisial' => 'DS', 'nama' => 'Dimas Saputra'],
        ['inisial' => 'FR', 'nama' => 'Fahri Ramadhan'],
    ],
    'Sabtu' => [
        ['inisial' => 'FR', 'nama' => 'Fahri Ramadhan'],
        ['inisial' => 'GA', 'nama' => 'Gita Anindya'],
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

    <!-- AREA SCROLL KONTEN PIKET -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Piket Kelas 4A</h2>
            <p class="text-[13px] text-gray-500">Piket berulang setiap hari, bukan per tanggal.</p>
        </div>

        <!-- Info Alert Biru -->
        <div class="bg-[#eff6ff] border border-[#dbeafe] rounded-xl px-4 py-2.5 flex items-center gap-2.5 mb-5 shrink-0">
            <i class="ph ph-info text-[#3b82f6] text-lg"></i>
            <p class="text-[13px] text-[#2563eb] font-medium">Atur siswa bertugas dari Senin sampai Sabtu.</p>
        </div>

        <!-- Grid Piket -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 pb-12 items-start">
            
            <?php foreach($piket_mingguan as $hari => $petugas): ?>
                <?php if(count($petugas) > 0): ?>
                    <!-- KARTU TERISI -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col w-full">
                        
                        <!-- Header Kartu (Ditambah garis bawah) -->
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2 text-[#1f2937] font-bold text-[13px] uppercase tracking-wider">
                                <i class="ph ph-broom text-[#455a2d] text-sm bg-[#f7f6f0] p-1.5 rounded-md"></i> <?= $hari ?>
                            </div>
                            <button type="button" onclick="bukaModalPiket('<?= $hari ?>', 'Ubah')" class="bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 px-3.5 py-1.5 rounded-lg text-[11px] font-bold transition">
                                Ubah
                            </button>
                        </div>

                        <!-- List Siswa -->
                        <div class="space-y-2 mb-3.5">
                            <?php foreach($petugas as $siswa): ?>
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-full bg-[#f7f6f0] flex items-center justify-center text-[9px] font-bold text-[#455a2d] shrink-0">
                                    <?= $siswa['inisial'] ?>
                                </div>
                                <p class="text-[13px] font-semibold text-gray-700"><?= $siswa['nama'] ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Footer Kartu -->
                        <div class="pt-2.5 border-t border-gray-100 text-[10px] text-gray-400 font-medium">
                            Pastikan kelas bersih sebelum pulang.
                        </div>
                    </div>
                
                <?php else: ?>
                    <!-- KARTU KOSONG -->
                    <div class="bg-gray-50/50 rounded-xl p-4 border border-dashed border-gray-300 flex flex-col w-full">
                        
                        <!-- Header Kartu Kosong (Ditambah garis bawah transparan/abu-abu terang) -->
                        <div class="flex items-center gap-2 text-gray-500 font-bold text-[13px] uppercase tracking-wider pb-3 mb-3 border-b border-gray-200">
                            <i class="ph ph-broom text-gray-400 text-sm"></i> <?= $hari ?>
                        </div>
                        
                        <p class="text-[11px] text-gray-400 font-medium mb-3">Belum ada petugas</p>
                        
                        <!-- Tombol Tambah -->
                        <button type="button" onclick="bukaModalPiket('<?= $hari ?>', 'Tambah')" class="w-full bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 py-2 rounded-xl text-[11px] font-bold flex items-center justify-center gap-1.5 transition shadow-sm">
                            <i class="ph ph-plus text-sm"></i> Tambah
                        </button>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>

    </main>

    <!-- INJECT FILE FORM MODAL DISINI -->
    <?php include 'form.php'; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>