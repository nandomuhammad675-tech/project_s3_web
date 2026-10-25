<!-- MODAL FORM PIKET (Tersembunyi secara default) -->
<div id="modalPiket" class="fixed inset-0 z-50 flex items-center justify-center px-4 hidden">
    <!-- Latar Gelap (Overlay) -->
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlayPiket" onclick="tutupModalPiket()"></div>
    
    <!-- Kotak Modal Form -->
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative z-10 transform scale-95 opacity-0 transition-all duration-300 flex flex-col" id="modalContentPiket">
        
        <!-- Header Form -->
        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
            <h2 class="text-xl font-bold text-gray-800" id="modalTitlePiket">Ubah Piket — Senin</h2>
            <button type="button" onclick="tutupModalPiket()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-500 transition">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Area Form -->
        <div class="p-8">
            <form action="index.php" method="POST" class="space-y-5">
                
                <!-- Input Hari (Readonly) -->
                <div>
                    <label class="text-sm font-bold text-gray-800 mb-2 block">Hari</label>
                    <input type="text" id="inputHariPiket" value="Senin" readonly class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-500 bg-gray-50 cursor-not-allowed font-medium focus:outline-none">
                </div>

                <!-- Input Siswa Bertugas (Multi-select / Tags UI Style) -->
                <div>
                    <label class="text-sm font-bold text-gray-800 mb-2 block">Siswa Bertugas</label>
                    <div class="w-full border border-gray-200 rounded-xl p-2 flex flex-wrap gap-2 items-center focus-within:border-[#eba048] focus-within:ring-1 focus-within:ring-[#eba048] transition-all bg-white min-h-[50px]">
                        
                        <!-- Contoh Tag Siswa (Dummy) -->
                        <span class="bg-[#f0eedc] text-[#455a2d] px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5">
                            Aditya <i class="ph ph-x cursor-pointer hover:text-red-500 transition"></i>
                        </span>
                        <span class="bg-[#f0eedc] text-[#455a2d] px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5">
                            Alya <i class="ph ph-x cursor-pointer hover:text-red-500 transition"></i>
                        </span>
                        
                        <!-- Input untuk mencari siswa -->
                        <input type="text" placeholder="Cari siswa..." class="flex-1 min-w-[120px] bg-transparent text-sm text-gray-800 focus:outline-none px-2 py-1">
                    </div>
                </div>

                <!-- Input Keterangan -->
                <div>
                    <label class="text-sm font-bold text-gray-800 mb-2 block">Keterangan</label>
                    <input type="text" placeholder="Opsional" class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 focus:outline-none focus:border-[#eba048] transition-all">
                </div>

                <hr class="border-gray-100 mt-6 mb-4">

                <!-- Aksi Form -->
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="tutupModalPiket()" class="px-6 py-2.5 rounded-lg border border-[#455a2d] text-[#455a2d] font-bold text-sm hover:bg-[#f7f6f0] transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#eba048] text-white font-bold text-sm hover:bg-orange-500 transition shadow-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT MODAL PIKET -->
<script>
    function bukaModalPiket(hari, aksi = 'Ubah') {
        const modal = document.getElementById('modalPiket');
        const overlay = document.getElementById('modalOverlayPiket');
        const content = document.getElementById('modalContentPiket');
        
        // Ubah Judul & Input sesuai hari yang diklik
        document.getElementById('modalTitlePiket').innerText = aksi + ' Piket — ' + hari;
        document.getElementById('inputHariPiket').value = hari;
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModalPiket() {
        const modal = document.getElementById('modalPiket');
        const overlay = document.getElementById('modalOverlayPiket');
        const content = document.getElementById('modalContentPiket');
        
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>