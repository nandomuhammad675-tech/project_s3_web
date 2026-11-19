<?php
$sk = sk_cfg();
$js = $sk['base'] . '/assets/js/';
// $page_scripts: daftar modul data + skrip halaman, diisi tiap view sebelum memanggil footer
$page_scripts = $page_scripts ?? [];
?>
<div id="toast-stack" class="fixed bottom-24 right-6 z-[100] flex flex-col gap-2 pointer-events-none" aria-live="polite"></div>
<script src="<?= sk_e($js) ?>pwa-install.js"></script>
<script src="<?= sk_e($js) ?>apiClient.js"></script>

<!-- Popup Konfirmasi Keluar PWA Guru -->
<script>
(function () {
    let sudahKonfirmasi = false;

    // 1. Ganti path foto sesuai keinginanmu
    const fotoUrl = <?= json_encode($sk['base'] . '/assets/img/logo-sekolah.png', JSON_UNESCAPED_SLASHES) ?>;
    // 2. Atur lebar & tinggi foto langsung di sini
    const lebarFoto  = '84px';
    const tinggiFoto = 'auto';

    document.addEventListener('click', function (e) {
        const btnLogout = e.target.closest('[data-action="logout"], [data-logout], #btnLogout');
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

<script src="<?= sk_e($js) ?>auth.js"></script>
<script src="<?= sk_e($js) ?>profil.js"></script>
<script src="<?= sk_e($js) ?>referensi.js"></script>
<script src="<?= sk_e($js) ?>app.js"></script>
<?php foreach ($page_scripts as $s): ?>
<script src="<?= sk_e($js . $s) ?>"></script>
<?php endforeach; ?>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('<?= sk_e($sk['base']) ?>/sw.js').catch(function () {});
    }
</script>
</body>
</html>