<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class HelpersTest extends TestCase
{
    // ─── slugify ──────────────────────────────────────────────────────────────

    #[Test]
    public function slugifyConvertsSpacesToDashes(): void
    {
        $this->assertSame('hello-world', slugify('Hello World'));
    }

    #[Test]
    public function slugifyRemovesSpecialCharacters(): void
    {
        $this->assertSame('premium-cigars', slugify('Premium Cigars!!!'));
    }

    #[Test]
    public function slugifyLowercasesInput(): void
    {
        $this->assertSame('sultan-smoke', slugify('Sultan Smoke'));
    }

    #[Test]
    public function slugifyHandlesMultipleDashes(): void
    {
        $this->assertSame('vape-pod-kit', slugify('Vape  --  Pod   Kit'));
    }

    #[Test]
    public function slugifyTrimsLeadingAndTrailingDashes(): void
    {
        $this->assertSame('test', slugify('---test---'));
    }

    // ─── format_price ─────────────────────────────────────────────────────────

    #[Test]
    public function formatPriceFormatsWithDefaultCurrency(): void
    {
        $this->assertSame('AED 10.00', format_price(10.0));
    }

    #[Test]
    public function formatPriceFormatsWithCustomCurrency(): void
    {
        $this->assertSame('USD 99.99', format_price(99.99, 'USD'));
    }

    #[Test]
    public function formatPriceRoundsToTwoDecimals(): void
    {
        $this->assertSame('AED 1.50', format_price(1.5));
    }

    #[Test]
    public function formatPriceHandlesLargeAmounts(): void
    {
        $result = format_price(1234567.89);
        $this->assertSame('AED 1,234,567.89', $result);
    }

    // ─── truncate ─────────────────────────────────────────────────────────────

    #[Test]
    public function truncateDoesNothingForShortText(): void
    {
        $this->assertSame('short', truncate('short', 100));
    }

    #[Test]
    public function truncateAddsEllipsisForLongText(): void
    {
        $result = truncate('This is a long text that should be cut', 10);
        $this->assertSame('This is a ...', $result);
    }

    #[Test]
    public function truncateUsesCustomSuffix(): void
    {
        $result = truncate('Hello World', 5, ' [more]');
        $this->assertSame('Hello [more]', $result);
    }

    // ─── generate_token ───────────────────────────────────────────────────────

    #[Test]
    public function generateTokenReturnsCorrectLength(): void
    {
        $token = generate_token(32);
        $this->assertSame(64, strlen($token)); // bin2hex doubles the length
    }

    #[Test]
    public function generateTokenIsHexadecimal(): void
    {
        $token = generate_token(16);
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
    }

    #[Test]
    public function generateTokenIsUnique(): void
    {
        $this->assertNotSame(generate_token(), generate_token());
    }

    // ─── e() XSS escaping ─────────────────────────────────────────────────────

    #[Test]
    public function escapeEscapesHtmlEntities(): void
    {
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>'));
    }

    #[Test]
    public function escapeHandlesQuotes(): void
    {
        // ENT_HTML5 encodes single quotes as &apos; not &#039;
        $result = e('say "hello" & \'world\'');
        $this->assertStringContainsString('&quot;', $result);
        $this->assertStringContainsString('&amp;', $result);
        $this->assertStringNotContainsString("'", $result);
    }

    #[Test]
    public function escapeHandlesNullInput(): void
    {
        $this->assertSame('', e(null));
    }

    // ─── format_phone ─────────────────────────────────────────────────────────

    #[Test]
    public function formatPhoneConvertsLocalToInternational(): void
    {
        $this->assertSame('+971501234567', format_phone('0501234567'));
    }

    #[Test]
    public function formatPhonePreservesInternationalFormat(): void
    {
        $this->assertSame('+971501234567', format_phone('+971501234567'));
    }

    // ─── format_weight ────────────────────────────────────────────────────────

    #[Test]
    public function formatWeightDisplaysGramsForSmallValues(): void
    {
        $this->assertSame('500 g', format_weight(500));
    }

    #[Test]
    public function formatWeightConvertsToKgForLargeValues(): void
    {
        $this->assertSame('1.5 kg', format_weight(1500));
    }

    // ─── payment_method_label ─────────────────────────────────────────────────

    #[Test]
    public function paymentMethodLabelReturnsCodLabel(): void
    {
        $this->assertSame('Cash on Delivery', payment_method_label('cod'));
    }

    #[Test]
    public function paymentMethodLabelReturnsStripeLabel(): void
    {
        $this->assertSame('Card (Stripe)', payment_method_label('stripe'));
    }

    #[Test]
    public function paymentMethodLabelHandlesUnknownMethod(): void
    {
        $this->assertSame('Unknown Method', payment_method_label('unknown_method'));
    }
}
