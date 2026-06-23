<?php

declare(strict_types=1);

namespace App\Core;

use Laminas\Escaper\Escaper;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Defuse\Crypto\Exception\CryptoException;

/**
 * Central security service integrating Laminas Escaper and Defuse Encryption.
 * Bugsnag initialisation is handled in bootstrap (index.php).
 */
class Security
{
    private static ?Escaper $escaper = null;

    // ─── Laminas Escaper ──────────────────────────────────────────────────────

    private static function escaper(): Escaper
    {
        if (self::$escaper === null) {
            self::$escaper = new Escaper('utf-8');
        }
        return self::$escaper;
    }

    /**
     * Escape a value for safe output inside HTML content.
     */
    public static function escapeHtml(?string $value): string
    {
        return self::escaper()->escapeHtml($value ?? '');
    }

    /**
     * Escape a value for safe output inside an HTML attribute (e.g. placeholder, title).
     */
    public static function escapeHtmlAttr(?string $value): string
    {
        return self::escaper()->escapeHtmlAttr($value ?? '');
    }

    /**
     * Escape a value for safe embedding inside a JavaScript string literal.
     */
    public static function escapeJs(?string $value): string
    {
        return self::escaper()->escapeJs($value ?? '');
    }

    /**
     * Escape a value for safe use in a CSS context.
     */
    public static function escapeCss(?string $value): string
    {
        return self::escaper()->escapeCss($value ?? '');
    }

    /**
     * Escape a value for safe embedding in a URL query parameter.
     */
    public static function escapeUrl(?string $value): string
    {
        return self::escaper()->escapeUrl($value ?? '');
    }

    // ─── Defuse PHP Encryption ────────────────────────────────────────────────

    /**
     * Encrypt a string using Defuse symmetric encryption.
     * Requires APP_KEY in .env to be a Defuse-generated ASCII key.
     * Falls back gracefully with logged error if key is missing.
     */
    public static function encrypt(string $plaintext): string
    {
        try {
            $key = self::loadKey();
            if ($key === null) {
                error_log('[Security] Encryption key not configured — storing data unencrypted.');
                return base64_encode($plaintext);
            }
            return Crypto::encrypt($plaintext, $key);
        } catch (CryptoException $e) {
            error_log('[Security] Encryption failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Decrypt a string previously encrypted with self::encrypt().
     */
    public static function decrypt(string $ciphertext): string
    {
        try {
            $key = self::loadKey();
            if ($key === null) {
                return base64_decode($ciphertext) ?: '';
            }
            return Crypto::decrypt($ciphertext, $key);
        } catch (CryptoException $e) {
            error_log('[Security] Decryption failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Generate a new Defuse key and return its ASCII-safe string for storage in .env.
     * Run once: php -r "require 'vendor/autoload.php'; echo App\Core\Security::generateKey();"
     */
    public static function generateKey(): string
    {
        return Key::createNewRandomKey()->saveToAsciiSafeString();
    }

    private static function loadKey(): ?Key
    {
        $rawKey = $_ENV['APP_ENCRYPTION_KEY'] ?? '';
        if (empty($rawKey)) return null;
        try {
            return Key::loadFromAsciiSafeString($rawKey);
        } catch (\Exception $e) {
            error_log('[Security] Invalid encryption key: ' . $e->getMessage());
            return null;
        }
    }

    // ─── CSRF helpers (supplement CsrfMiddleware) ─────────────────────────────

    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
        return $token;
    }

    public static function validateCsrfToken(string $submitted): bool
    {
        $stored = $_SESSION['_csrf_token'] ?? '';
        return hash_equals($stored, $submitted);
    }

    // ─── Input sanitisation using Laminas Filter ──────────────────────────────

    /**
     * Strip all HTML tags and trim whitespace — thin wrapper so callers don't
     * need to know which library provides it.
     */
    public static function sanitize(?string $input): string
    {
        if ($input === null) return '';
        return trim(strip_tags($input));
    }

    /**
     * Sanitize an integer parameter from user input.
     */
    public static function sanitizeInt(mixed $value, int $default = 0): int
    {
        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        return ($filtered === false) ? $default : (int)$filtered;
    }
}
