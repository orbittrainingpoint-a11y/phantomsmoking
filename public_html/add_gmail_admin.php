<?php
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
}

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4", $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = 'solsedighi@gmail.com';
    $hash  = password_hash('Test@Phantom@123', PASSWORD_BCRYPT, ['cost' => 12]);

    $existing = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $existing->execute([$email]);

    if ($existing->fetch()) {
        $pdo->prepare("UPDATE users SET password_hash=?, role='admin', is_active=1, email_verified=1, age_verified=1 WHERE email=?")->execute([$hash, $email]);
        echo "✅ Updated existing user to admin.";
    } else {
        $pdo->prepare("INSERT INTO users (first_name,last_name,email,password_hash,role,email_verified,age_verified,is_active) VALUES ('Admin','Phantom',?,?,'admin',1,1,1)")->execute([$email, $hash]);
        echo "✅ New admin created.";
    }
    echo " Login: {$email} / Test@Phantom@123 — <strong>Delete this file now!</strong>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
