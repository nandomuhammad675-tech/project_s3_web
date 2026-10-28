<dialog class="dialog" id="dlg-libur" aria-labelledby="hl-form-title">
  <form id="form-libur" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="hl-form-title">Tambah Hari Libur</h2>
    <p class="dialog__lead">Isi tanggal dan siapa saja yang libur, lalu simpan.</p>

    <div class="field">
      <label for="hl-f-tanggal">Tanggal</label>
      <input class="input" type="date" id="hl-f-tanggal" required aria-describedby="hl-f-tanggal-hint hl-f-tanggal-err">
      <div class="field__hint" id="hl-f-tanggal-hint">Hari Minggu otomatis libur.</div>
      <div class="field__error" id="hl-f-tanggal-err" hidden></div>
    </div>

    <div class="field">
      <label for="hl-f-ket">Keterangan <span class="opt">(opsional)</span></label>
      <input class="input" type="text" id="hl-f-ket" maxlength="100" autocomplete="off" placeholder="Contoh: Libur Semester" aria-describedby="hl-f-ket-err">
      <div class="field__error" id="hl-f-ket-err" hidden></div>
    </div>

    <fieldset class="field fieldset" aria-describedby="hl-f-kelas-err">
      <legend>Berlaku untuk</legend>
      <label class="radio"><input type="radio" name="hl-target" value="semua" checked> Semua kelas</label>
      <label class="radio"><input type="radio" name="hl-target" value="tertentu"> Kelas tertentu</label>
      <div class="kelas-pick" id="hl-kelas-box" hidden>
        <div class="kelas-pick__note" id="hl-kelas-note"></div>
        <div class="kelas-pick__grid" id="hl-kelas-list"></div>
      </div>
      <div class="field__error" id="hl-f-kelas-err" hidden></div>
    </fieldset>

    <div class="notice notice--warn" id="hl-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="hl-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="hl-save">Simpan</button>
    </div>
  </form>
</dialog>