<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'jadwal';
$page_title = 'Jadwal';

// Simulasi Data Jadwal dari Database (Ganjil)
$jadwal_mingguan = [
    'Senin' => [
        ['jam_mulai' => '07:00', 'jam_selesai' => '08:10', 'mapel' => 'Matematika', 'materi' => 'Materi pecahan'],
        ['jam_mulai' => '08:10', 'jam_selesai' => '09:20', 'mapel' => 'Bahasa Indonesia', 'materi' => 'Teks narasi'],
    ],
    'Selasa' => [
        ['jam_mulai' => '07:00', 'jam_selesai' => '08:10', 'mapel' => 'IPAS', 'materi' => 'Ekosistem'],
    ],
    'Rabu' => [], // Kosong
    'Kamis' => [
        ['jam_mulai' => '09:40', 'jam_selesai' => '10:50', 'mapel' => 'Pendidikan Pancasila', 'materi' => 'Gotong royong'],
    ],
    'Jumat' => [], // Kosong
    'Sabtu' => [
        ['jam_mulai' => '07:00', 'jam_selesai' => '08:10', 'mapel' => 'Seni Budaya', 'materi' => 'Seni rupa'],
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

    <!-- AREA SCROLL KONTEN JADWAL -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Jadwal Pelajaran Kelas 4A</h2>
            <p class="text-[13px] text-gray-500">Susun jadwal belajar semester berjalan.</p>
        </div>

        <!-- Tab Semester (Gaya Garis Bawah yang Berfungsi) -->
        <div class="flex gap-8 border-b border-gray-200 mb-6 shrink-0">
            <button type="button" id="tabGanjil" onclick="pindahSemester('ganjil')" class="pb-2.5 border-b-2 border-[#eba048] text-[#eba048] font-bold text-[13px] transition-colors">
                Ganjil
            </button>
            <button type="button" id="tabGenap" onclick="pindahSemester('genap')" class="pb-2.5 text-gray-500 hover:text-gray-800 font-semibold text-[13px] transition-colors border-b-2 border-transparent">
                Genap
            </button>
        </div>

        <!-- AREA KONTEN: SEMESTER GANJIL -->
        <div id="kontenGanjil" class="block">
            <!-- Grid Jadwal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pb-20">
                
                <?php foreach($jadwal_mingguan as $hari => $list_jadwal): ?>
                <?php $jumlah_pelajaran = count($list_jadwal); ?>
                
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col h-full">
                    
                    <div class="flex items-center justify-between pb-3 mb-3 <?= $jumlah_pelajaran > 0 ? 'border-b border-gray-100' : 'border-b border-dashed border-gray-200' ?>">
                        <div class="flex items-center gap-2 text-[#1f2937] font-bold text-[14px]">
                            <i class="ph ph-calendar-blank text-gray-400 text-base"></i> <?= $hari ?>
                        </div>
                        <span class="bg-[#f7f6f0] text-[#455a2d] px-2.5 py-1 rounded-full text-[10px] font-bold">
                            <?= $jumlah_pelajaran ?> pelajaran
                        </span>
                    </div>

                    <?php if($jumlah_pelajaran > 0): ?>
                        <div class="flex flex-col">
                            <?php foreach($list_jadwal as $index => $item): ?>
                            <div class="flex items-center gap-3 <?= $index !== $jumlah_pelajaran - 1 ? 'border-b border-gray-100 pb-3 mb-3' : '' ?>">
                                <div class="bg-[#f7f6f0] rounded-xl p-2 flex flex-col items-center justify-center min-w-[56px] text-[10px] font-bold text-gray-600 shrink-0 leading-tight">
                                    <span><?= $item['jam_mulai'] ?></span>
                                    <span><?= $item['jam_selesai'] ?></span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-[#1f2937] text-[12px] mb-0.5"><?= $item['mapel'] ?></h4>
                                    <p class="text-[10px] text-gray-500"><?= $item['materi'] ?></p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" onclick="bukaModalJadwal()" class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition">
                                        <i class="ph ph-pencil-simple text-[13px]"></i>
                                    </button>
                                    <button class="w-7 h-7 rounded-lg border border-red-100 flex items-center justify-center text-red-500 hover:bg-red-50 transition">
                                        <i class="ph ph-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex-1 flex flex-col items-center justify-center py-5 gap-3">
                            <div class="flex items-center gap-1.5 text-[12px] text-gray-400 font-medium">
                                <i class="ph ph-calendar-x text-base"></i> Belum ada jadwal
                            </div>
                            <button type="button" onclick="bukaModalJadwal()" class="border border-gray-200 bg-gray-50 hover:bg-white text-gray-600 px-3.5 py-1.5 rounded-lg text-[11px] font-bold flex items-center gap-1.5 transition shadow-sm">
                                <i class="ph ph-plus"></i> Tambah
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- AREA KONTEN: SEMESTER GENAP (Disembunyikan secara default) -->
        <div id="kontenGenap" class="hidden flex-col items-center justify-center py-16">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 mb-4">
                <i class="ph ph-calendar-blank text-4xl text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-[#1f2937] mb-1">Semester Genap Kosong</h3>
            <p class="text-[13px] text-gray-500 mb-6">Belum ada jadwal yang disusun untuk semester ini.</p>
            <button type="button" onclick="bukaModalJadwal()" class="bg-[#eba048] hover:bg-orange-500 text-white px-5 py-2.5 rounded-xl text-[13px] font-bold transition shadow-sm flex items-center gap-2">
                <i class="ph ph-plus text-base font-bold"></i> Susun Jadwal
            </button>
        </div>

        <!-- Floating Action Button (FAB) Tambah Jadwal -->
        <button type="button" onclick="bukaModalJadwal()" class="fixed bottom-10 right-10 w-[52px] h-[52px] bg-[#eba048] rounded-full shadow-[0_8px_16px_-4px_rgba(235,160,72,0.5)] flex items-center justify-center text-white hover:bg-orange-500 transition-all hover:scale-105 z-30">
            <i class="ph ph-plus text-xl font-bold"></i>
        </button>

    </main>

    <!-- PANGGIL FORM MODAL MELAYANG -->
    <?php include 'form.php'; ?>

</div>

<!-- SCRIPT UNTUK PINDAH TAB SEMESTER -->
<script>
    function pindahSemester(semester) {
        // Ambil elemen Tab
        const tabGanjil = document.getElementById('tabGanjil');
        const tabGenap = document.getElementById('tabGenap');
        
        // Ambil elemen Konten
        const kontenGanjil = document.getElementById('kontenGanjil');
        const kontenGenap = document.getElementById('kontenGenap');

        // Class CSS untuk tab aktif dan tidak aktif
        const activeClass = 'pb-2.5 border-b-2 border-[#eba048] text-[#eba048] font-bold text-[13px] transition-colors';
        const inactiveClass = 'pb-2.5 text-gray-500 hover:text-gray-800 font-semibold text-[13px] transition-colors border-b-2 border-transparent';

        if (semester === 'ganjil') {
            // Ubah gaya tab
            tabGanjil.className = activeClass;
            tabGenap.className = inactiveClass;
            
            // Tampilkan konten ganjil, sembunyikan genap
            kontenGanjil.classList.remove('hidden');
            kontenGanjil.classList.add('block');
            
            kontenGenap.classList.remove('flex');
            kontenGenap.classList.add('hidden');
        } else {
            // Ubah gaya tab
            tabGenap.className = activeClass;
            tabGanjil.className = inactiveClass;
            
            // Tampilkan konten genap, sembunyikan ganjil
            kontenGenap.classList.remove('hidden');
            kontenGenap.classList.add('flex'); // Pakai flex agar letaknya sejajar di tengah
            
            kontenGanjil.classList.remove('block');
            kontenGanjil.classList.add('hidden');
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>