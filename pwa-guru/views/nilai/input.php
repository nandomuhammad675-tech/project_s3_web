<?php 
// 1. Konfigurasi Halaman & Menu Aktif
$active_menu = 'nilai';
$page_title = 'Nilai';
$page_scripts = ['nilai.js', 'page-nilai.js'];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>
<script>window.SIKESA_PAGE = { butuhKelas: true };</script>

<!-- WRAPPER KONTEN UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
       <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight" data-kelas-awalan="Nilai Kelas">Nilai Kelas</h2>
            <p class="text-[13px] text-gray-500">Input cepat dan rekap nilai siswa.</p>
        </div>

        <!-- Tab Navigasi -->
        <div class="flex gap-8 border-b border-gray-200 mb-6 shrink-0" role="tablist">
            <button type="button" id="tabInput" role="tab" class="pb-3 border-b-2 font-bold text-sm">Input Nilai</button>
            <button type="button" id="tabRekap" role="tab" class="pb-3 border-b-2 font-bold text-sm">Rekap</button>
        </div>

        <!-- Komponen Pilihan Mata Pelajaran -->
        <div class="shrink-0">
            <?php require __DIR__ . '/pilih-mapel.php'; ?>
        </div>

        <div id="infoNilai" hidden class="mb-4 rounded-xl px-4 py-3 text-sm font-medium bg-red-50 text-red-700"></div>

        <!-- PANEL INPUT (diisi page-nilai.js) -->
        <section id="panelInput">
            <div id="daftarNilai" class="space-y-3 shrink-0" aria-live="polite">
                <div class="skel h-14"></div><div class="skel h-14"></div><div class="skel h-14"></div>
            </div>
            <div class="mt-8 mb-10 shrink-0">
                <button id="btnSimpanNilai" type="button" class="bg-[#eba048] hover:bg-orange-500 text-white px-6 py-3 rounded-lg text-sm font-semibold flex items-center gap-2 transition shadow-sm disabled:opacity-40 disabled:cursor-not-allowed">
                    <i class="ph ph-floppy-disk text-xl"></i> Simpan Semua Nilai
                </button>
                <p class="text-[11px] text-gray-400 mt-2">Kolom yang dikosongkan tidak diubah. Gunakan tombol hapus untuk menghapus UH yang salah input; ASTS dan ASAS hanya dapat diperbaiki.</p>
            </div>
        </section>

        <!-- PANEL REKAP -->
        <section id="panelRekap" hidden class="mb-10">
            <div id="rekapNilai" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto"></div>
        </section>

    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>