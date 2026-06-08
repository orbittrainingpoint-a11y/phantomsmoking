<?php
// Load .env only once
if (empty($_ENV['APP_NAME'])) {
    $envFile = dirname(__DIR__, 2) . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (str_contains($line, '=')) {
                [$key, $val] = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
            }
        }
    }
}

return [
    'name'     => $_ENV['APP_NAME'] ?? "Phantom Smoking",
    'url'      => $_ENV['APP_URL'] ?? 'http://localhost',
    'env'      => $_ENV['APP_ENV'] ?? 'production',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Dubai',
    'key'      => $_ENV['APP_KEY'] ?? '',
    'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
    'session_secure'   => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'upload_max_size'  => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880),
    'upload_path'      => $_ENV['UPLOAD_PATH'] ?? '/uploads/',
];
