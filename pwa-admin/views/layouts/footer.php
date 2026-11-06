<div class="toast-stack" id="toast-stack" aria-live="polite" aria-atomic="true"></div>

<script src="<?= e(url('assets/js/apiClient.js')) ?>"></script>
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