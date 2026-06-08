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

    echo "<pre>";

    // Check tables exist
    $tables = $pdo->query("SHOW TABLES LIKE 'flavours'")->fetchAll();
    echo "flavours table: " . (count($tables) ? "✅ EXISTS" : "❌ MISSING") . "\n";

    $tables2 = $pdo->query("SHOW TABLES LIKE 'product_flavours'")->fetchAll();
    echo "product_flavours table: " . (count($tables2) ? "✅ EXISTS" : "❌ MISSING") . "\n\n";

    if (count($tables)) {
        $flavours = $pdo->query("SELECT * FROM flavours LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        echo "Flavours in DB (" . count($flavours) . "):\n";
        foreach ($flavours as $f) echo "  ID:{$f['id']} Name:{$f['name']} Active:{$f['is_active']}\n";
    }

    if (count($tables2)) {
        $pf = $pdo->query("SELECT pf.*, p.name as product_name FROM product_flavours pf JOIN products p ON p.id=pf.product_id LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nProduct-Flavour assignments (" . count($pf) . "):\n";
        foreach ($pf as $r) echo "  Product:{$r['product_name']} (ID:{$r['product_id']}) → Flavour ID:{$r['flavour_id']}\n";
    }

    // Test the exact API query for product 11
    $pid = $_GET['pid'] ?? 11;
    echo "\nAPI query result for product_id={$pid}:\n";
    $result = $pdo->prepare("SELECT f.id, f.name, f.category FROM flavours f JOIN product_flavours pf ON f.id = pf.flavour_id WHERE pf.product_id = ? AND f.is_active = 1 ORDER BY f.name");
    $result->execute([$pid]);
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    echo count($rows) ? json_encode($rows, JSON_PRETTY_PRINT) : "❌ No flavours found for product $pid";

    echo "</pre>";
    echo "<br><strong style='color:red'>Delete this file!</strong>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
