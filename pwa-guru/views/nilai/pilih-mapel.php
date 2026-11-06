<?php
/* Komponen filter nilai: semester, mata pelajaran, jenis penilaian, nomor UH.
   Pilihan mata pelajaran diisi dari GET /api/guru/mapel oleh page-nilai.js. */
$sel = 'w-full border border-gray-200 rounded-lg py-2.5 px-3 text-sm text-gray-800 bg-gray-50 hover:bg-white focus:bg-white focus:outline-none focus:border-[#eba048] focus:ring-4 focus:ring-[#eba048]/10 transition-all font-medium';
$lbl = 'text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-2 block';
?>
<div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-end relative">
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#455a2d] to-[#eba048] opacity-80 rounded-t-xl"></div>

    <div class="md:col-span-2">
        <label class="<?= $lbl ?>" for="selSemester">Semester</label>
        <select id="selSemester" class="<?= $sel ?>">
            <option value="ganjil">Ganjil</option>
            <option value="genap">Genap</option>
        </select>
    </div>

    <div class="md:col-span-4">
        <label class="<?= $lbl ?>" for="selMapel">Mata Pelajaran</label>
        <select id="selMapel" class="<?= $sel ?>"><option value="">Memuat...</option></select>
    </div>

    <div class="md:col-span-4" id="grupJenis">
        <span class="<?= $lbl ?>">Jenis Penilaian</span>
        <div class="flex bg-gray-50 border border-gray-100 rounded-lg p-1 h-[44px] items-center" role="group" aria-label="Jenis penilaian">
            <button type="button" data-jenis="UH" class="flex-1 h-full rounded-md text-xs font-bold transition">UH</button>
            <button type="button" data-jenis="ASTS" class="flex-1 h-full rounded-md text-xs font-bold transition">ASTS</button>
            <button type="button" data-jenis="ASAS" class="flex-1 h-full rounded-md text-xs font-bold transition">ASAS</button>
        </div>
    </div>

    <div class="md:col-span-2" id="grupNomorUh">
        <label class="<?= $lbl ?>" for="inpNomorUh">Nomor UH</label>
        <div class="relative group flex items-center">
            <i class="ph ph-hash text-gray-400 absolute left-3 text-lg pointer-events-none"></i>
            <input id="inpNomorUh" type="number" min="1" step="1" value="1" class="w-full border border-gray-200 rounded-lg py-2.5 pl-9 pr-3 text-sm text-gray-800 bg-gray-50 hover:bg-white focus:bg-white focus:outline-none focus:border-[#eba048] focus:ring-4 focus:ring-[#eba048]/10 transition-all font-bold text-center">
        </div>
    </div>
</div>
