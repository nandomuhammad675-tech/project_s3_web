<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'piket';
$page_title = 'Piket';
$page_scripts = ['piket.js', 'page-piket.js'];

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>
<script>window.SIKESA_PAGE = { butuhKelas: true };</script>

<!-- WRAPPER UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN PIKET -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman (Standar Seragam) -->
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight" data-kelas-awalan="Piket Kelas">Piket Kelas</h2>
            <p class="text-[13px] text-gray-500">Piket berulang setiap hari, bukan per tanggal.</p>
        </div>

        <!-- Info Alert Biru -->
        <div class="bg-[#eff6ff] border border-[#dbeafe] rounded-xl px-4 py-2.5 flex items-center gap-2.5 mb-5 shrink-0">
            <i class="ph ph-info text-[#3b82f6] text-lg"></i>
            <p class="text-[13px] text-[#2563eb] font-medium">Atur siswa bertugas dari Senin sampai Sabtu.</p>
        </div>

        <!-- Grid Piket (diisi page-piket.js) -->
        <div id="gridPiket" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 pb-12 items-start" aria-live="polite">
            <div class="skel h-36"></div><div class="skel h-36"></div><div class="skel h-36"></div>
        </div>

    </main>

   <!-- INJECT FILE FORM MODAL DISINI -->
    <?php include __DIR__ . '/form.php'; ?>

    <!-- MODAL KONFIRMASI HAPUS PIKET (IDENTIK DENGAN PWA ADMIN) -->
    <dialog class="modal modal--sm" id="dlg-hapus" aria-labelledby="dlg-hapus-title">
        <div class="modal__head">
            <h2 id="dlg-hapus-title">Hapus Piket</h2>
        </div>
        <div class="modal__body">
            <p class="modal__lead">Yakin ingin menghapus? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="del-target" id="dlg-hapus-target" style="background:#f7f6f0;border:1px solid #e5e4dc;border-radius:12px;padding:12px 16px;margin-top:14px;font-size:14px;color:#374151"></div>
            <div class="notice notice--warn" id="dlg-hapus-msg" hidden style="margin-top:12px"></div>
        </div>
        <div class="modal__foot" style="display:flex;justify-content:flex-end;gap:10px;padding-top:18px">
            <button type="button" class="btn btn--outline" id="dlg-hapus-cancel">Batal</button>
            <button type="button" class="btn btn--danger" id="dlg-hapus-ok">Hapus</button>
        </div>
    </dialog>
    <style>
#dlg-hapus {
    border: 0;
    border-radius: 24px;
    padding: 28px 30px 26px;
    width: min(100% - 32px, 460px);
    background: #ffffff;
    color: #1f2937;
    box-shadow: 0 24px 60px rgba(15, 23, 10, 0.25);
}
#dlg-hapus::backdrop {
    background: rgba(24, 34, 15, 0.52);
    backdrop-filter: blur(2px);
}
#dlg-hapus .modal__head h2 {
    margin: 0 0 8px;
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}
#dlg-hapus .modal__lead {
    margin: 0;
    font-size: 14px;
    color: #4b5563;
}
#dlg-hapus .btn {
    padding: 9px 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}
#dlg-hapus .btn--outline {
    background: #ffffff;
    border: 1.5px solid #556b3c;
    color: #3f522b;
}
#dlg-hapus .btn--danger {
    background: #dc2626;
    border: 0;
    color: #ffffff;
}
#dlg-hapus .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>