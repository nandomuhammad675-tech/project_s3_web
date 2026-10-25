<!-- MODAL FORM MELAYANG (Tersembunyi secara default) -->
<div id="modalJadwal" class="fixed inset-0 z-50 flex items-center justify-center px-4 hidden">
    <!-- Latar Gelap (Overlay) -->
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlay" onclick="tutupModalJadwal()"></div>
    
    <!-- Kotak Modal Form -->
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative z-10 transform scale-95 opacity-0 transition-all duration-300 max-h-[90vh] flex flex-col" id="modalContent">
        
        <!-- Header Form -->
        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
            <h2 class="text-xl font-bold text-gray-800">Tambah Jadwal</h2>
            <button type="button" onclick="tutupModalJadwal()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-500 transition">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Area Form -->
        <div class="p-8 overflow-y-auto">
            <!-- Info Alert -->
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3 mb-8">
                <i class="ph ph-info text-blue-500 text-xl mt-0.5"></i>
                <p class="text-sm text-blue-600 font-medium">Tidak ada pilihan Guru pengampu. Jadwal otomatis untuk kelas Anda.</p>
            </div>

            <!-- Form Inputs -->
            <form action="index.php" method="POST" class="space-y-6">
                <!-- Semester & Hari -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-bold text-gray-800 mb-2 block">Semester <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 font-medium focus:outline-none focus:border-[#eba048] appearance-none cursor-pointer">
                                <option>Ganjil</option>
                                <option>Genap</option>
                            </select>
                            <i class="ph ph-caret-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-gray-800 mb-2 block">Hari <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 font-medium focus:outline-none focus:border-[#eba048] appearance-none cursor-pointer">
                                <option>Senin</option>
                                <option>Selasa</option>
                                <option>Rabu</option>
                                <option>Kamis</option>
                                <option>Jumat</option>
                                <option>Sabtu</option>
                            </select>
                            <i class="ph ph-caret-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="text-sm font-bold text-gray-800 mb-2 block">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 font-medium focus:outline-none focus:border-[#eba048] appearance-none cursor-pointer">
                            <option>Matematika</option>
                            <option>Bahasa Indonesia</option>
                            <option>IPAS</option>
                        </select>
                        <i class="ph ph-caret-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Jam Mulai & Jam Selesai -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-bold text-gray-800 mb-2 block">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" value="07:30" class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 font-bold focus:outline-none focus:border-[#eba048]">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-gray-800 mb-2 block">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" value="08:40" class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 font-bold focus:outline-none focus:border-[#eba048]">
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="text-sm font-bold text-gray-800 mb-2 block">Keterangan</label>
                    <input type="text" placeholder="Opsional" class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 focus:outline-none focus:border-[#eba048]">
                </div>

                <hr class="border-gray-100 my-6">

                <!-- Aksi Form -->
                <div class="flex items-center justify-end gap-3 pb-2">
                    <button type="button" onclick="tutupModalJadwal()" class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-bold text-sm hover:bg-gray-50 transition">
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

<!-- SCRIPT MODAL (Hanya dipanggil saat file ini di-include) -->
<script>
    function bukaModalJadwal() {
        const modal = document.getElementById('modalJadwal');
        const overlay = document.getElementById('modalOverlay');
        const content = document.getElementById('modalContent');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModalJadwal() {
        const modal = document.getElementById('modalJadwal');
        const overlay = document.getElementById('modalOverlay');
        const content = document.getElementById('modalContent');
        
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>