<div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-end relative">
    
    <!-- Aksen Garis Atas -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#455a2d] to-[#eba048] opacity-80 rounded-t-xl"></div>

    <!-- Filter Semester (Custom Dropdown) -->
    <div class="md:col-span-2 custom-dropdown-container relative">
        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
            Semester
        </label>
        
        <!-- Tombol Dropdown Semester -->
        <button type="button" onclick="toggleDropdown('menu-semester')" class="w-full border border-gray-200 rounded-lg py-2.5 px-3 text-sm text-gray-800 bg-gray-50 hover:bg-white focus:bg-white focus:outline-none focus:border-[#eba048] focus:ring-4 focus:ring-[#eba048]/10 transition-all font-medium flex items-center gap-2 group">
            <i class="ph ph-calendar-blank text-gray-400 group-hover:text-[#eba048] transition-colors text-lg"></i>
            <span id="text-semester" class="flex-1 text-left">Ganjil</span>
            <i class="ph ph-caret-down text-gray-400 text-xs transition-transform duration-200" id="icon-semester"></i>
        </button>

        <!-- Menu Melayang -->
        <div id="menu-semester" class="custom-dropdown-menu hidden absolute z-50 w-full mt-1.5 bg-white border border-gray-100 rounded-xl shadow-lg overflow-hidden py-1.5 opacity-0 transition-opacity duration-200">
            <ul class="text-sm text-gray-700">
                <li onclick="selectOption('Semester', 'text-semester', 'input-semester', 'menu-semester', 'Ganjil')" class="px-4 py-2 hover:bg-[#fef3e5] hover:text-[#eba048] cursor-pointer font-medium transition">Ganjil</li>
                <li onclick="selectOption('Semester', 'text-semester', 'input-semester', 'menu-semester', 'Genap')" class="px-4 py-2 hover:bg-[#fef3e5] hover:text-[#eba048] cursor-pointer font-medium transition">Genap</li>
            </ul>
        </div>
        <!-- Input Tersembunyi -->
        <input type="hidden" name="semester" id="input-semester" value="Ganjil">
    </div>

    <!-- Filter Mata Pelajaran (Custom Dropdown) -->
    <div class="md:col-span-4 custom-dropdown-container relative">
        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
            Mata Pelajaran
        </label>
        
        <!-- Tombol Dropdown Mapel -->
        <button type="button" onclick="toggleDropdown('menu-mapel')" class="w-full border border-gray-200 rounded-lg py-2.5 px-3 text-sm text-gray-800 bg-gray-50 hover:bg-white focus:bg-white focus:outline-none focus:border-[#eba048] focus:ring-4 focus:ring-[#eba048]/10 transition-all font-medium flex items-center gap-2 group">
            <i class="ph ph-books text-gray-400 group-hover:text-[#eba048] transition-colors text-lg"></i>
            <span id="text-mapel" class="flex-1 text-left truncate">Matematika</span>
            <i class="ph ph-caret-down text-gray-400 text-xs transition-transform duration-200" id="icon-mapel"></i>
        </button>

        <!-- Menu Melayang -->
        <div id="menu-mapel" class="custom-dropdown-menu hidden absolute z-50 w-full mt-1.5 bg-white border border-gray-100 rounded-xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] overflow-hidden py-1.5 opacity-0 transition-opacity duration-200">
            <ul class="max-h-56 overflow-y-auto scrollbar-hide text-sm text-gray-700">
                <li onclick="selectOption('Mapel', 'text-mapel', 'input-mapel', 'menu-mapel', 'Matematika')" class="px-4 py-2 hover:bg-[#fef3e5] hover:text-[#eba048] cursor-pointer font-medium transition">Matematika</li>
                <li onclick="selectOption('Mapel', 'text-mapel', 'input-mapel', 'menu-mapel', 'Bahasa Indonesia')" class="px-4 py-2 hover:bg-[#fef3e5] hover:text-[#eba048] cursor-pointer font-medium transition">Bahasa Indonesia</li>
                <li onclick="selectOption('Mapel', 'text-mapel', 'input-mapel', 'menu-mapel', 'Ilmu Pengetahuan Alam')" class="px-4 py-2 hover:bg-[#fef3e5] hover:text-[#eba048] cursor-pointer font-medium transition">Ilmu Pengetahuan Alam</li>
                <li onclick="selectOption('Mapel', 'text-mapel', 'input-mapel', 'menu-mapel', 'Pendidikan Pancasila')" class="px-4 py-2 hover:bg-[#fef3e5] hover:text-[#eba048] cursor-pointer font-medium transition">Pendidikan Pancasila</li>
                <li onclick="selectOption('Mapel', 'text-mapel', 'input-mapel', 'menu-mapel', 'Bahasa Inggris')" class="px-4 py-2 hover:bg-[#fef3e5] hover:text-[#eba048] cursor-pointer font-medium transition">Bahasa Inggris</li>
                <li onclick="selectOption('Mapel', 'text-mapel', 'input-mapel', 'menu-mapel', 'Seni Budaya')" class="px-4 py-2 hover:bg-[#fef3e5] hover:text-[#eba048] cursor-pointer font-medium transition">Seni Budaya</li>
            </ul>
        </div>
        <!-- Input Tersembunyi -->
        <input type="hidden" name="mata_pelajaran" id="input-mapel" value="Matematika">
    </div>

    <!-- Toggle Jenis Penilaian -->
    <div class="md:col-span-4">
        <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-2 block">
            Jenis Penilaian
        </label>
        <div class="flex bg-gray-50 border border-gray-100 rounded-lg p-1 h-[44px] items-center">
            <!-- Button UH -->
            <button type="button" id="btn-uh" onclick="selectJenisPenilaian('UH')" class="flex-1 bg-[#eba048] text-white h-full rounded-md text-xs font-bold transition shadow-md flex items-center justify-center gap-1.5 ring-1 ring-black/5">
                <i class="ph ph-check-circle text-sm" id="icon-uh"></i> UH
            </button>
            <!-- Button ASTS -->
            <button type="button" id="btn-asts" onclick="selectJenisPenilaian('ASTS')" class="flex-1 text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 h-full rounded-md text-xs font-bold transition flex items-center justify-center gap-1.5">
                <i class="ph ph-check-circle text-sm hidden" id="icon-asts"></i> ASTS
            </button>
            <!-- Button ASAS -->
            <button type="button" id="btn-asas" onclick="selectJenisPenilaian('ASAS')" class="flex-1 text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 h-full rounded-md text-xs font-bold transition flex items-center justify-center gap-1.5">
                <i class="ph ph-check-circle text-sm hidden" id="icon-asas"></i> ASAS
            </button>
        </div>
        <!-- Input Tersembunyi untuk Form Submission -->
        <input type="hidden" name="jenis_penilaian" id="input-jenis-penilaian" value="UH">
    </div>

    <!-- Input Nomor Penilaian (Dinamis: Nomor UH / Nomor ASTS / Nomor ASAS) -->
    <div class="md:col-span-2" id="container-nomor-penilaian">
        <label id="label-nomor-penilaian" class="text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-2 block">
            Nomor UH
        </label>
        <div class="relative group flex items-center">
            <i class="ph ph-hash text-gray-400 group-focus-within:text-[#eba048] transition-colors absolute left-3 text-lg pointer-events-none"></i>
            <input type="number" name="nomor_penilaian" id="input-nomor-penilaian" value="3" min="1" class="w-full border border-gray-200 rounded-lg py-2.5 pl-9 pr-3 text-sm text-gray-800 bg-gray-50 hover:bg-white focus:bg-white focus:outline-none focus:border-[#eba048] focus:ring-4 focus:ring-[#eba048]/10 transition-all font-bold text-center">
        </div>
    </div>

