<?php
$appCfg = require dirname(__DIR__, 2) . '/config/app.php';
$baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? "Phantom Smoking") ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/root.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/components.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/age-gate.css">
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/responsive.css">
</head>
<body>
<?= $content ?>
<script src="<?= $baseUrl ?>/assets/js/age-gate.js"></script>
</body>
</html>
