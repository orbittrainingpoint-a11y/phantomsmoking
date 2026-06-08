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

    // 1. Flavours table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `flavours` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(110) NOT NULL UNIQUE,
        `category` VARCHAR(50) DEFAULT 'general',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 2. Product flavours pivot
    $pdo->exec("CREATE TABLE IF NOT EXISTS `product_flavours` (
        `product_id` INT UNSIGNED NOT NULL,
        `flavour_id` INT UNSIGNED NOT NULL,
        PRIMARY KEY (`product_id`, `flavour_id`),
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`flavour_id`) REFERENCES `flavours`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 3. Add shisha/hookah columns to products
    $cols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('shisha_weight', $cols))
        $pdo->exec("ALTER TABLE products ADD COLUMN `shisha_weight` VARCHAR(50) NULL AFTER `cigar_origin`");
    if (!in_array('hookah_height', $cols))
        $pdo->exec("ALTER TABLE products ADD COLUMN `hookah_height` VARCHAR(50) NULL AFTER `shisha_weight`");

    // 4. Add selected_flavours to cart_items & order_items
    $ciCols = $pdo->query("SHOW COLUMNS FROM cart_items")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('selected_flavours', $ciCols))
        $pdo->exec("ALTER TABLE cart_items ADD COLUMN `selected_flavours` VARCHAR(500) NULL AFTER `unit_price`");
    $oiCols = $pdo->query("SHOW COLUMNS FROM order_items")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('selected_flavours', $oiCols))
        $pdo->exec("ALTER TABLE order_items ADD COLUMN `selected_flavours` VARCHAR(500) NULL AFTER `variant_name`");

    // 5. Settings for all new features
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?,?)");
    $newSettings = [
        ['store_lat',               '25.0805'],
        ['store_lng',               '55.1403'],
        ['store_map_address',       'Dubai Marina - Marsa Dubai - Dubai Marina - Dubai - UAE'],
        ['google_maps_api_key',     ''],
        ['google_maps_embed_url',   ''],
        ['delivery_km_enabled',     '0'],
        ['delivery_base_fee',       '10'],
        ['delivery_per_km_fee',     '2'],
        ['delivery_free_km',        '0'],
        ['whatsapp_number',         '971562177081'],
        ['contact_email',           'info@phantomsmoking.com'],
        ['contact_whatsapp_label',  'Chat on WhatsApp'],
    ];
    foreach ($newSettings as [$k, $v]) $stmt->execute([$k, $v]);

    // 6. Seed flavours
    $ins = $pdo->prepare("INSERT IGNORE INTO flavours (name,slug,category) VALUES (?,?,?)");
    foreach ([
        ['Mango','mango','vape'],['Watermelon','watermelon','vape'],['Blueberry','blueberry','vape'],
        ['Strawberry','strawberry','vape'],['Mint','mint','vape'],['Ice','ice','vape'],
        ['Lemon','lemon','vape'],['Grape','grape','vape'],['Peach','peach','vape'],
        ['Pineapple','pineapple','vape'],['Mango Ice','mango-ice','vape'],
        ['Blueberry Ice','blueberry-ice','vape'],['Strawberry Ice','strawberry-ice','vape'],
        ['Double Apple','double-apple','shisha'],['Grape Mint','grape-mint','shisha'],
        ['Watermelon Mint','watermelon-mint','shisha'],['Rose','rose','shisha'],
        ['Lemon Mint','lemon-mint','shisha'],['Mixed Fruit','mixed-fruit','shisha'],
        ['Gum','gum','shisha'],['Vanilla','vanilla','shisha'],
        ['Natural','natural','cigar'],['Maduro','maduro','cigar'],
        ['Claro','claro','cigar'],['Colorado','colorado','cigar'],
        ['Cool Mint','cool-mint','nic-pouch'],['Citrus','citrus','nic-pouch'],
    ] as [$n,$s,$c]) $ins->execute([$n,$s,$c]);

    echo "✅ All migrations complete. <strong>Delete this file!</strong>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
