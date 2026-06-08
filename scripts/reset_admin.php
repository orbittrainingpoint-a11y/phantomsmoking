<?php
$hash = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash: " . $hash . "\n";

// Update in DB
$pdo = new PDO('mysql:host=127.0.0.1;dbname=sultans_smoke_db;charset=utf8mb4', 'root', '');
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@sultanssmokedubai.com'");
$stmt->execute([$hash]);
echo "Rows updated: " . $stmt->rowCount() . "\n";
echo "Done. Login with Admin@123\n";
