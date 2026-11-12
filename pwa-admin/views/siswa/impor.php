<dialog class="dialog dialog--wide" id="dlg-impor" aria-labelledby="im-title" style="width:min(760px,calc(100vw - 32px))">
  <div class="dialog__body">
    <h2 class="dialog__title" id="im-title">Impor Data Siswa dari Excel</h2>
    <p class="dialog__lead">Siswa yang diimpor berstatus <b>Baru</b>. Tempatkan ke kelas lewat menu Penempatan Siswa.</p>

    <div class="notice" style="background:#f4f6ea;border:1px solid #dfe5c8;margin-bottom:14px">
      <b>Format berkas (.xlsx):</b> baris pertama adalah judul kolom <code>NISN</code>, <code>Nama Siswa</code>, <code>Nama Wali</code>, <code>No. HP Wali</code>.
      NISN wajib 10 digit dan tidak boleh sama dengan siswa yang sudah ada. Nama Wali dan No. HP Wali boleh kosong.
      <div style="margin-top:8px"><button type="button" class="btn btn--outline" id="im-template">Unduh Contoh Tabel (.xlsx)</button></div>
    </div>

    <div class="field">
      <label for="im-file">Pilih berkas Excel</label>
      <input class="input" type="file" id="im-file" accept=".xlsx,.xls">
    </div>

    <div class="notice notice--warn" id="im-msg" role="alert" hidden></div>
    <div id="im-preview" hidden>
      <p id="im-ringkas" style="margin:8px 0;font-weight:600"></p>
      <div style="max-height:260px;overflow:auto;border:1px solid var(--line);border-radius:8px">
        <table class="table" style="width:100%;font-size:.85rem">
          <thead><tr><th>#</th><th>NISN</th><th>Nama Siswa</th><th>Nama Wali</th><th>No. HP Wali</th><th>Keterangan</th></tr></thead>
          <tbody id="im-body"></tbody>
        </table>
      </div>
    </div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="im-tutup">Tutup</button>
      <button type="button" class="btn btn--primary" id="im-kirim" disabled>Impor</button>
    </div>
  </div>
</dialog>
