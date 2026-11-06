<?php
$sk = sk_cfg();
$js = $sk['base'] . '/assets/js/';
// $page_scripts: daftar modul data + skrip halaman, diisi tiap view sebelum memanggil footer
$page_scripts = $page_scripts ?? [];
?>
<div id="toast-stack" class="fixed bottom-24 right-6 z-[100] flex flex-col gap-2 pointer-events-none" aria-live="polite"></div>
<script src="<?= sk_e($js) ?>pwa-install.js"></script>
<script src="<?= sk_e($js) ?>apiClient.js"></script>
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
