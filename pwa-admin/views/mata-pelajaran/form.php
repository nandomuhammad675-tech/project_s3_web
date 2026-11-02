<dialog class="dialog" id="dlg-mapel" aria-labelledby="mp-form-title">
  <form id="form-mapel" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="mp-form-title">Tambah Mata Pelajaran</h2>
    <p class="dialog__lead" id="mp-form-lead">Isi data mata pelajaran, lalu simpan.</p>

    <div class="field">
      <label for="mp-kode">Kode <span class="opt">(opsional)</span></label>
      <input class="input" type="text" id="mp-kode" name="kode" maxlength="10" autocomplete="off" placeholder="Contoh: MTK" aria-describedby="mp-kode-hint mp-kode-err">
      <div class="field__hint" id="mp-kode-hint">Maksimal 10 karakter dan tidak boleh sama dengan kode lain.</div>
      <div class="field__error" id="mp-kode-err" hidden></div>
    </div>

    <div class="field">
      <label for="mp-nama">Nama Mata Pelajaran</label>
      <input class="input" type="text" id="mp-nama" name="nama" maxlength="50" required autocomplete="off" placeholder="Contoh: Matematika" aria-describedby="mp-nama-err">
      <div class="field__error" id="mp-nama-err" hidden></div>
    </div>

    <div class="notice notice--warn" id="mp-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="mp-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="mp-save">Simpan</button>
    </div>
  </form>
</dialog>