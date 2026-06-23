<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public_html');

// Load helpers without DB dependency
require_once APP_PATH . '/helpers/validators.php';
require_once APP_PATH . '/helpers/formatters.php';

// Load helper functions (skip DB-dependent ones)
if (!function_exists('slugify')) {
    require_once APP_PATH . '/helpers/functions.php';
}

// Load vendor autoload
require_once ROOT_PATH . '/vendor/autoload.php';

// Stub environment
$_ENV['APP_URL']   = 'http://localhost';
$_ENV['APP_NAME']  = 'Test';
$_ENV['APP_DEBUG'] = 'true';
