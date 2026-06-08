<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);

// Load config (also loads .env)
$appConfig = require APP_PATH . '/config/app.php';

// Timezone
date_default_timezone_set($appConfig['timezone']);

// Error reporting — never expose errors in production
if ($appConfig['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}

// Hide PHP version
header_remove('X-Powered-By');
ini_set('expose_php', '0');

// Block oversized requests early
if (!empty($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 20 * 1024 * 1024) {
    http_response_code(413);
    exit('Request too large');
}

// Autoloader (manual PSR-4 for app namespace)
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $parts = explode('\\', $class);
    array_shift($parts); // remove 'App'
    $dir   = strtolower($parts[0]); // core, controllers, models, gateways etc.
    $file  = APP_PATH . '/' . $dir . '/' . implode('/', array_slice($parts, 1)) . '.php';
    if (file_exists($file)) require_once $file;
});

// Load helpers
require_once APP_PATH . '/helpers/functions.php';
require_once APP_PATH . '/helpers/validators.php';
require_once APP_PATH . '/helpers/formatters.php';
require_once APP_PATH . '/helpers/image_helper.php';
require_once APP_PATH . '/helpers/email_helper.php';

// Start session
\App\Core\Session::start();

// Security headers
$appUrl  = rtrim($_ENV['APP_URL'] ?? 'https://phantomsmoking.ae', '/');
$appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'phantomsmoking.ae';
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');
header("Content-Security-Policy: frame-ancestors 'self' https://{$appHost}");
header_remove('Server');

// Route
$router = \App\Core\Router::load();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uri    = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
