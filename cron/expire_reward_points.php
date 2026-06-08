<?php
// expire_reward_points.php — Run monthly on 1st at 1 AM
// Cron: 0 1 1 * * php /path/to/cron/expire_reward_points.php

define('ROOT_PATH', dirname(__DIR__));
$cfg = require ROOT_PATH . '/app/config/database.php';
$pdo = new PDO("mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}", $cfg['user'], $cfg['pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Find users with no purchase in 12 months who have points
$users = $pdo->query("
    SELECT u.id, u.reward_points FROM users u
    WHERE u.reward_points > 0
    AND u.id NOT IN (
        SELECT DISTINCT user_id FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        AND user_id IS NOT NULL
    )
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $pdo->prepare("INSERT INTO reward_points_log (user_id, type, points, balance_after, description) VALUES (?, 'expired', ?, 0, 'Points expired due to 12 months inactivity')")->execute([$user['id'], -$user['reward_points']]);
    $pdo->prepare("UPDATE users SET reward_points = 0 WHERE id = ?")->execute([$user['id']]);
}
echo date('Y-m-d H:i:s') . " — Expired points for " . count($users) . " inactive users\n";
