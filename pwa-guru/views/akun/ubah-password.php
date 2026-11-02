<?php 
// Konfigurasi Halaman & Menu
$active_menu = 'akun';
$page_title = 'Ganti Password';

// Panggil Layout Header & Sidebar
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; 
?>

<!-- WRAPPER KONTEN UTAMA -->
<div class="flex-1 flex flex-col h-full relative overflow-hidden bg-[#f7f6f0]">
    
    <!-- Topbar -->
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <!-- AREA SCROLL KONTEN GANTI PASSWORD -->
    <main class="flex-1 overflow-y-auto px-10 py-6 flex flex-col relative">
        
        <!-- Header Halaman -->
        <div class="shrink-0 mb-6">
            <h2 class="text-[26px] font-bold text-[#1f2937] mb-1 leading-tight">Ganti Password</h2>
            <p class="text-[13px] text-gray-500">Perbarui keamanan akun Anda.</p>
        </div>

        <!-- FORM CARD GANTI PASSWORD -->
        <div class="max-w-xl">
            <form action="proses-ganti-password.php" method="POST" class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-5">
                
                <!-- Field: Password Lama -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Password Lama</label>
                    <div class="relative flex items-center">
                        <input type="password" id="password_lama" name="password_lama" placeholder="Masukkan password lama" class="w-full border border-gray-200 rounded-xl py-3 pl-4 pr-11 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#eba048] focus:ring-4 focus:ring-[#eba048]/10 transition-all font-medium">
                        <button type="button" onclick="togglePassword('password_lama', 'icon_lama')" class="absolute right-4 text-gray-400 hover:text-gray-600 transition">
                            <i class="ph ph-eye text-lg" id="icon_lama"></i>
                        </button>
                    </div>
                </div>

                <!-- Field: Password Baru -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Password Baru</label>
                    <div class="relative flex items-center mb-2">
                        <input type="password" id="password_baru" name="password_baru" oninput="checkPasswordStrength(this.value)" placeholder="Minimal 8 karakter" class="w-full border border-gray-200 rounded-xl py-3 pl-4 pr-11 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#eba048] focus:ring-4 focus:ring-[#eba048]/10 transition-all font-medium">
                        <button type="button" onclick="togglePassword('password_baru', 'icon_baru')" class="absolute right-4 text-gray-400 hover:text-gray-600 transition">
                            <i class="ph ph-eye text-lg" id="icon_baru"></i>
                        </button>
                    </div>
                    
                    <!-- Indikator Kekuatan Password (Strength Bar) -->
                    <div class="space-y-1">
                        <div class="grid grid-cols-4 gap-1.5 h-1">
                            <div id="bar-1" class="bg-emerald-500 rounded-full h-full transition-all"></div>
                            <div id="bar-2" class="bg-emerald-500 rounded-full h-full transition-all"></div>
                            <div id="bar-3" class="bg-emerald-500 rounded-full h-full transition-all"></div>
                            <div id="bar-4" class="bg-emerald-500 rounded-full h-full transition-all"></div>
                        </div>
                        <p id="text-strength" class="text-[10px] text-right font-medium text-emerald-600">Kuat</p>
                    </div>
                </div>

                <!-- Field: Ulangi Password Baru -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Ulangi Password Baru</label>
                    <input type="password" id="ulangi_password" name="ulangi_password" placeholder="Ketik ulang password baru" class="w-full border border-gray-200 rounded-xl py-3 px-4 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#eba048] focus:ring-4 focus:ring-[#eba048]/10 transition-all font-medium">
                </div>

                <!-- Tombol Simpan -->
                <button type="submit" class="w-full bg-[#eba048] hover:bg-orange-500 text-white font-bold py-3.5 rounded-xl text-sm transition shadow-sm flex items-center justify-center gap-2">
                    <i class="ph ph-floppy-disk text-lg"></i> Simpan Password
                </button>

                <!-- Informasi Bantuan -->
                <p class="text-center text-[11px] text-gray-400 font-medium">
                    Tidak ada fitur lupa password. Hubungi Admin jika lupa.
                </p>

                <!-- Tombol Kembali -->
                <a href="index.php" class="block w-full bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold py-3 rounded-xl text-sm text-center transition">
                    Kembali ke Akun
                </a>

            </form>
        </div>

    </main>
    
</div>

<!-- SCRIPT TOGGLE SHOW/HIDE PASSWORD & STRENGTH CHECKER -->
<script>
    // Toggle Intip Password
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ph-eye');
            icon.classList.add('ph-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('ph-eye-slash');
            icon.classList.add('ph-eye');
        }
    }

    // Simulasi Indikator Kekuatan Password
    function checkPasswordStrength(val) {
        const bars = [
            document.getElementById('bar-1'),
            document.getElementById('bar-2'),
            document.getElementById('bar-3'),
            document.getElementById('bar-4')
        ];
        const textStrength = document.getElementById('text-strength');

        if (val.length === 0) {
            bars.forEach(b => b.className = 'bg-gray-200 rounded-full h-full transition-all');
            textStrength.innerText = '';
        } else if (val.length < 6) {
            bars[0].className = 'bg-red-500 rounded-full h-full transition-all';
            bars[1].className = 'bg-gray-200 rounded-full h-full transition-all';
            bars[2].className = 'bg-gray-200 rounded-full h-full transition-all';
            bars[3].className = 'bg-gray-200 rounded-full h-full transition-all';
            textStrength.className = 'text-[10px] text-right font-medium text-red-500';
            textStrength.innerText = 'Lemah';
        } else if (val.length < 8) {
            bars[0].className = 'bg-amber-500 rounded-full h-full transition-all';
            bars[1].className = 'bg-amber-500 rounded-full h-full transition-all';
            bars[2].className = 'bg-gray-200 rounded-full h-full transition-all';
            bars[3].className = 'bg-gray-200 rounded-full h-full transition-all';
            textStrength.className = 'text-[10px] text-right font-medium text-amber-500';
            textStrength.innerText = 'Sedang';
        } else {
            bars.forEach(b => b.className = 'bg-emerald-500 rounded-full h-full transition-all');
            textStrength.className = 'text-[10px] text-right font-medium text-emerald-600';
            textStrength.innerText = 'Kuat';
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>