<dialog class="dialog dialog--wide" id="dlg-siswa" aria-labelledby="sw-form-title">
  <form id="form-siswa" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="sw-form-title">Tambah Siswa</h2>
    <p class="dialog__lead" id="sw-form-lead">Siswa baru otomatis berstatus Baru. Tempatkan ke kelas lewat menu Penempatan Siswa.</p>

    <div class="field">
      <label for="sw-nama">Nama Siswa</label>
      <input class="input" type="text" id="sw-nama" maxlength="100" required autocomplete="off" placeholder="Contoh: Aditya Pratama" aria-describedby="sw-nama-err">
      <div class="field__error" id="sw-nama-err" hidden></div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="sw-nisn">NISN</label>
        <input class="input" type="text" id="sw-nisn" maxlength="10" required autocomplete="off" inputmode="numeric" placeholder="10 digit angka" aria-describedby="sw-nisn-hint sw-nisn-err">
        <div class="field__hint" id="sw-nisn-hint">Tepat 10 digit angka dan tidak boleh sama dengan siswa lain.</div>
        <div class="field__error" id="sw-nisn-err" hidden></div>
      </div>
      <div class="field" id="sw-status-field" hidden>
        <label for="sw-status-in">Status</label>
        <select class="input" id="sw-status-in" aria-describedby="sw-status-hint"></select>
        <div class="field__hint" id="sw-status-hint">Status Aktif dan Lulus berubah otomatis lewat penempatan dan kenaikan kelas.</div>
      </div>
    </div>

    <p class="form-sep">Data wali</p>
    <div class="field-row">
      <div class="field">
        <label for="sw-wali">Nama Wali <span class="opt">(opsional)</span></label>
        <input class="input" type="text" id="sw-wali" maxlength="100" autocomplete="off" aria-describedby="sw-wali-err">
        <div class="field__error" id="sw-wali-err" hidden></div>
      </div>
      <div class="field">
        <label for="sw-hp">No. HP Wali <span class="opt">(opsional)</span></label>
        <input class="input" type="tel" id="sw-hp" maxlength="15" autocomplete="off" inputmode="tel" aria-describedby="sw-hp-hint sw-hp-err">
        <div class="field__hint" id="sw-hp-hint">Tidak dipakai untuk login dan tidak tampil di publik.</div>
        <div class="field__error" id="sw-hp-err" hidden></div>
      </div>
    </div>

    <div class="notice notice--warn" id="sw-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="sw-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="sw-save">Simpan</button>
    </div>
  </form>
</dialog>