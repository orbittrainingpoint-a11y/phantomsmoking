<?php
$envFile = dirname(__DIR__) . '/.env';
$raw = '';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_contains($line, 'MAIL_PASSWORD=')) {
            $raw = substr($line, strpos($line, '=') + 1);
        }
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
}

echo "<pre>";
echo "Raw MAIL_PASSWORD line value: [" . htmlspecialchars($raw) . "]\n";
echo "Parsed password length: " . strlen($_ENV['MAIL_PASSWORD'] ?? '') . " chars\n";
echo "Parsed password: [" . htmlspecialchars($_ENV['MAIL_PASSWORD'] ?? '') . "]\n";
echo "Username: " . ($_ENV['MAIL_USERNAME'] ?? '') . "\n";
echo "</pre>";
echo "<br><strong style='color:red'>DELETE THIS FILE after testing!</strong>";
