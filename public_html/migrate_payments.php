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

    $gateways = [
        ['stripe_enabled','0'],['stripe_public_key',''],['stripe_secret_key',''],['stripe_webhook_secret',''],
        ['telr_enabled','0'],['telr_store_id',''],['telr_auth_key',''],['telr_test_mode','1'],
        ['tabby_enabled','0'],['tabby_public_key',''],['tabby_secret_key',''],['tabby_merchant_code',''],['tabby_test_mode','1'],
        ['tamara_enabled','0'],['tamara_api_token',''],['tamara_notification_key',''],['tamara_test_mode','1'],
        ['cod_enabled','1'],['cod_label','Cash on Delivery'],
        ['payment_currency','AED'],
        ['default_shipping_fee','15'],['default_express_fee','25'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($gateways as [$key, $val]) $stmt->execute([$key, $val]);

    echo "✅ Payment settings inserted. <strong>Delete this file now!</strong>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
