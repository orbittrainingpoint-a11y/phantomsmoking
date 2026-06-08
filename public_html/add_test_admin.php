<?php
$envFile = dirname(__DIR__) . '/.env';
$env = [];
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\"'");
    }
}

try {
    $pdo = new PDO("mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4", $env['DB_USER'], $env['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = 'test@zemariashotel.com';
    $hash  = password_hash('Test@Phantom@123', PASSWORD_BCRYPT, ['cost' => 12]);

    $existing = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $existing->execute([$email]);

    if ($existing->fetch()) {
        $pdo->prepare("UPDATE users SET password_hash=?, role='admin', is_active=1, email_verified=1 WHERE email=?")->execute([$hash, $email]);
        echo "✅ Existing user updated to admin. ";
    } else {
        $pdo->prepare("INSERT INTO users (first_name,last_name,email,password_hash,role,email_verified,age_verified,is_active) VALUES ('Test','Admin',?,?,'admin',1,1,1)")->execute([$email, $hash]);
        echo "✅ New test admin created. ";
    }
    echo "Login: {$email} / Test@Phantom@123 — <strong>Delete this file now!</strong>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
