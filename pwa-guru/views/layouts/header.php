<?php
require_once __DIR__ . '/../../config.php';
$sk = sk_cfg();
$page_title = $page_title ?? 'Beranda';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#455a2d">
    <title><?= sk_e($page_title) ?> - SI-KESA</title>
    <link rel="apple-touch-icon" href="<?= sk_e($sk['base']) ?>/assets/icons/icon-192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SI-KESA Guru">
    <link rel="manifest" href="<?= sk_e($sk['base']) ?>/manifest.json">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="<?= sk_e($sk['base']) ?>/assets/css/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script>
        /* Konfigurasi klien (dipakai assets/js/apiClient.js dan app.js) */
        window.SIKESA_CONFIG = <?= json_encode([
            'apiBase'  => $sk['api_base'],
            'tokenKey' => $sk['token_key'],
            'userKey'  => $sk['user_key'],
            'loginUrl' => sk_url('login'),
            'homeUrl'  => sk_url('dashboard'),
            'base'     => $sk['base'],
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
</head>
<body class="bg-[#f7f6f0] flex h-screen overflow-hidden text-gray-800"></body>
