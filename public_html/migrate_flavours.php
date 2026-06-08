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

    $pdo->exec("CREATE TABLE IF NOT EXISTS `flavours` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(110) NOT NULL UNIQUE,
        `category` VARCHAR(50) NULL COMMENT 'vape,shisha,cigar,general',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `product_flavours` (
        `product_id` INT UNSIGNED NOT NULL,
        `flavour_id` INT UNSIGNED NOT NULL,
        PRIMARY KEY (`product_id`, `flavour_id`),
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`flavour_id`) REFERENCES `flavours`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Add shisha/hookah fields to products
    $pdo->exec("ALTER TABLE `products`
        ADD COLUMN IF NOT EXISTS `shisha_weight` VARCHAR(50) NULL COMMENT '50g,250g,1kg',
        ADD COLUMN IF NOT EXISTS `hookah_height` VARCHAR(50) NULL COMMENT 'Height in cm',
        ADD COLUMN IF NOT EXISTS `store_lat` DECIMAL(10,8) NULL,
        ADD COLUMN IF NOT EXISTS `store_lng` DECIMAL(11,8) NULL
    ");

    // Add store location to settings
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?,?)");
    $stmt->execute(['store_lat', '25.0805']);
    $stmt->execute(['store_lng', '55.1403']);
    $stmt->execute(['store_map_address', 'Dubai Marina - Marsa Dubai - Dubai Marina - Dubai - UAE']);
    $stmt->execute(['google_maps_api_key', '']);
    $stmt->execute(['delivery_km_enabled', '0']);
    $stmt->execute(['delivery_base_fee', '10']);
    $stmt->execute(['delivery_per_km_fee', '2']);
    $stmt->execute(['delivery_free_km_threshold', '0']);

    // Seed common flavours
    $flavours = [
        ['Mango','mango','vape'],['Watermelon','watermelon','vape'],['Blueberry','blueberry','vape'],
        ['Strawberry','strawberry','vape'],['Mint','mint','vape'],['Ice','ice','vape'],
        ['Lemon','lemon','vape'],['Grape','grape','vape'],['Peach','peach','vape'],
        ['Pineapple','pineapple','vape'],['Mango Ice','mango-ice','vape'],['Blueberry Ice','blueberry-ice','vape'],
        ['Double Apple','double-apple','shisha'],['Grape Mint','grape-mint','shisha'],
        ['Watermelon Mint','watermelon-mint','shisha'],['Rose','rose','shisha'],
        ['Lemon Mint','lemon-mint','shisha'],['Mixed Fruit','mixed-fruit','shisha'],
        ['Natural','natural','cigar'],['Maduro','maduro','cigar'],['Claro','claro','cigar'],
    ];
    $ins = $pdo->prepare("INSERT IGNORE INTO flavours (name,slug,category) VALUES (?,?,?)");
    foreach ($flavours as [$n,$s,$c]) $ins->execute([$n,$s,$c]);

    echo "✅ Flavours table, product_flavours, shisha/hookah fields, store location settings all created. <strong>Delete this file!</strong>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
