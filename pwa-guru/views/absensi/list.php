<?php 
// Tentukan menu aktif
$active_menu = 'absensi';
$page_title = 'Absensi';
$page_scripts = ['absensi.js', 'page-absensi.js'];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>
<script>window.SIKESA_PAGE = { butuhKelas: true };</script>

<!-- WRAPPER KONTEN UTAMA (Tambahkan overflow-hidden disini) -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
   <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN ABSENSI -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight" data-kelas-awalan="Absensi Kelas">Absensi Kelas</h2>
            <p class="text-[13px] text-gray-500">Catat kehadiran seluruh siswa dalam satu kali simpan.</p>
        </div>

        <!-- Pemilih Tanggal -->
        <div class="px-10 mb-6 shrink-0">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
                <button id="btnPrev" type="button" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-50 text-gray-600 transition" aria-label="Hari sebelumnya">
                    <i class="ph ph-caret-left text-lg"></i>
                </button>
                <label class="flex items-center gap-3 cursor-pointer relative">
                    <i class="ph ph-calendar-blank text-2xl text-[#eba048]"></i>
                    <div>
                        <p id="tanggalLabel" class="text-sm font-bold text-gray-800 leading-tight">&nbsp;</p>
                        <p id="tanggalSub" class="text-[10px] text-gray-500">&nbsp;</p>
                    </div>
                    <input id="inputTanggal" type="date" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" aria-label="Pilih tanggal">
                </label>
                <button id="btnNext" type="button" class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-50 text-gray-600 transition disabled:opacity-30" aria-label="Hari berikutnya">
                    <i class="ph ph-caret-right text-lg"></i>
                </button>
            </div>
            <div id="bannerStatus" hidden class="mt-3 rounded-xl px-4 py-3 text-sm font-medium"></div>
        </div>

        <!-- Tombol Semua Hadir -->
        <div class="px-10 mb-4 shrink-0">
            <button id="btnSemuaHadir" type="button" class="border border-[#455a2d] text-[#455a2d] px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 hover:bg-[#e6e4dc] transition bg-transparent disabled:opacity-40">
                <i class="ph ph-check-circle text-lg"></i> Semua Hadir
            </button>
        </div>

        <!-- Daftar Siswa (diisi page-absensi.js) -->
        <div id="daftarSiswa" class="px-10 pb-6 space-y-3 flex-1" aria-live="polite">
            <div class="skel h-14"></div><div class="skel h-14"></div><div class="skel h-14"></div>
        </div>
    </main>

    <!-- FLOATING BOTTOM BAR (Sekarang DILUAR tag main, jadi tidak akan ikut ke-scroll) -->
    <div class="shrink-0 bg-white border-t border-gray-200 px-10 py-4 flex items-center justify-between z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] relative">
        <div class="flex items-center gap-4 text-[11px] font-bold text-gray-600">
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-600"></span> H <b id="cH">0</b></div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#eba048]"></span> S <b id="cS">0</b></div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-600"></span> I <b id="cI">0</b></div>
            <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-600"></span> A <b id="cA">0</b></div>
            <div class="flex items-center gap-1.5 text-gray-400">Belum diisi <b id="cB">0</b></div>
        </div>
        <div class="text-right">
            <button id="btnSimpan" type="button" class="bg-[#eba048] hover:bg-orange-500 text-white px-6 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 transition mb-1 ml-auto disabled:opacity-40 disabled:cursor-not-allowed">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Absensi
            </button>
            <p class="text-[9px] text-gray-400">Satu permintaan batch untuk seluruh siswa</p>
        </div>
    </div>
    
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>