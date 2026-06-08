<?php
// cleanup_carts.php — Run daily at 2 AM
// Cron: 0 2 * * * php /path/to/cron/cleanup_carts.php

define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/app/config/app.php';
require ROOT_PATH . '/app/helpers/functions.php';

$cfg = require ROOT_PATH . '/app/config/database.php';
$pdo = new PDO("mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}", $cfg['user'], $cfg['pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare('DELETE FROM carts WHERE user_id IS NULL AND expires_at < NOW()');
$stmt->execute();
$deleted = $stmt->rowCount();
echo date('Y-m-d H:i:s') . " — Cleaned up $deleted expired guest carts\n";
