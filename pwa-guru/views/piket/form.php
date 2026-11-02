<!-- MODAL FORM PIKET (dibuka/ditutup oleh page-piket.js) -->
<div id="modalPiket" class="fixed inset-0 z-50 flex items-center justify-center px-4 hidden">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlayPiket" data-tutup></div>
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative z-10 transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="modalContentPiket" role="dialog" aria-modal="true" aria-labelledby="modalTitlePiket">
        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
            <h2 class="text-xl font-bold text-gray-800" id="modalTitlePiket">Ubah Piket</h2>
            <button type="button" data-tutup class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-500 transition" aria-label="Tutup">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <div class="p-8 overflow-y-auto">
            <div id="pkAlert" hidden class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm mb-5"></div>
            <form id="formPiket" class="space-y-5" novalidate>
                <div>
                    <label class="text-sm font-bold text-gray-800 mb-2 block" for="inputHariPiket">Hari</label>
                    <input type="text" id="inputHariPiket" readonly class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-500 bg-gray-50 cursor-not-allowed font-medium focus:outline-none">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-bold text-gray-800" for="pkCari">Siswa Bertugas</label>
                        <span class="text-xs text-gray-500"><b id="pkJumlah">0</b> dipilih</span>
                    </div>
                    <input type="text" id="pkCari" placeholder="Cari siswa..." class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 focus:outline-none focus:border-[#eba048] mb-2">
                    <div id="pkDaftar" class="border border-gray-200 rounded-xl max-h-56 overflow-y-auto divide-y divide-gray-50"></div>
                    <p class="text-xs text-red-600 mt-1" data-err="id_siswa" hidden></p>
                </div>
                <div>
                    <label class="text-sm font-bold text-gray-800 mb-2 block" for="pkKet">Keterangan</label>
                    <input type="text" id="pkKet" maxlength="100" placeholder="Opsional" class="w-full border border-gray-200 rounded-xl p-3 text-sm text-gray-800 focus:outline-none focus:border-[#eba048] transition-all">
                    <p class="text-xs text-red-600 mt-1" data-err="keterangan" hidden></p>
                </div>
                <hr class="border-gray-100 mt-6 mb-4">
                <div class="flex items-center justify-between gap-3">
                    <button type="button" id="pkHapus" hidden class="px-4 py-2.5 rounded-lg text-red-600 font-bold text-sm hover:bg-red-50 transition">Hapus Piket</button>
                    <div class="flex items-center gap-3 ml-auto">
                        <button type="button" data-tutup class="px-6 py-2.5 rounded-lg border border-[#455a2d] text-[#455a2d] font-bold text-sm hover:bg-[#f7f6f0] transition">Batal</button>
                        <button type="submit" id="pkSimpan" class="px-6 py-2.5 rounded-lg bg-[#eba048] text-white font-bold text-sm hover:bg-orange-500 transition shadow-sm disabled:opacity-50">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
