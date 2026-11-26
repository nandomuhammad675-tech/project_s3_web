<dialog class="dialog" id="dlg-rk" aria-labelledby="rk-form-title">
  <form id="form-rk" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="rk-form-title">Tempatkan Siswa</h2>
    <p class="dialog__lead" id="rk-form-lead">Pilih siswa yang belum punya kelas, lalu tentukan kelas dan nomor absen.</p>

    <div class="field">
      <label for="rk-f-siswa">Siswa</label>
      <div id="rk-cari-wrap" style="margin-bottom:8px">
        <input class="input" type="search" id="rk-f-cari" autocomplete="off" placeholder="Ketik NISN atau nama untuk menyaring..." aria-label="Saring daftar siswa">
      </div>
      <select class="input" id="rk-f-siswa" required aria-describedby="rk-f-siswa-hint rk-f-siswa-err"></select>
      <div class="field__hint" id="rk-f-siswa-hint">Hanya siswa berstatus Baru atau Aktif yang belum punya kelas tahun ini.</div>
      <div class="field__error" id="rk-f-siswa-err" hidden></div>
    </div>

    <div class="field">
      <label for="rk-f-tahun">Tahun Ajaran</label>
      <select class="input" id="rk-f-tahun" required aria-describedby="rk-f-tahun-err"></select>
      <div class="field__error" id="rk-f-tahun-err" hidden></div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="rk-f-kelas">Kelas</label>
        <select class="input" id="rk-f-kelas" required aria-describedby="rk-f-kelas-err"></select>
        <div class="field__error" id="rk-f-kelas-err" hidden></div>
      </div>
      <div class="field">
        <label for="rk-f-absen">Nomor Absen <span class="opt">(opsional)</span></label>
        <input class="input" type="number" id="rk-f-absen" min="1" step="1" inputmode="numeric" placeholder="Contoh: 1" aria-describedby="rk-f-absen-err">
        <div class="field__error" id="rk-f-absen-err" hidden></div>
      </div>
    </div>

    <div class="notice notice--warn" id="rk-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="rk-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="rk-save">Simpan</button>
    </div>
  </form>
</dialog>