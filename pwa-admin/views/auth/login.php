<?php
/**
 * SI-KESA PWA Admin | Redirect ke halaman login terpadu di PWA Guru
 */
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$projDir = rtrim(str_replace('\\', '/', dirname(__DIR__, 3)), '/'); // naik 3 tingkat dari views/auth -> /project_s3_web
$webBase = ($docRoot !== '' && strpos($projDir, $docRoot) === 0)
    ? substr($projDir, strlen($docRoot))
    : '/project_s3_web';

$target = rtrim($webBase, '/') . '/pwa-guru/index.php?hal=login&role=admin';
header('Location: ' . $target, true, 302);
exit;