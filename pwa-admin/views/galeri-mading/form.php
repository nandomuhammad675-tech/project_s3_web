<dialog class="dialog dialog--wide" id="dlg-mading" aria-labelledby="md-form-title">
  <form id="form-mading" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="md-form-title">Tambah Karya</h2>
    <p class="dialog__lead" id="md-form-lead">Isi data karya siswa, lalu simpan.</p>

    <div class="field">
      <label for="md-judul">Judul</label>
      <input class="input" type="text" id="md-judul" maxlength="100" required autocomplete="off" placeholder="Contoh: Alam di Sekitar Kita" aria-describedby="md-judul-err">
      <div class="field__error" id="md-judul-err" hidden></div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="md-kat">Kategori <span class="opt">(opsional)</span></label>
        <input class="input" type="text" id="md-kat" maxlength="50" list="md-kat-list" autocomplete="off" placeholder="Contoh: Puisi" aria-describedby="md-kat-err">
        <datalist id="md-kat-list"></datalist>
        <div class="field__error" id="md-kat-err" hidden></div>
      </div>
      <div class="field">
        <label for="md-penulis">Nama Penulis <span class="opt">(opsional)</span></label>
        <input class="input" type="text" id="md-penulis" maxlength="100" autocomplete="off" placeholder="Contoh: Nadia · 5A" aria-describedby="md-penulis-err">
        <div class="field__error" id="md-penulis-err" hidden></div>
      </div>
    </div>

    <div class="field">
      <label for="md-deskripsi">Deskripsi <span class="opt">(opsional)</span></label>
      <textarea class="input input--area" id="md-deskripsi" rows="4" placeholder="Ceritakan singkat tentang karya ini" aria-describedby="md-deskripsi-err"></textarea>
      <div class="field__error" id="md-deskripsi-err" hidden></div>
    </div>

    <div class="field">
      <label for="md-gambar">Gambar</label>
      <div class="foto-pick">
        <div class="foto-pick__preview foto-pick__preview--banner" id="md-gambar-preview" aria-hidden="true"></div>
        <div class="foto-pick__ctrl">
          <input class="input input--file" type="file" id="md-gambar" accept="image/jpeg,image/png,image/webp" aria-describedby="md-gambar-hint md-gambar-err">
        </div>
      </div>
      <div class="field__hint" id="md-gambar-hint">Wajib. JPG, PNG, atau WebP, maksimal 2 MB. Gambar dikecilkan otomatis sebelum dikirim.</div>
      <div class="field__error" id="md-gambar-err" hidden></div>
    </div>

    <label class="check" for="md-tampil">
      <input type="checkbox" id="md-tampil" checked>
      <span>Tampilkan di website</span>
    </label>

    <div class="notice notice--warn" id="md-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="md-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="md-save">Simpan</button>
    </div>
  </form>
</dialog>