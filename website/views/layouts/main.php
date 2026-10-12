<?php
/**
 * views/layouts/main.php - SATU-SATUNYA tempat kerangka halaman (head, navbar, footer) ditulis.
 * Dipanggil oleh layout_end() di lib/bootstrap.php. Variabel: $opt (opsi halaman), $content (isi halaman).
 */
$title   = $opt['title'] ?? 'SD Negeri Kedungsari 1';
$current = $opt['current'] ?? '';
$desc    = $opt['description'] ?? 'Website resmi SD Negeri Kedungsari 1 - Cerdas, Berkarakter, Berprestasi';
$css     = $opt['css'] ?? null;
$js      = $opt['js'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <meta name="description" content="<?= esc($desc) ?>">
    <link rel="icon" href="<?= asset('icon/logo_sekolah.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<?php if ($css): ?>
    <link rel="stylesheet" href="<?= asset('css/pages/' . $css . '.css') ?>">
<?php endif; ?>
</head>
<body>
<?php require __DIR__ . '/../partials/site-nav.php'; ?>

<?= $content ?>

<?php require __DIR__ . '/../partials/site-footer.php'; ?>

<script src="<?= asset('js/main.js') ?>" defer></script>
<?php if ($js): ?>
<script src="<?= asset('js/' . $js . '.js') ?>" defer></script>
<?php endif; ?>
</body>
</html>
