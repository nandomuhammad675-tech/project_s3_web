<?php
$inp = 'w-full border border-gray-200 rounded-xl p-3.5 text-sm text-[#1f2937] font-medium focus:outline-none focus:border-[#eba048]';
$lbl = 'text-sm font-bold text-[#1f2937] mb-2 block';
$err = 'text-xs text-red-600 mt-1';
?>
<!-- MODAL FORM KEDISIPLINAN (dibuka/ditutup oleh page-disiplin.js) -->
<div id="modalDisiplin" class="fixed inset-0 z-50 flex items-center justify-center px-4 hidden">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlayDisiplin" data-tutup></div>
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative z-10 transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh]" id="modalContentDisiplin" role="dialog" aria-modal="true" aria-labelledby="modalTitleDisiplin">
        <div class="px-8 py-5 flex items-center justify-between bg-white shrink-0">
            <h2 class="text-xl font-bold text-[#1f2937]" id="modalTitleDisiplin">Tambah Catatan Kedisiplinan</h2>
            <button type="button" data-tutup class="w-8 h-8 flex items-center justify-center rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-500 transition" aria-label="Tutup"><i class="ph ph-x text-lg"></i></button>
        </div>
        <div class="w-full h-[1px] bg-gray-100 shrink-0"></div>
        <div class="p-8 overflow-y-auto">
            <div class="bg-[#f0f7ff] border border-[#bfdbfe] rounded-xl p-4 flex items-center gap-3 mb-3">
                <i class="ph ph-info text-[#3b82f6] text-xl"></i>
                <p class="text-sm text-[#3b82f6] font-medium">Catatan tidak dapat dihapus, hanya dapat diperbaiki.</p>
            </div>
            <!-- FR-DIS-07: pemberitahuan Wali Murid dapat melihat catatan -->
            <div class="bg-[#fffbeb] border border-[#fde68a] rounded-xl p-4 flex items-center gap-3 mb-6">
                <i class="ph ph-eye text-[#d97706] text-xl"></i>
                <p class="text-sm text-[#92400e] font-medium">Catatan ini dapat dilihat oleh Wali Murid siswa terkait. Jangan menyebut nama siswa lain.</p>
            </div>
            <div id="dsAlert" hidden class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm mb-5"></div>
            <form id="formDisiplin" class="space-y-6" novalidate>
                <div>
                    <label class="<?= $lbl ?>" for="inputSiswaDisiplin">Siswa *</label>
                    <select id="inputSiswaDisiplin" class="<?= $inp ?> appearance-none cursor-pointer"><option value="">Pilih siswa</option></select>
                    <p class="<?= $err ?>" data-err="id_siswa" hidden></p>
                </div>
                <div>
                    <label class="<?= $lbl ?>" for="dsTanggal">Tanggal *</label>
                    <input id="dsTanggal" type="date" class="<?= $inp ?>">
                    <p class="<?= $err ?>" data-err="tanggal" hidden></p>
                </div>
                <div>
                    <label class="<?= $lbl ?>" for="dsJenis">Jenis Pelanggaran *</label>
                    <input id="dsJenis" type="text" maxlength="100" list="dsJenisSaran" placeholder="mis. Terlambat" class="<?= $inp ?>">
                    <datalist id="dsJenisSaran"><option value="Terlambat"><option value="Tidak mengerjakan PR"><option value="Atribut tidak lengkap"><option value="Lainnya"></datalist>
                    <p class="<?= $err ?>" data-err="jenis_pelanggaran" hidden></p>
                </div>
                <div>
                    <label class="<?= $lbl ?>" for="dsCatatan">Catatan *</label>
                    <textarea id="dsCatatan" rows="4" maxlength="2000" placeholder="Tulis catatan secara objektif..." class="<?= $inp ?> resize-none"></textarea>
                    <p class="<?= $err ?>" data-err="catatan" hidden></p>
                </div>
            </form>
        </div>
        <div class="w-full h-[1px] bg-gray-100 shrink-0"></div>
        <div class="px-8 py-5 bg-white flex items-center justify-end gap-4 shrink-0">
            <button type="button" data-tutup class="px-7 py-2.5 rounded-xl border border-[#455a2d] text-[#455a2d] font-bold text-sm hover:bg-[#f7f6f0] transition">Batal</button>
            <button type="submit" form="formDisiplin" id="dsSimpan" class="px-7 py-2.5 rounded-xl bg-[#eba048] text-white font-bold text-sm hover:bg-orange-500 transition shadow-sm disabled:opacity-50">Simpan</button>
        </div>
    </div>
</div>
