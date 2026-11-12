<dialog class="dialog" id="dlg-kelas" aria-labelledby="kl-form-title">
  <form id="form-kelas" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="kl-form-title">Tambah Kelas</h2>
    <p class="dialog__lead" id="kl-form-lead">Isi data kelas, lalu simpan.</p>

    <div class="field">
      <label for="kl-f-tahun">Tahun Ajaran</label>
      <select class="input" id="kl-f-tahun" required aria-describedby="kl-f-tahun-hint kl-f-tahun-err"></select>
      <div class="field__hint" id="kl-f-tahun-hint" hidden>Tahun ajaran tidak diubah setelah kelas dibuat.</div>
      <div class="field__error" id="kl-f-tahun-err" hidden></div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="kl-f-tingkat">Tingkat</label>
        <select class="input" id="kl-f-tingkat" required aria-describedby="kl-f-tingkat-err">
          <option value="">Pilih tingkat</option>
          <option value="1">Tingkat 1</option>
          <option value="2">Tingkat 2</option>
          <option value="3">Tingkat 3</option>
          <option value="4">Tingkat 4</option>
          <option value="5">Tingkat 5</option>
          <option value="6">Tingkat 6</option>
        </select>
        <div class="field__error" id="kl-f-tingkat-err" hidden></div>
      </div>
      <div class="field">
        <label for="kl-f-nama">Nama Kelas</label>
        <input class="input" type="text" id="kl-f-nama" maxlength="10" required autocomplete="off" placeholder="Contoh: 1A" aria-describedby="kl-f-nama-hint kl-f-nama-err">
        <div class="field__hint" id="kl-f-nama-hint">Maksimal 10 karakter, unik dalam satu tahun ajaran.</div>
        <div class="field__error" id="kl-f-nama-err" hidden></div>
      </div>
    </div>

    <div class="field">
      <label for="kl-f-wali">Wali Kelas <span class="opt">(wajib sebelum kenaikan kelas)</span></label>
      <select class="input" id="kl-f-wali" aria-describedby="kl-f-wali-hint kl-f-wali-err"></select>
      <div class="field__hint" id="kl-f-wali-hint">Satu guru hanya boleh menjadi wali satu kelas per tahun ajaran.</div>
      <div class="field__error" id="kl-f-wali-err" hidden></div>
    </div>

    <p class="rombel-rule" id="kl-rule">
      <strong>Aturan rombel:</strong> Tingkat 2, 3, 4, dan 5 tepat satu kelas. Tingkat 1 dan 6 satu atau dua kelas, maksimal dua.
      Server menolak bila melanggar.
    </p>

    <div class="notice notice--warn" id="kl-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="kl-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="kl-save">Simpan</button>
    </div>
  </form>
</dialog>