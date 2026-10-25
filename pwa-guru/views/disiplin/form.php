<!-- MODAL FORM KEDISIPLINAN (Tersembunyi secara default) -->
<div id="modalDisiplin" class="fixed inset-0 z-50 flex items-center justify-center px-4 hidden">
    <!-- Latar Gelap (Overlay) -->
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlayDisiplin" onclick="tutupModalDisiplin()"></div>
    
    <!-- Kotak Modal Form -->
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative z-10 transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="modalContentDisiplin">
        
        <!-- Header Form -->
        <div class="px-8 py-5 flex items-center justify-between bg-white shrink-0">
            <h2 class="text-xl font-bold text-[#1f2937]" id="modalTitleDisiplin">Tambah Catatan Kedisiplinan</h2>
            <button type="button" onclick="tutupModalDisiplin()" class="w-8 h-8 flex items-center justify-center rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-500 transition">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <div class="w-full h-[1px] bg-gray-100 shrink-0"></div> <!-- Garis Pembatas Header -->

        <!-- Area Form (Bisa di-scroll jika layar sempit) -->
        <div class="p-8 overflow-y-auto">
            
            <!-- Alert Biru -->
            <div class="bg-[#f0f7ff] border border-[#bfdbfe] rounded-xl p-4 flex items-center gap-3 mb-6">
                <i class="ph ph-info text-[#3b82f6] text-xl"></i>
                <p class="text-sm text-[#3b82f6] font-medium">Catatan tidak dapat dihapus, hanya dapat diperbaiki.</p>
            </div>

            <form action="index.php" method="POST" class="space-y-6">
                
                <!-- Input Nama Siswa -->
                <div>
                    <label class="text-sm font-bold text-[#1f2937] mb-2 block">Siswa *</label>
                    <div class="relative">
                        <select class="w-full border border-gray-200 rounded-xl p-3.5 text-sm text-[#1f2937] font-medium focus:outline-none focus:border-[#eba048] appearance-none cursor-pointer" id="inputSiswaDisiplin">
                            <option value="">Pilih siswa</option>
                            <option value="Dimas Saputra">Dimas Saputra</option>
                            <option value="Bagas Maulana">Bagas Maulana</option>
                            <option value="Fahri Ramadhan">Fahri Ramadhan</option>
                        </select>
                        <i class="ph ph-caret-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Input Tanggal -->
                <div>
                    <label class="text-sm font-bold text-[#1f2937] mb-2 block">Tanggal *</label>
                    <input type="text" value="5 Oktober 2026" class="w-full border border-gray-200 rounded-xl p-3.5 text-sm text-[#9ca3af] font-medium focus:outline-none focus:border-[#eba048] focus:text-[#1f2937] transition-colors">
                </div>

                <!-- Input Jenis Pelanggaran -->
                <div>
                    <label class="text-sm font-bold text-[#1f2937] mb-2 block">Jenis Pelanggaran *</label>
                    <div class="relative">
                        <select class="w-full border border-gray-200 rounded-xl p-3.5 text-sm text-[#1f2937] font-medium focus:outline-none focus:border-[#eba048] appearance-none cursor-pointer">
                            <option>Terlambat</option>
                            <option>Tidak mengerjakan PR</option>
                            <option>Atribut tidak lengkap</option>
                            <option>Lainnya</option>
                        </select>
                        <i class="ph ph-caret-down absolute right-4 top-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Input Catatan -->
                <div>
                    <label class="text-sm font-bold text-[#1f2937] mb-2 block">Catatan *</label>
                    <textarea rows="4" placeholder="Tulis catatan secara objektif..." class="w-full border border-gray-200 rounded-xl p-3.5 text-sm text-[#1f2937] placeholder-gray-400 focus:outline-none focus:border-[#eba048] transition-all resize-y min-h-[100px]"></textarea>
                </div>

            </form>
        </div>

        <div class="w-full h-[1px] bg-gray-100 shrink-0"></div> <!-- Garis Pembatas Footer -->

        <!-- Aksi Form -->
        <div class="px-8 py-5 bg-white flex items-center justify-end gap-4 shrink-0">
            <button type="button" onclick="tutupModalDisiplin()" class="px-7 py-2.5 rounded-xl border border-[#455a2d] text-[#455a2d] font-bold text-sm hover:bg-[#f7f6f0] transition">
                Batal
            </button>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-[#eba048] text-white font-bold text-sm hover:bg-orange-500 transition shadow-sm">
                Simpan
            </button>
        </div>

    </div>
</div>

<!-- SCRIPT MODAL KEDISIPLINAN -->
<script>
    function bukaModalDisiplin(aksi = 'Tambah', namaSiswa = '') {
        const modal = document.getElementById('modalDisiplin');
        const overlay = document.getElementById('modalOverlayDisiplin');
        const content = document.getElementById('modalContentDisiplin');
        
        // Ubah Judul 
        document.getElementById('modalTitleDisiplin').innerText = aksi + ' Catatan Kedisiplinan';
        
        // Set Value Input jika mode "Ubah" dari halaman utama
        const inputSiswa = document.getElementById('inputSiswaDisiplin');
        if (namaSiswa) {
            inputSiswa.value = namaSiswa;
        } else {
            inputSiswa.value = ""; // Reset ke placeholder
        }
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModalDisiplin() {
        const modal = document.getElementById('modalDisiplin');
        const overlay = document.getElementById('modalOverlayDisiplin');
        const content = document.getElementById('modalContentDisiplin');
        
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>