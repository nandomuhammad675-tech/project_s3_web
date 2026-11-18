<div class="toast-stack" id="toast-stack" aria-live="polite" aria-atomic="true"></div>

<script src="<?= e(url('assets/js/pwa-install.js')) ?>"></script>
<script src="<?= e(url('assets/js/apiClient.js')) ?>"></script>

<!-- Popup Konfirmasi Keluar -->
<script>
(function () {
    let sudahKonfirmasi = false;

    // 1. Ganti path foto sesuai keinginanmu
    const fotoUrl = <?= json_encode(url('assets/img/logo-sekolah.png')) ?>;
    // 2. Atur lebar & tinggi foto langsung di sini (misal: '80px', '100px', atau 'auto')
    const lebarFoto  = '84px';
    const tinggiFoto = 'auto';

    document.addEventListener('click', function (e) {
        const btnLogout = e.target.closest('[data-action="logout"]');
        if (!btnLogout) return;

        if (sudahKonfirmasi) {
            sudahKonfirmasi = false;
            return;
        }

        e.preventDefault();
        e.stopImmediatePropagation();

        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(24,34,15,.58);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);display:grid;place-items:center;padding:20px;font-family:Poppins,sans-serif;opacity:0;transition:opacity .2s ease';

        overlay.innerHTML = `
            <div id="skCard" style="position:relative;background:#fff;color:#1f2916;width:min(100%,380px);padding:32px 26px 24px;border-radius:24px;text-align:center;border:1px solid rgba(69,90,45,.12);box-shadow:0 24px 60px rgba(15,23,10,.32);transform:scale(.92) translateY(10px);transition:transform .25s cubic-bezier(.34,1.56,.64,1)">
                <button type="button" id="skCloseOut" aria-label="Tutup" style="position:absolute;top:14px;right:14px;width:32px;height:32px;border:0;border-radius:50%;background:transparent;color:#7c8573;cursor:pointer;display:grid;place-items:center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
                <img src="${fotoUrl}" alt="Foto" style="width:${lebarFoto};height:${tinggiFoto};max-width:100%;object-fit:contain;display:block;margin:0 auto 16px">
                <h3 style="margin:0 0 8px;font-size:1.2rem;font-weight:700;color:#1f2916">Keluar dari SI-KESA?</h3>
                <p style="margin:0 0 24px;font-size:.85rem;color:#5f6955;line-height:1.55">Sesi Anda akan diakhiri. Anda perlu masuk kembali untuk mengakses halaman dashboard.</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <button type="button" id="skBatalOut" style="height:46px;border:1.5px solid #e1e5dc;border-radius:14px;background:#f8f9f6;color:#46503c;font-family:inherit;font-size:.9rem;font-weight:600;cursor:pointer">Batal</button>
                    <button type="button" id="skYaOut" style="height:46px;border:0;border-radius:14px;background:linear-gradient(135deg,#eba048 0%,#df8d2d 100%);color:#ffffff;font-family:inherit;font-size:.9rem;font-weight:700;cursor:pointer;box-shadow:0 6px 16px rgba(223,141,45,.32)">Ya, Keluar</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            const card = overlay.querySelector('#skCard');
            if (card) card.style.transform = 'scale(1) translateY(0)';
        });

        const tutup = () => {
            document.removeEventListener('keydown', onEsc);
            overlay.remove();
        };
        const onEsc = (ev) => { if (ev.key === 'Escape') tutup(); };

        document.addEventListener('keydown', onEsc);
        overlay.querySelector('#skCloseOut').onclick = tutup;
        overlay.querySelector('#skBatalOut').onclick = tutup;
        overlay.onclick = (ev) => { if (ev.target === overlay) tutup(); };
        overlay.querySelector('#skYaOut').onclick = () => {
            tutup();
            sudahKonfirmasi = true;
            btnLogout.click();
        };
    }, true);
})();
</script>

<script src="<?= e(url('assets/js/auth.js')) ?>"></script>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
<?php foreach ($pageScripts as $js): ?>
<script src="<?= e(url('assets/js/' . $js)) ?>"></script>
<?php endforeach; ?>
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register(<?= json_encode(url('sw.js')) ?>).catch(function () {});
    });
  }
</script>
</body>
</html> 