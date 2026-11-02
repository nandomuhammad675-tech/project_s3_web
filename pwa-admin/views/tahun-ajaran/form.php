<dialog class="dialog" id="dlg-ta" aria-labelledby="ta-form-title">
  <form id="form-ta" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="ta-form-title">Tambah Tahun Ajaran</h2>
    <p class="dialog__lead" id="ta-form-lead">Semester otomatis Ganjil dan tahun ajaran belum aktif.</p>

    <div class="field">
      <label for="ta-nama">Nama Tahun</label>
      <input class="input" type="text" id="ta-nama" maxlength="9" autocomplete="off" inputmode="numeric" placeholder="Contoh: 2026/2027" aria-describedby="ta-nama-hint ta-nama-err">
      <div class="field__hint" id="ta-nama-hint">Format tahun/tahun, misalnya 2026/2027. Tidak boleh sama dengan tahun ajaran lain.</div>
      <div class="field__error" id="ta-nama-err" hidden></div>
    </div>

    <div class="notice notice--warn" id="ta-form-msg" role="alert" hidden></div>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="ta-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="ta-save">Simpan</button>
    </div>
  </form>
</dialog>

<dialog class="dialog" id="dlg-ta-sem" aria-labelledby="ta-sem-title">
  <form id="form-ta-sem" class="dialog__body" method="dialog">
    <h2 class="dialog__title" id="ta-sem-title">Ganti Semester</h2>
    <p class="dialog__text">Semester akan diubah dari Ganjil ke Genap. Tindakan ini <strong>TIDAK DAPAT DIKEMBALIKAN</strong>. Absensi yang dicatat setelah ini akan tercatat sebagai semester genap. Lanjutkan?</p>
    <label class="check check--block"><input type="checkbox" id="ta-sem-confirm"><span>Saya mengerti</span></label>
    <div class="notice notice--info" id="ta-sem-msg" role="alert" hidden></div>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="ta-sem-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="ta-sem-ok" disabled>Lanjutkan</button>
    </div>
  </form>
</dialog>

<dialog class="dialog" id="dlg-ta-aktif" aria-labelledby="ta-aktif-title">
  <div class="dialog__body">
    <h2 class="dialog__title" id="ta-aktif-title">Aktifkan Tahun Ajaran</h2>
    <p class="dialog__text" id="ta-aktif-text"></p>
    <div class="notice notice--info" id="ta-aktif-msg" role="alert" hidden></div>
    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="ta-aktif-cancel">Batal</button>
      <button type="button" class="btn btn--primary" id="ta-aktif-ok">Aktifkan</button>
    </div>
  </div>
</dialog>