<dialog class="dialog dialog--wide" id="dlg-ekskul" aria-labelledby="ek-form-title">
  <form id="form-ekskul" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="ek-form-title">Tambah Ekstrakurikuler</h2>
    <p class="dialog__lead" id="ek-form-lead">Isi data ekstrakurikuler, lalu simpan.</p>

    <div class="field">
      <label for="ek-nama">Nama</label>
      <input class="input" type="text" id="ek-nama" maxlength="50" required autocomplete="off" placeholder="Contoh: Pramuka" aria-describedby="ek-nama-err">
      <div class="field__error" id="ek-nama-err" hidden></div>
    </div>

    <div class="field">
      <label for="ek-deskripsi">Deskripsi <span class="opt">(opsional)</span></label>
      <textarea class="input input--area" id="ek-deskripsi" rows="3" placeholder="Contoh: Membentuk karakter dan kemandirian" aria-describedby="ek-deskripsi-err"></textarea>
      <div class="field__error" id="ek-deskripsi-err" hidden></div>
    </div>

    <div class="field">
      <label for="ek-ikon">Ikon <span class="opt">(opsional)</span></label>
      <div class="foto-pick">
        <div class="foto-pick__preview foto-pick__preview--square" id="ek-ikon-preview" aria-hidden="true"></div>
        <div class="foto-pick__ctrl">
          <input class="input input--file" type="file" id="ek-ikon" accept="image/jpeg,image/png,image/webp" aria-describedby="ek-ikon-hint ek-ikon-err">
          <button type="button" class="btn btn--soft btn--sm" id="ek-ikon-clear" hidden>Hapus ikon</button>
        </div>
      </div>
      <div class="field__hint" id="ek-ikon-hint">JPG, PNG, atau WebP, maksimal 2 MB. Gambar dikecilkan otomatis sebelum dikirim.</div>
      <div class="field__error" id="ek-ikon-err" hidden></div>
    </div>

    <fieldset class="field pembina" aria-describedby="ek-guru-hint">
      <legend>Guru Pembina <span class="opt">(boleh lebih dari satu)</span></legend>
      <div class="pembina__list" id="ek-guru-list" role="group" aria-label="Pilih guru pembina">
        <span class="skeleton skeleton--row"></span>
      </div>
      <div class="field__hint" id="ek-guru-hint">Centang guru yang membina ekstrakurikuler ini.</div>
    </fieldset>

    <div class="notice notice--warn" id="ek-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="ek-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="ek-save">Simpan</button>
    </div>
  </form>
</dialog>