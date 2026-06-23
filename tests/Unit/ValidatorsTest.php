<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ValidatorsTest extends TestCase
{
    // ─── validate_email ───────────────────────────────────────────────────────

    #[Test]
    public function validEmailPassesValidation(): void
    {
        $this->assertTrue(validate_email('user@example.com'));
        $this->assertTrue(validate_email('user+tag@example.co.uk'));
        $this->assertTrue(validate_email('admin@phantom-smoking.ae'));
    }

    #[Test]
    public function invalidEmailFailsValidation(): void
    {
        $this->assertFalse(validate_email(''));
        $this->assertFalse(validate_email('notanemail'));
        $this->assertFalse(validate_email('@domain.com'));
        $this->assertFalse(validate_email('user@'));
        $this->assertFalse(validate_email('user @domain.com'));
        $this->assertFalse(validate_email('<script>alert(1)</script>@x.com'));
    }

    // ─── validate_phone_uae ───────────────────────────────────────────────────

    #[Test]
    public function validUAEPhoneNumbersPass(): void
    {
        $this->assertTrue(validate_phone_uae('+971501234567'));
        $this->assertTrue(validate_phone_uae('00971501234567'));
        $this->assertTrue(validate_phone_uae('0501234567'));
        $this->assertTrue(validate_phone_uae('+971 50 123 4567'));
    }

    #[Test]
    public function invalidUAEPhoneNumbersFail(): void
    {
        $this->assertFalse(validate_phone_uae(''));
        $this->assertFalse(validate_phone_uae('123'));
        $this->assertFalse(validate_phone_uae('+1 212 555 0100'));
        $this->assertFalse(validate_phone_uae('abcdefghij'));
        $this->assertFalse(validate_phone_uae('+971 XX XXXX'));
    }

    // ─── validate_password_strength ───────────────────────────────────────────

    #[Test]
    public function strongPasswordHasNoErrors(): void
    {
        $errors = validate_password_strength('SecurePass1');
        $this->assertEmpty($errors);
    }

    #[Test]
    public function shortPasswordFails(): void
    {
        $errors = validate_password_strength('Ab1');
        $this->assertContains('Password must be at least 8 characters', $errors);
    }

    #[Test]
    public function passwordWithoutUppercaseFails(): void
    {
        $errors = validate_password_strength('lowercase1');
        $this->assertContains('Password must contain an uppercase letter', $errors);
    }

    #[Test]
    public function passwordWithoutNumberFails(): void
    {
        $errors = validate_password_strength('NoNumbersHere');
        $this->assertContains('Password must contain a number', $errors);
    }

    #[Test]
    public function completelyWeakPasswordReturnsAllErrors(): void
    {
        $errors = validate_password_strength('abc');
        $this->assertCount(3, $errors);
    }

    // ─── validate_age ─────────────────────────────────────────────────────────

    #[Test]
    public function adultAgePassesValidation(): void
    {
        $dob = date('Y-m-d', strtotime('-25 years'));
        $this->assertTrue(validate_age($dob, 18));
    }

    #[Test]
    public function minorAgeFailsValidation(): void
    {
        $dob = date('Y-m-d', strtotime('-16 years'));
        $this->assertFalse(validate_age($dob, 18));
    }

    #[Test]
    public function exactlyEighteenPassesValidation(): void
    {
        $dob = date('Y-m-d', strtotime('-18 years'));
        $this->assertTrue(validate_age($dob, 18));
    }

    // ─── sanitize_string ──────────────────────────────────────────────────────

    #[Test]
    public function sanitizeStringStripsHtmlTags(): void
    {
        $this->assertSame('alert xss', sanitize_string('<script>alert xss</script>'));
        $this->assertSame('Hello World', sanitize_string('<b>Hello</b> World'));
    }

    #[Test]
    public function sanitizeStringTrimWhitespace(): void
    {
        $this->assertSame('hello', sanitize_string('  hello  '));
    }

    #[Test]
    public function sanitizeStringDeepProcessesArray(): void
    {
        $data = ['name' => '<b>Ali</b>', 'age' => 25];
        $result = sanitize_string_deep($data);
        $this->assertSame('Ali', $result['name']);
        $this->assertSame(25, $result['age']);
    }
}
