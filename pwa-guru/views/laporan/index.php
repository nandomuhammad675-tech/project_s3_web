<?php 
// 1. Konfigurasi Halaman & Menu Aktif
$active_menu = 'laporan';
$page_title = 'Laporan';

// 2. Data Dummy Siswa & Nilai Rekap
$daftar_siswa = [
    ['id' => 1, 'nama' => 'Aditya Pratama', 'nis' => '12345'],
    ['id' => 2, 'nama' => 'Alya Rahmani', 'nis' => '12346'],
    ['id' => 3, 'nama' => 'Bagas Maulana', 'nis' => '12347'],
    ['id' => 4, 'nama' => 'Citra Lestari', 'nis' => '12348'],
];

$nilai_uh = [
    ['mapel' => 'Matematika', 'uh1' => 90, 'uh2' => 90, 'uh3' => 90],
    ['mapel' => 'Bahasa Indonesia', 'uh1' => 80, 'uh2' => 80, 'uh3' => 80],
    ['mapel' => 'IPA', 'uh1' => 88, 'uh2' => 88, 'uh3' => 88],
    ['mapel' => 'IPS', 'uh1' => 77, 'uh2' => 77, 'uh3' => 77],
    ['mapel' => 'Bahasa Inggris', 'uh1' => 60, 'uh2' => 60, 'uh3' => 60],
    ['mapel' => 'PJOK', 'uh1' => 60, 'uh2' => 60, 'uh3' => 60],
    ['mapel' => 'Seni Budaya', 'uh1' => 95, 'uh2' => 95, 'uh3' => 95],
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
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Laporan Rekap Siswa</h2>
            <p class="text-[13px] text-gray-500">Pratinjau dan unduh data rekap nilai per individu siswa.</p>
        </div>

        <!-- Filter Siswa, Semester & Jenis Penilaian (Presisi Warna & Font Halaman Nilai) -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-6 shrink-0">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-center">
                
                <!-- 1. PILIH SISWA -->
                <div>
                    <label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-2 block">PILIH SISWA</label>
                    <select id="select-siswa" class="w-full h-11 border border-gray-100 rounded-xl px-3.5 text-sm font-bold text-gray-800 bg-[#f8f9fa] focus:outline-none focus:border-[#eba048] transition">
                        <?php foreach ($daftar_siswa as $siswa): ?>
                            <option value="<?= $siswa['id'] ?>"><?= htmlspecialchars($siswa['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 2. SEMESTER -->
                <div>
                    <label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-2 block">SEMESTER</label>
                    <div class="inline-flex bg-[#f8f9fa] p-1 rounded-xl border border-gray-100 w-full h-11 items-center">
                        <button type="button" class="flex-1 h-full bg-[#eba048] text-white rounded-lg text-xs font-bold shadow-sm transition">
                            Ganjil
                        </button>
                        <button type="button" class="flex-1 h-full text-gray-400 hover:text-gray-600 rounded-lg text-xs font-bold transition">
                            Genap
                        </button>
                    </div>
                </div>

                <!-- 3. JENIS PENILAIAN -->
                <div>
                    <label class="text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-2 block">JENIS PENILAIAN</label>
                    <div class="inline-flex bg-[#f8f9fa] p-1 rounded-xl border border-gray-100 w-full h-11 items-center">
                        <button type="button" id="tab-uh" onclick="switchTab('uh')" class="flex-1 h-full rounded-lg font-bold text-xs flex items-center justify-center gap-1.5 transition-all bg-[#eba048] text-white shadow-sm">
                            <i class="ph ph-check-circle text-sm tab-icon"></i> UH
                        </button>
                        <button type="button" id="tab-asts" onclick="switchTab('asts')" class="flex-1 h-full rounded-lg font-bold text-xs flex items-center justify-center gap-1.5 transition-all text-gray-400 hover:text-gray-600">
                            <i class="ph ph-check-circle text-sm tab-icon hidden"></i> ASTS
                        </button>
                        <button type="button" id="tab-asas" onclick="switchTab('asas')" class="flex-1 h-full rounded-lg font-bold text-xs flex items-center justify-center gap-1.5 transition-all text-gray-400 hover:text-gray-600">
                            <i class="ph ph-check-circle text-sm tab-icon hidden"></i> ASAS
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tabel Rekap Nilai Siswa -->
        <div id="content-uh" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6 shrink-0">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f8f9fa] text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                        <th class="py-3.5 px-5">Mata Pelajaran</th>
                        <th class="py-3.5 px-4 text-center w-32">UH-1</th>
                        <th class="py-3.5 px-4 text-center w-32">UH-2</th>
                        <th class="py-3.5 px-4 text-center w-32">UH-3</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-800">
                    <?php foreach ($nilai_uh as $row): ?>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3.5 px-5 font-bold text-gray-800"><?= htmlspecialchars($row['mapel']) ?></td>
                            <td class="py-3.5 px-4 text-center font-bold text-[#eba048]"><?= $row['uh1'] ?></td>
                            <td class="py-3.5 px-4 text-center font-bold text-[#eba048]"><?= $row['uh2'] ?></td>
                            <td class="py-3.5 px-4 text-center font-bold text-[#eba048]"><?= $row['uh3'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Content Dummy ASTS & ASAS (Hidden) -->
        <div id="content-asts" class="hidden bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 text-xs mb-6 shrink-0">
            Rekap Nilai ASTS belum tersedia.
        </div>
        <div id="content-asas" class="hidden bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 text-xs mb-6 shrink-0">
            Rekap Nilai ASAS belum tersedia.
        </div>

        <!-- Tombol Aksi Pratinjau & Unduh -->
        <div class="flex items-center justify-end gap-3 mb-8 shrink-0">
            <button type="button" class="px-5 py-2.5 rounded-xl border border-[#455a2d] text-[#455a2d] font-bold text-xs hover:bg-[#f7f6f0] transition flex items-center gap-1.5">
                <i class="ph ph-eye text-base"></i> Lihat Pratinjau
            </button>
            <button type="button" class="bg-[#eba048] hover:bg-orange-500 text-white px-5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-sm">
                <i class="ph ph-download-simple text-base"></i> Unduh Excel Nilai Siswa
            </button>
        </div>

        <!-- Alert Peringatan (Kuning Ringkas) -->
        <div class="bg-[#fffbeb] border border-[#fef08a] rounded-xl p-3 flex items-center gap-2.5 shadow-sm shrink-0 mb-6">
            <i class="ph ph-warning text-[#d97706] text-lg mb-0.5"></i>
            <p class="text-xs text-[#d97706] font-medium">Anda hanya dapat melihat laporan tahun ajaran aktif (2026/2027).</p>
        </div>

    </main>
</div>

<script>
function switchTab(type) {
    const tabs = ['uh', 'asts', 'asas'];
    
    tabs.forEach(t => {
        document.getElementById('content-' + t).classList.add('hidden');
        
        const btn = document.getElementById('tab-' + t);
        const icon = btn.querySelector('.tab-icon');
        
        btn.className = "flex-1 h-full rounded-lg font-bold text-xs flex items-center justify-center gap-1.5 transition-all text-gray-400 hover:text-gray-600";
        if (icon) icon.classList.add('hidden');
    });

    document.getElementById('content-' + type).classList.remove('hidden');
    
    const activeBtn = document.getElementById('tab-' + type);
    const activeIcon = activeBtn.querySelector('.tab-icon');
    
    activeBtn.className = "flex-1 h-full rounded-lg font-bold text-xs flex items-center justify-center gap-1.5 transition-all bg-[#eba048] text-white shadow-sm";
    if (activeIcon) activeIcon.classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>