</div>

<!-- SCRIPT UNTUK DROPDOWN DAN TOGGLE JENIS PENILAIAN -->
<script>
    // Fungsi Switch Jenis Penilaian (UH, ASTS, ASAS)
    function selectJenisPenilaian(jenis) {
        const types = ['UH', 'ASTS', 'ASAS'];
        
        // Simpan nilai ke hidden input
        document.getElementById('input-jenis-penilaian').value = jenis;

        types.forEach(type => {
            const btn = document.getElementById(`btn-${type.toLowerCase()}`);
            const icon = document.getElementById(`icon-${type.toLowerCase()}`);

            if (type === jenis) {
                // Style Tombol Aktif
                btn.className = "flex-1 bg-[#eba048] text-white h-full rounded-md text-xs font-bold transition shadow-md flex items-center justify-center gap-1.5 ring-1 ring-black/5";
                if (icon) icon.classList.remove('hidden');
            } else {
                // Style Tombol Inaktif
                btn.className = "flex-1 text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 h-full rounded-md text-xs font-bold transition flex items-center justify-center gap-1.5";
                if (icon) icon.classList.add('hidden');
            }
        });

        // Ubah Label Widget secara Dinamis berdasarkan pilihan (NOMOR UH / NOMOR ASTS / NOMOR ASAS)
        const labelPenilaian = document.getElementById('label-nomor-penilaian');
        labelPenilaian.innerText = `Nomor ${jenis}`;
    }

    // Fungsi untuk membuka/menutup dropdown
    function toggleDropdown(menuId) {
        document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
            if(menu.id !== menuId) {
                menu.classList.add('hidden');
                menu.classList.remove('opacity-100');
            }
        });

        const menu = document.getElementById(menuId);
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            setTimeout(() => menu.classList.add('opacity-100'), 10);
        } else {
            menu.classList.remove('opacity-100');
            setTimeout(() => menu.classList.add('hidden'), 200);
        }
    }

    // Fungsi saat salah satu opsi dipilih
    function selectOption(type, textId, inputId, menuId, value) {
        document.getElementById(textId).innerText = value;
        document.getElementById(inputId).value = value;
        
        const menu = document.getElementById(menuId);
        menu.classList.remove('opacity-100');
        setTimeout(() => menu.classList.add('hidden'), 200);
    }

    // Event Listener untuk menutup dropdown jika user klik di luar area
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-dropdown-container')) {
            document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
                menu.classList.remove('opacity-100');
                setTimeout(() => menu.classList.add('hidden'), 200);
            });
        }
    });
</script>