<dialog class="dialog dialog--wide" id="dlg-program" aria-labelledby="pb-form-title">
  <form id="form-program" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="pb-form-title">Ubah Program</h2>
    <p class="dialog__lead" id="pb-form-lead">Perbarui nama, deskripsi, atau ikon program.</p>

    <div class="field">
      <label>Hari</label>
      <div class="pb-hari" id="pb-hari"></div>
      <div class="field__hint">Hari tidak dapat diubah.</div>
    </div>

    <div class="field">
      <label for="pb-nama">Nama Program</label>
      <input class="input" type="text" id="pb-nama" maxlength="50" required autocomplete="off" placeholder="Contoh: Senin Disiplin" aria-describedby="pb-nama-err">
      <div class="field__error" id="pb-nama-err" hidden></div>
    </div>

    <div class="field">
      <label for="pb-deskripsi">Deskripsi <span class="opt">(opsional)</span></label>
      <textarea class="input input--area" id="pb-deskripsi" rows="3" placeholder="Jelaskan singkat kegiatan pembiasaan hari ini" aria-describedby="pb-deskripsi-err"></textarea>
      <div class="field__error" id="pb-deskripsi-err" hidden></div>
    </div>

    <div class="field">
      <label for="pb-ikon">Ikon <span class="opt">(opsional)</span></label>
      <div class="foto-pick">
        <div class="foto-pick__preview foto-pick__preview--square" id="pb-ikon-preview" aria-hidden="true"></div>
        <div class="foto-pick__ctrl">
          <input class="input input--file" type="file" id="pb-ikon" accept="image/jpeg,image/png,image/webp" aria-describedby="pb-ikon-hint pb-ikon-err">
          <button type="button" class="btn btn--soft btn--sm" id="pb-ikon-clear" hidden>Hapus ikon</button>
        </div>
      </div>
      <div class="field__hint" id="pb-ikon-hint">JPG, PNG, atau WebP, maksimal 2 MB. Gambar dikecilkan otomatis sebelum dikirim.</div>
      <div class="field__error" id="pb-ikon-err" hidden></div>
    </div>

    <div class="notice notice--warn" id="pb-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="pb-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="pb-save">Simpan</button>
    </div>
  </form>
</dialog>