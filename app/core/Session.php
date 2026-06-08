<?php
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 7200);
            // On Hostinger (reverse proxy), X-Forwarded-Proto signals HTTPS
            $isHttps  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                     || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
                     || ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on';
            $secure   = $isHttps && filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN);
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.gc_maxlifetime', (string)$lifetime);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax',  // Strict blocks cookie on POST->redirect on some proxies
            ]);
            session_name('SS_SESS');
            session_start();
            // Rotate session ID every 30 minutes to prevent fixation
            if (!isset($_SESSION['_last_regenerated'])) {
                $_SESSION['_last_regenerated'] = time();
            } elseif (time() - $_SESSION['_last_regenerated'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['_last_regenerated'] = time();
            }
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    public static function id(): string
    {
        return session_id();
    }
}
