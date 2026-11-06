<dialog class="dialog dialog--wide" id="dlg-event" aria-labelledby="ev-form-title">
  <form id="form-event" class="dialog__body" novalidate>
    <h2 class="dialog__title" id="ev-form-title">Tambah Event</h2>
    <p class="dialog__lead" id="ev-form-lead">Isi data event, lalu simpan.</p>

    <div class="field">
      <label for="ev-nama">Nama Event</label>
      <input class="input" type="text" id="ev-nama" maxlength="100" required autocomplete="off" placeholder="Contoh: Upacara Hari Pendidikan" aria-describedby="ev-nama-err">
      <div class="field__error" id="ev-nama-err" hidden></div>
    </div>

    <div class="field-row">
      <div class="field">
        <label for="ev-kat">Kategori</label>
        <select class="input" id="ev-kat" required aria-describedby="ev-kat-err">
          <option value="">Pilih kategori</option>
          <option value="kegiatan">Kegiatan</option>
          <option value="event">Event</option>
          <option value="prestasi">Prestasi</option>
          <option value="kolaborasi">Kolaborasi</option>
        </select>
        <div class="field__error" id="ev-kat-err" hidden></div>
      </div>
      <div class="field">
        <label for="ev-tanggal">Tanggal <span class="opt">(opsional)</span></label>
        <input class="input" type="date" id="ev-tanggal" aria-describedby="ev-tanggal-err">
        <div class="field__error" id="ev-tanggal-err" hidden></div>
      </div>
    </div>

    <div class="field">
      <label for="ev-deskripsi">Deskripsi <span class="opt">(opsional)</span></label>
      <textarea class="input input--area" id="ev-deskripsi" rows="4" placeholder="Ceritakan singkat tentang event ini" aria-describedby="ev-deskripsi-err"></textarea>
      <div class="field__error" id="ev-deskripsi-err" hidden></div>
    </div>

    <div class="field">
      <label for="ev-banner">Banner <span class="opt">(opsional)</span></label>
      <div class="foto-pick">
        <div class="foto-pick__preview foto-pick__preview--banner" id="ev-banner-preview" aria-hidden="true"></div>
        <div class="foto-pick__ctrl">
          <input class="input input--file" type="file" id="ev-banner" accept="image/jpeg,image/png,image/webp" aria-describedby="ev-banner-hint ev-banner-err">
          <button type="button" class="btn btn--soft btn--sm" id="ev-banner-clear" hidden>Hapus banner</button>
        </div>
      </div>
      <div class="field__hint" id="ev-banner-hint">JPG, PNG, atau WebP, maksimal 2 MB. Gambar dikecilkan otomatis sebelum dikirim.</div>
      <div class="field__error" id="ev-banner-err" hidden></div>
    </div>

    <label class="check" for="ev-tampil">
      <input type="checkbox" id="ev-tampil" checked>
      <span>Tampilkan di website</span>
    </label>

    <div class="notice notice--warn" id="ev-form-msg" role="alert" hidden></div>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="ev-cancel">Batal</button>
      <button type="submit" class="btn btn--primary" id="ev-save">Simpan</button>
    </div>
  </form>
</dialog>