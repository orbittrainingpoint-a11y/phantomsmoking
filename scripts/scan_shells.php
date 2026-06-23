<?php
/**
 * Web Shell Scanner — run from CLI only: php scripts/scan_shells.php
 * Scans public_html for planted PHP files in non-application directories.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

$publicDir = dirname(__DIR__) . '/public_html';

// Directories where PHP files should NEVER exist
$noPHPDirs = [
    $publicDir . '/assets',
    $publicDir . '/uploads',
];

// Dev utility files that are already blocked by .htaccess — skip these
$skipFiles = [
    realpath($publicDir . '/test_smtp.php'),
    realpath($publicDir . '/add_phantom_admin.php'),
    realpath($publicDir . '/migrate_features.php'),
    realpath($publicDir . '/index.php'),
];

// Patterns that indicate a web shell (plain strings, matched with strpos for speed)
$shellStrings = [
    'eval(',
    'base64_decode(',
    'system(',
    'passthru(',
    'shell_exec(',
    'popen(',
    'proc_open(',
    'php file manager',
    'FilesMatch',
];

// Regex patterns requiring a proper regex engine
$shellPatterns = [
    '/assert\s*\(/i',
    '/exec\s*\(\s*\$_/i',
    '/file_put_contents\s*\(.*\$_/i',
    '/move_uploaded_file.*\$_/i',
    '/\$_(?:GET|POST|REQUEST|COOKIE)\s*\[.+\]\s*\(/i',
    '/preg_replace\s*\(\s*[\'"]\/.*\/e[\'"],/i',
];

$found = [];

// 1. PHP files in no-PHP directories
foreach ($noPHPDirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (preg_match('/\.(php\d?|phtml|phar)$/i', $file->getFilename())) {
            $found[] = ['type' => 'PHP_IN_ASSET_DIR', 'path' => $file->getPathname(), 'mtime' => date('Y-m-d H:i:s', $file->getMTime())];
        }
    }
}

// 2. Shell patterns in all PHP files under public_html
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($publicDir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if (!preg_match('/\.(php\d?|phtml|phar)$/i', $file->getFilename())) continue;
    $realPath = realpath($file->getPathname());
    if (in_array($realPath, $skipFiles)) continue;

    $content = file_get_contents($file->getPathname());
    if ($content === false) continue;

    $hit = null;
    foreach ($shellStrings as $s) {
        if (stripos($content, $s) !== false) { $hit = 'CONTAINS:' . $s; break; }
    }
    if (!$hit) {
        foreach ($shellPatterns as $p) {
            if (preg_match($p, $content)) { $hit = 'PATTERN:' . $p; break; }
        }
    }
    if ($hit) {
        $found[] = ['type' => $hit, 'path' => $file->getPathname(), 'mtime' => date('Y-m-d H:i:s', $file->getMTime())];
    }
}

if (empty($found)) {
    echo "[OK] No suspicious files found.\n";
} else {
    echo "[ALERT] Found " . count($found) . " suspicious file(s):\n\n";
    foreach ($found as $f) {
        echo "  Type:    {$f['type']}\n";
        echo "  Path:    {$f['path']}\n";
        echo "  Modified:{$f['mtime']}\n\n";
    }
    exit(1);
}
