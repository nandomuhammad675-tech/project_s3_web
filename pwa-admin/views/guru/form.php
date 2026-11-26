<dialog class="dialog dialog--wide" id="dlg-guru" aria-labelledby="gr-form-title">
  <form id="form-guru" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="gr-form-title">Tambah Guru</h2>
    <p class="dialog__lead" id="gr-form-lead">Isi data guru. Akun login Guru dibuat sekaligus.</p>

    <div class="field">
      <label for="gr-nama">Nama Guru</label>
      <input class="input" type="text" id="gr-nama" maxlength="100" required autocomplete="off" placeholder="Contoh: Siti Aminah, S.Pd." aria-describedby="gr-nama-err">
      <div class="field__error" id="gr-nama-err" hidden></div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="gr-nip">NIP <span class="opt">(opsional)</span></label>
        <input class="input" type="text" id="gr-nip" maxlength="20" autocomplete="off" inputmode="numeric" aria-describedby="gr-nip-err">
        <div class="field__error" id="gr-nip-err" hidden></div>
      </div>
      <div class="field">
        <label for="gr-jabatan">Jabatan <span class="opt">(opsional)</span></label>
        <input class="input" type="text" id="gr-jabatan" maxlength="50" autocomplete="off" placeholder="Contoh: Guru Kelas" aria-describedby="gr-jabatan-err">
        <div class="field__error" id="gr-jabatan-err" hidden></div>
      </div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="gr-email">Email <span class="opt">(opsional)</span></label>
        <input class="input" type="email" id="gr-email" maxlength="100" autocomplete="off" aria-describedby="gr-email-hint gr-email-err">
        <div class="field__hint" id="gr-email-hint">Tidak ditampilkan di website publik.</div>
        <div class="field__error" id="gr-email-err" hidden></div>
      </div>
      <div class="field">
        <label for="gr-telepon">No. Telepon <span class="opt">(opsional)</span></label>
        <input class="input" type="tel" id="gr-telepon" maxlength="20" autocomplete="off" inputmode="tel" aria-describedby="gr-telepon-hint gr-telepon-err">
        <div class="field__hint" id="gr-telepon-hint">Tidak ditampilkan di website publik.</div>
        <div class="field__error" id="gr-telepon-err" hidden></div>
      </div>
    </div>

    <div class="field">
      <label for="gr-foto">Foto <span class="opt">(opsional)</span></label>
      <div class="foto-pick">
        <div class="foto-pick__preview" id="gr-foto-preview" aria-hidden="true"></div>
        <div class="foto-pick__ctrl">
          <input class="input input--file" type="file" id="gr-foto" accept="image/jpeg,image/png,image/webp" aria-describedby="gr-foto-hint gr-foto-err">
          <button type="button" class="btn btn--soft btn--sm" id="gr-foto-clear" hidden>Hapus foto</button>
        </div>
      </div>
      <div class="field__hint" id="gr-foto-hint">JPG, PNG, atau WebP. Foto dikecilkan otomatis sebelum dikirim.</div>
      <div class="field__error" id="gr-foto-err" hidden></div>
    </div>

    <div id="gr-akun">
      <p class="form-sep">Akun login Guru</p>
      <div class="field-row">
        <div class="field">
          <label for="gr-username">Username</label>
          <input class="input" type="text" id="gr-username" maxlength="50" autocomplete="off" autocapitalize="none" spellcheck="false" aria-describedby="gr-username-err">
          <div class="field__error" id="gr-username-err" hidden></div>
        </div>
        <div class="field">
          <label for="gr-password">Password</label>
          <div class="input-group">
            <input class="input" type="password" id="gr-password" autocomplete="new-password" aria-describedby="gr-password-hint gr-password-err">
            <button type="button" class="input-group__btn" id="gr-pw-toggle" aria-controls="gr-password" aria-pressed="false">Tampilkan</button>
          </div>
          <div class="field__hint" id="gr-password-hint">Minimal 8 karakter.</div>
          <div class="field__error" id="gr-password-err" hidden></div>
        </div>
      </div>
    </div>
    <p class="field__hint" id="gr-akun-note" hidden>Username dan password tidak diubah di sini. Reset password lewat menu Kelola Akun.</p>

    <div class="notice notice--warn" id="gr-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="gr-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="gr-save">Simpan</button>
    </div>
  </form>
</dialog>