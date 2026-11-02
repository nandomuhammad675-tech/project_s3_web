<?php
$inp = 'w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 font-medium focus:outline-none focus:border-[#eba048]';
$sel = $inp . ' appearance-none cursor-pointer';
$lbl = 'text-sm font-bold text-gray-800 mb-2 block';
$err = 'text-xs text-red-600 mt-1';
?>
<!-- MODAL FORM JADWAL (dibuka/ditutup oleh page-jadwal.js) -->
<div id="modalJadwal" class="fixed inset-0 z-50 flex items-center justify-center px-4 hidden">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlay" data-tutup></div>
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative z-10 transform scale-95 opacity-0 transition-all duration-300 max-h-[90vh] flex flex-col" id="modalContent" role="dialog" aria-modal="true" aria-labelledby="modalJudul">
        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
            <h2 id="modalJudul" class="text-xl font-bold text-gray-800">Tambah Jadwal</h2>
            <button type="button" data-tutup class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-500 transition" aria-label="Tutup">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <div class="p-8 overflow-y-auto">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3 mb-6">
                <i class="ph ph-info text-blue-500 text-xl mt-0.5"></i>
                <p class="text-sm text-blue-600 font-medium">Tidak ada pilihan Guru pengampu. Jadwal otomatis untuk kelas Anda.</p>
            </div>
            <div id="jdAlert" hidden class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm mb-6"></div>
            <form id="formJadwal" class="space-y-6" novalidate>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="<?= $lbl ?>" for="jdSemester">Semester <span class="text-red-500">*</span></label>
                        <select id="jdSemester" class="<?= $sel ?>"><option value="ganjil">Ganjil</option><option value="genap">Genap</option></select>
                        <p class="<?= $err ?>" data-err="semester" hidden></p>
                    </div>
                    <div>
                        <label class="<?= $lbl ?>" for="jdHari">Hari <span class="text-red-500">*</span></label>
                        <select id="jdHari" class="<?= $sel ?>">
                            <option value="senin">Senin</option><option value="selasa">Selasa</option><option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option><option value="jumat">Jumat</option><option value="sabtu">Sabtu</option>
                        </select>
                        <p class="<?= $err ?>" data-err="hari" hidden></p>
                    </div>
                </div>
                <div>
                    <label class="<?= $lbl ?>" for="jdMapel">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select id="jdMapel" class="<?= $sel ?>"></select>
                    <p class="<?= $err ?>" data-err="id_mapel" hidden></p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="<?= $lbl ?>" for="jdMulai">Jam Mulai <span class="text-red-500">*</span></label>
                        <input id="jdMulai" type="time" value="07:00" class="<?= $inp ?> font-bold">
                        <p class="<?= $err ?>" data-err="jam_mulai" hidden></p>
                    </div>
                    <div>
                        <label class="<?= $lbl ?>" for="jdSelesai">Jam Selesai <span class="text-red-500">*</span></label>
                        <input id="jdSelesai" type="time" value="07:35" class="<?= $inp ?> font-bold">
                        <p class="<?= $err ?>" data-err="jam_selesai" hidden></p>
                    </div>
                </div>
                <div>
                    <label class="<?= $lbl ?>" for="jdKet">Keterangan</label>
                    <input id="jdKet" type="text" maxlength="100" placeholder="Opsional" class="<?= $inp ?>">
                    <p class="<?= $err ?>" data-err="keterangan" hidden></p>
                </div>
                <hr class="border-gray-100">
                <div class="flex items-center justify-end gap-3 pb-2">
                    <button type="button" data-tutup class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-bold text-sm hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" id="jdSimpan" class="px-6 py-2.5 rounded-lg bg-[#eba048] text-white font-bold text-sm hover:bg-orange-500 transition shadow-sm disabled:opacity-50">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
