<?php
$active_menu = 'akun';
$page_title = 'Ganti Password';
$page_scripts = ['page-profil.js'];

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$inp = 'w-full border border-gray-200 rounded-xl p-3 text-sm text-[#1f2937] font-medium focus:outline-none focus:border-[#eba048]';
$lbl = 'text-sm font-bold text-[#1f2937] mb-2 block';
?>
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        <div class="shrink-0 mb-5">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Ganti Password Saya</h2>
            <p class="text-[13px] text-gray-500">Password baru berlaku segera; sesi di perangkat lain akan berakhir.</p>
        </div>
        <div class="max-w-xl w-full bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div id="pwAlert" hidden class="rounded-xl px-4 py-3 text-sm font-medium mb-5"></div>
            <form id="formPassword" class="space-y-5" novalidate autocomplete="off">
                <div>
                    <label class="<?= $lbl ?>" for="pwLama">Password lama</label>
                    <input id="pwLama" type="password" autocomplete="current-password" class="<?= $inp ?>">
                    <p class="text-xs text-red-600 mt-1" data-err="password_lama" hidden></p>
                </div>
                <div>
                    <label class="<?= $lbl ?>" for="pwBaru">Password baru</label>
                    <input id="pwBaru" type="password" autocomplete="new-password" class="<?= $inp ?>">
                    <p class="text-xs text-gray-400 mt-1">Minimal 8 karakter.</p>
                    <p class="text-xs text-red-600 mt-1" data-err="password_baru" hidden></p>
                </div>
                <div>
                    <label class="<?= $lbl ?>" for="pwUlang">Ulangi password baru</label>
                    <input id="pwUlang" type="password" autocomplete="new-password" class="<?= $inp ?>">
                    <p class="text-xs text-red-600 mt-1" data-err="ulangi" hidden></p>
                </div>
                <button type="submit" id="pwSimpan" class="px-6 py-2.5 rounded-lg bg-[#eba048] text-white font-bold text-sm hover:bg-orange-500 transition shadow-sm disabled:opacity-50">Simpan Password</button>
            </form>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
