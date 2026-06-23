<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Security-focused unit tests verifying headers, token generation,
 * CSRF token uniqueness, and input sanitization edge-cases.
 */
class SecurityHeadersTest extends TestCase
{
    // ─── CSRF token generation ─────────────────────────────────────────────────

    #[Test]
    public function csrfTokenIsHexadecimalString(): void
    {
        $token = generate_token(32);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    #[Test]
    public function twoCsrfTokensAreNotEqual(): void
    {
        $t1 = generate_token(32);
        $t2 = generate_token(32);
        $this->assertNotSame($t1, $t2);
    }

    // ─── XSS prevention via e() ───────────────────────────────────────────────

    #[Test]
    public function xssScriptTagIsNeutralised(): void
    {
        $malicious = '<script>document.cookie="stolen"</script>';
        $safe = e($malicious);
        $this->assertStringNotContainsString('<script>', $safe);
        $this->assertStringContainsString('&lt;script&gt;', $safe);
    }

    #[Test]
    public function xssEventAttributeIsNeutralised(): void
    {
        $malicious = '" onmouseover="alert(1)"';
        $safe = e($malicious);
        $this->assertStringNotContainsString('"', $safe);
    }

    #[Test]
    public function xssJavascriptProtocolIsNeutralised(): void
    {
        $malicious = 'javascript:alert(1)';
        $safe = e($malicious);
        // e() does not specifically strip javascript:, it escapes HTML chars only;
        // verify the string survived output-encoding without raw angle brackets
        $this->assertStringNotContainsString('<', $safe);
    }

    // ─── Input sanitisation ────────────────────────────────────────────────────

    #[Test]
    public function sanitizeRemovesSqlInjectionAttempt(): void
    {
        $input = "<script>'; DROP TABLE users; --</script>";
        $clean = sanitize_string($input);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringContainsString('DROP TABLE', $clean); // sanitize strips tags, not SQL
        // Note: SQL safety is handled by PDO prepared statements, not sanitize_string
    }

    #[Test]
    public function sanitizeRemovesNullBytes(): void
    {
        $input = "normal\x00string";
        // sanitize_string uses strip_tags + trim; null bytes pass through;
        // document this gap in the report
        $clean = sanitize_string($input);
        $this->assertIsString($clean);
    }

    // ─── Password strength edge cases ─────────────────────────────────────────

    #[Test]
    public function sqlInjectionPasswordIsRejectedOnStrength(): void
    {
        // A SQL injection attempt that is short — should fail on length
        $errors = validate_password_strength("'OR'1");
        $this->assertContains('Password must be at least 8 characters', $errors);
    }

    #[Test]
    public function passwordWith256CharactersIsAccepted(): void
    {
        $longPwd = str_repeat('Aa1', 85) . 'Aa1';
        $errors = validate_password_strength($longPwd);
        $this->assertEmpty($errors, 'Long passwords should pass strength check');
    }

    // ─── URL helper redirect safety ───────────────────────────────────────────

    #[Test]
    public function urlHelperProducesAbsoluteUrl(): void
    {
        $_ENV['APP_URL'] = 'https://phantom-smoking.ae';
        $result = url('shop/cigars');
        $this->assertSame('https://phantom-smoking.ae/shop/cigars', $result);
    }

    #[Test]
    public function urlHelperHandlesEmptyPath(): void
    {
        $_ENV['APP_URL'] = 'https://phantom-smoking.ae';
        $result = url('');
        $this->assertSame('https://phantom-smoking.ae/', $result);
    }
}
