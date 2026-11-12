<dialog class="dialog dialog--wide" id="dlg-galeri" aria-labelledby="gl-title" style="width:min(820px,calc(100vw - 32px))">
  <div class="dialog__body">
    <h2 class="dialog__title" id="gl-title">Foto Galeri</h2>
    <p class="dialog__lead" id="gl-lead">Unggah foto kegiatan. Foto disimpan di database dan di folder website (assets/img/event) dengan nama mengikuti nama event, lalu tampil di website.</p>

    <div class="field">
      <label for="gl-file">Tambah foto (boleh banyak sekaligus)</label>
      <input class="input" type="file" id="gl-file" accept="image/jpeg,image/png,image/webp" multiple>
      <div class="field__hint">JPG, PNG, atau WEBP. Foto diperkecil otomatis dan maksimal 2 MB per foto.</div>
    </div>
    <div class="field">
      <label for="gl-ket">Keterangan foto (opsional)</label>
      <input class="input" type="text" id="gl-ket" maxlength="255" autocomplete="off" placeholder="Contoh: Peserta lomba menyanyi">
    </div>
    <div class="notice notice--warn" id="gl-msg" role="alert" hidden></div>
    <div id="gl-progress" class="field__hint" hidden></div>

    <div class="gl-grid" id="gl-grid" aria-live="polite"></div>
    <p class="field__hint" id="gl-empty" hidden>Belum ada foto.</p>

    <div class="dialog__actions">
      <button type="button" class="btn btn--outline" id="gl-tutup">Tutup</button>
      <button type="button" class="btn btn--primary" id="gl-unggah" disabled>Unggah Foto</button>
    </div>
  </div>
</dialog>
