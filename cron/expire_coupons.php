<?php
// expire_coupons.php — Run daily at midnight
// Cron: 0 0 * * * php /path/to/cron/expire_coupons.php

define('ROOT_PATH', dirname(__DIR__));
$cfg = require ROOT_PATH . '/app/config/database.php';
$pdo = new PDO("mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}", $cfg['user'], $cfg['pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("UPDATE coupons SET is_active = 0 WHERE end_date < NOW() AND is_active = 1");
$stmt->execute();
echo date('Y-m-d H:i:s') . " — Deactivated {$stmt->rowCount()} expired coupons\n";
