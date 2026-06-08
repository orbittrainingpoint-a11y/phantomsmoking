<?php
// TEMPORARY SMTP DEBUG — DELETE THIS FILE AFTER TESTING
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');

// Load .env
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $_ENV[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
        }
    }
}

$host       = $_ENV['MAIL_HOST']         ?? '';
$port       = (int)($_ENV['MAIL_PORT']   ?? 465);
$username   = $_ENV['MAIL_USERNAME']     ?? '';
$password   = $_ENV['MAIL_PASSWORD']     ?? '';
$fromEmail  = $_ENV['MAIL_FROM_ADDRESS'] ?? '';
$encryption = $_ENV['MAIL_ENCRYPTION']   ?? 'ssl';
$sendTo     = $_GET['to'] ?? $username;

echo "<pre style='font-family:monospace;font-size:13px;padding:20px'>";
echo "=== SMTP DEBUG ===\n";
echo "Host:       $host\n";
echo "Port:       $port\n";
echo "Encryption: $encryption\n";
echo "Username:   $username\n";
echo "Password:   " . (empty($password) ? '(EMPTY!)' : str_repeat('*', strlen($password)) . ' (length: ' . strlen($password) . ')') . "\n";
echo "Pass bytes: " . implode(' ', array_map('ord', str_split($password))) . "\n";
echo "From:       $fromEmail\n";
echo "Sending to: $sendTo\n";
echo "Server IP:  " . ($_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname())) . "\n\n";

// Step 1: TCP connect
echo "--- Step 1: TCP Connect ---\n";
$socketHost = ($encryption === 'ssl' || $port === 465) ? "ssl://$host" : "tcp://$host";
$ctx = stream_context_create(['ssl' => [
    'verify_peer'       => false,
    'verify_peer_name'  => false,
    'allow_self_signed' => true,
]]);
$errno = 0; $errstr = '';
$socket = @stream_socket_client("$socketHost:$port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
if (!$socket) {
    echo "FAILED: $errstr (errno $errno)\n</pre>"; exit;
}
echo "OK\n\n";
stream_set_timeout($socket, 15);

function smtp_read($s): string {
    $d = '';
    while ($line = fgets($s, 515)) { $d .= $line; if ($line[3] === ' ') break; }
    return trim($d);
}
function smtp_cmd($s, $cmd): string {
    fwrite($s, $cmd . "\r\n");
    return smtp_read($s);
}

$banner = smtp_read($socket);
smtp_cmd($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'phantomsmoking.ae'));

// Try all 3 auth methods and show raw responses
echo "--- AUTH METHOD 1: AUTH PLAIN (inline) ---\n";
$creds = base64_encode("\0{$username}\0{$password}");
echo "Credentials b64: $creds\n";
$r = smtp_cmd($socket, "AUTH PLAIN $creds");
echo "Response: $r\n\n";

if (!str_starts_with($r, '235')) {
    // Reconnect for next attempt
    fclose($socket);
    $socket = @stream_socket_client("$socketHost:$port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    stream_set_timeout($socket, 15);
    smtp_read($socket);
    smtp_cmd($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'phantomsmoking.ae'));

    echo "--- AUTH METHOD 2: AUTH LOGIN ---\n";
    $r1 = smtp_cmd($socket, "AUTH LOGIN");
    echo "Challenge 1: $r1\n";
    $r2 = smtp_cmd($socket, base64_encode($username));
    echo "Challenge 2: $r2\n";
    // Decode challenge to see what server is asking
    if (str_starts_with($r2, '334')) {
        $challenge = base64_decode(trim(substr($r2, 4)));
        echo "Server asks: $challenge\n";
    }
    $r3 = smtp_cmd($socket, base64_encode($password));
    echo "Response: $r3\n\n";

    if (!str_starts_with($r3, '235')) {
        fclose($socket);
        $socket = @stream_socket_client("$socketHost:$port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        stream_set_timeout($socket, 15);
        smtp_read($socket);
        smtp_cmd($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'phantomsmoking.ae'));

        echo "--- AUTH METHOD 3: AUTH PLAIN (two-step) ---\n";
        $r1 = smtp_cmd($socket, "AUTH PLAIN");
        echo "Challenge: $r1\n";
        $r2 = smtp_cmd($socket, $creds);
        echo "Response: $r2\n\n";

        if (!str_starts_with($r2, '235')) {
            echo "\n❌ ALL AUTH METHODS FAILED\n";
            echo "This means Titan is blocking authentication from this server IP.\n";
            echo "Fix: Log into Titan webmail → Settings → Security → Allow SMTP from all IPs\n";
            echo "OR: Use Hostinger's own SMTP instead (see below)\n\n";
            echo "--- Hostinger SMTP Alternative ---\n";
            echo "MAIL_HOST=smtp.hostinger.com\n";
            echo "MAIL_PORT=465\n";
            echo "MAIL_ENCRYPTION=ssl\n";
            echo "MAIL_USERNAME=info@phantomsmoking.ae\n";
            echo "MAIL_PASSWORD=(your Hostinger email password)\n";
            fclose($socket);
            echo "</pre>"; exit;
        }
        $authOk = true;
    } else {
        $authOk = true;
    }
} else {
    $authOk = true;
}

echo "AUTH OK\n\n";

// Send test email
echo "--- Sending Test Email ---\n";
$r = smtp_cmd($socket, "MAIL FROM:<$fromEmail>"); echo "MAIL FROM: $r\n";
$r = smtp_cmd($socket, "RCPT TO:<$sendTo>");      echo "RCPT TO:   $r\n";
$r = smtp_cmd($socket, "DATA");                   echo "DATA:      $r\n";
$body = base64_encode("<h2>SMTP Test OK</h2><p>OTP emails will work.</p>");
$msg  = "From: Phantom Smoking <$fromEmail>\r\n"
      . "To: $sendTo\r\n"
      . "Subject: SMTP Test OK\r\n"
      . "MIME-Version: 1.0\r\n"
      . "Content-Type: text/html; charset=UTF-8\r\n"
      . "Content-Transfer-Encoding: base64\r\n"
      . "\r\n" . chunk_split($body) . "\r\n.";
$r = smtp_cmd($socket, $msg); echo "SEND: $r\n";
smtp_cmd($socket, "QUIT");
fclose($socket);
echo str_starts_with($r, '250') ? "\n✅ SUCCESS — check inbox of $sendTo\n" : "\n❌ SEND FAILED: $r\n";
echo "</pre>";
