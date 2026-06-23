<?php
namespace App\Core;

class Mailer
{
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;
    private string $encryption; // tls | ssl | none

    public function __construct()
    {
        $this->host       = $_ENV['MAIL_HOST']         ?? 'smtp.gmail.com';
        $this->port       = (int)($_ENV['MAIL_PORT']   ?? 587);
        $this->username   = $_ENV['MAIL_USERNAME']     ?? '';
        $this->password   = $_ENV['MAIL_PASSWORD']     ?? '';
        $this->fromEmail  = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@sultanssmokedubai.com';
        $this->fromName   = $_ENV['MAIL_FROM_NAME']    ?? "Sultan's Smoke";
        $this->encryption = $_ENV['MAIL_ENCRYPTION']   ?? 'tls';
    }

    public function send(string $to, string $subject, string $htmlBody, string $toName = ''): bool
    {
        // Decode HTML entities in fromName (e.g. &quot; from .env)
        $this->fromName = html_entity_decode($this->fromName, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Use SMTP when credentials are present, regardless of APP_ENV
        if (empty($this->username) || empty($this->password)) {
            return $this->fallbackMail($to, $subject, $htmlBody, $toName);
        }

        try {
            $socket = $this->connect();
            if (!$socket) return $this->fallbackMail($to, $subject, $htmlBody, $toName);

            $this->expect($socket, 220);
            $this->send_cmd($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $this->read($socket);

            // STARTTLS upgrade — only for port 587/tls, not port 465/ssl
            if ($this->encryption === 'tls' && $this->port !== 465) {
                $this->send_cmd($socket, "STARTTLS");
                $this->expect($socket, 220);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->send_cmd($socket, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
                $this->read($socket);
            }

            // AUTH PLAIN — single step, required by Titan/Postfix on port 465
            $credentials = base64_encode("\0{$this->username}\0{$this->password}");
            $this->send_cmd($socket, "AUTH PLAIN {$credentials}");
            $authResponse = $this->read($socket);
            if (!str_starts_with($authResponse, '235')) {
                // Fallback: AUTH PLAIN two-step (server sends 334 first)
                $this->send_cmd($socket, "AUTH PLAIN");
                $r = $this->read($socket);
                if (str_starts_with($r, '334')) {
                    $this->send_cmd($socket, $credentials);
                    $authResponse = $this->read($socket);
                }
                if (!str_starts_with($authResponse, '235')) {
                    throw new \RuntimeException("SMTP AUTH failed: {$authResponse}");
                }
            }

            // Envelope
            $this->send_cmd($socket, "MAIL FROM:<{$this->fromEmail}>");
            $this->expect($socket, 250);
            $this->send_cmd($socket, "RCPT TO:<{$to}>");
            $this->expect($socket, 250);
            $this->send_cmd($socket, "DATA");
            $this->expect($socket, 354);

            // Headers + body
            $toDisplay = $toName ? "{$toName} <{$to}>" : $to;
            $msgId     = '<' . time() . '.' . bin2hex(random_bytes(4)) . '@' . ($_SERVER['HTTP_HOST'] ?? 'phantomsmoking.com') . '>';
            $message   = "From: {$this->fromName} <{$this->fromEmail}>\r\n"
                       . "To: {$toDisplay}\r\n"
                       . "Subject: {$subject}\r\n"
                       . "Message-ID: {$msgId}\r\n"
                       . "Date: " . date('r') . "\r\n"
                       . "MIME-Version: 1.0\r\n"
                       . "Content-Type: text/html; charset=UTF-8\r\n"
                       . "Content-Transfer-Encoding: base64\r\n"
                       . "\r\n"
                       . chunk_split(base64_encode($htmlBody))
                       . "\r\n.";
            $this->send_cmd($socket, $message);
            $this->expect($socket, 250);
            $this->send_cmd($socket, "QUIT");
            fclose($socket);
            return true;
        } catch (\Throwable $e) {
            error_log('[Mailer] SMTP error: ' . $e->getMessage());
            return $this->fallbackMail($to, $subject, $htmlBody, $toName);
        }
    }

    private function connect()
    {
        // Port 465 = SSL wrap, Port 587 = plain then STARTTLS
        $host = ($this->encryption === 'ssl' || $this->port === 465)
            ? "ssl://{$this->host}"
            : "tcp://{$this->host}";
        $ctx = stream_context_create(['ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]]);
        $socket = stream_socket_client("{$host}:{$this->port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$socket) throw new \RuntimeException("SMTP connect failed ({$host}:{$this->port}): {$errstr}");
        stream_set_timeout($socket, 15);
        return $socket;
    }

    private function send_cmd($socket, string $cmd): void
    {
        fwrite($socket, $cmd . "\r\n");
    }

    private function read($socket): string
    {
        $data = '';
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $data;
    }

    private function expect($socket, int $code): void
    {
        $response = $this->read($socket);
        if ((int)substr($response, 0, 3) !== $code) {
            throw new \RuntimeException("SMTP expected {$code}, got: {$response}");
        }
    }

    private function fallbackMail(string $to, string $subject, string $body, string $toName = ''): bool
    {
        $fromEmail = $this->fromEmail;
        $fromName  = $this->fromName;
        // When sender = recipient, PHP mail() loops. Use server's default sender.
        if (strtolower($to) === strtolower($fromEmail)) {
            $serverHost = $_SERVER['HTTP_HOST'] ?? 'phantomsmoking.com';
            $fromEmail  = 'no-reply@' . $serverHost;
        }
        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            "From: {$fromName} <{$fromEmail}>",
            "Reply-To: {$this->fromEmail}",
            'X-Mailer: PHP/' . phpversion(),
        ]);
        // Use sendmail_from ini for Windows, -f flag for Linux
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            ini_set('sendmail_from', $fromEmail);
            return mail($to, $subject, $body, $headers);
        }
        return mail($to, $subject, $body, $headers, "-f{$fromEmail}");
    }
}
