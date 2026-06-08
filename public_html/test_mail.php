<?php
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v, " \t\"'");
    }
}

$username = $_ENV['MAIL_USERNAME'] ?? '';
$password = $_ENV['MAIL_PASSWORD'] ?? '';
$from     = $username;
$to       = $_GET['to'] ?? $username;

echo "<pre>";
echo "Testing with curl SMTP...\n";
echo "Username: {$username}\n";
echo "Password length: " . strlen($password) . "\n\n";

// Method 1: curl SMTP (most reliable)
if (function_exists('curl_init')) {
    $ch = curl_init();
    $body = "From: Phantom Smoking <{$from}>\r\nTo: {$to}\r\nSubject: Curl SMTP Test\r\nMIME-Version: 1.0\r\nContent-Type: text/html\r\n\r\n<h2>Curl SMTP Test</h2><p>This works!</p>";

    curl_setopt_array($ch, [
        CURLOPT_URL            => "smtps://smtp0101.titan.email:465",
        CURLOPT_MAIL_FROM      => "<{$from}>",
        CURLOPT_MAIL_RCPT      => ["<{$to}>"],
        CURLOPT_READDATA       => fopen('data://text/plain,' . urlencode($body), 'r'),
        CURLOPT_UPLOAD         => true,
        CURLOPT_USERNAME       => $username,
        CURLOPT_PASSWORD       => $password,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_VERBOSE        => true,
        CURLOPT_RETURNTRANSFER => true,
    ]);

    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $result = curl_exec($ch);
    $error  = curl_error($ch);
    $info   = curl_getinfo($ch);
    curl_close($ch);

    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);

    if (empty($error)) {
        echo "✅ CURL SMTP SUCCESS!\n";
    } else {
        echo "❌ Curl error: {$error}\n";
    }
    echo "\nCurl verbose log:\n" . htmlspecialchars($verboseLog) . "\n";
} else {
    echo "❌ curl not available\n";
}

// Method 2: PHP mail() as last resort
echo "\n--- Testing PHP mail() fallback ---\n";
$headers = "From: {$from}\r\nContent-Type: text/html\r\n";
$sent = @mail($to, 'PHP mail() test', '<p>PHP mail test</p>', $headers);
echo $sent ? "✅ PHP mail() sent (check inbox)\n" : "❌ PHP mail() also failed\n";

echo "</pre><strong style='color:red'>DELETE THIS FILE!</strong>";
