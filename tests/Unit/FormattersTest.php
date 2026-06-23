<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FormattersTest extends TestCase
{
    #[Test]
    public function formatDateReturnsFormattedDate(): void
    {
        $result = format_date('2024-01-15', 'd M Y');
        $this->assertSame('15 Jan 2024', $result);
    }

    #[Test]
    public function formatDatetimeReturnsReadableFormat(): void
    {
        $result = format_datetime('2024-01-15 14:30:00');
        $this->assertStringContainsString('2024', $result);
        $this->assertStringContainsString('Jan', $result);
    }

    #[Test]
    public function formatOrderNumberStartsWithSS(): void
    {
        $number = format_order_number();
        $this->assertStringStartsWith('SS-', $number);
    }

    #[Test]
    public function formatOrderNumberHasCorrectStructure(): void
    {
        $number = format_order_number();
        $this->assertMatchesRegularExpression('/^SS-\d{8}-\d{4}$/', $number);
    }

    #[Test]
    public function formatOrderNumberIsDateBased(): void
    {
        $number = format_order_number();
        $today = date('Ymd');
        $this->assertStringContainsString($today, $number);
    }

    #[Test]
    public function formatPhoneNormalisesLocalNumber(): void
    {
        $this->assertSame('+971501234567', format_phone('0501234567'));
    }

    #[Test]
    public function formatPhoneLeavesInternationalNumberIntact(): void
    {
        $this->assertSame('+971501234567', format_phone('+971501234567'));
    }

    #[Test]
    public function formatWeightShowsGrams(): void
    {
        $this->assertSame('250 g', format_weight(250));
    }

    #[Test]
    public function formatWeightShowsKilograms(): void
    {
        $this->assertSame('2.5 kg', format_weight(2500));
    }

    #[Test]
    public function formatWeightExactlyOneKg(): void
    {
        $this->assertSame('1.0 kg', format_weight(1000));
    }

    #[Test]
    public function paymentLabelCod(): void
    {
        $this->assertSame('Cash on Delivery', payment_method_label('cod'));
    }

    #[Test]
    public function paymentLabelTabby(): void
    {
        $this->assertSame('Tabby — Pay in 4', payment_method_label('tabby'));
    }

    #[Test]
    public function paymentLabelTamara(): void
    {
        $this->assertSame('Tamara — Pay in 3', payment_method_label('tamara'));
    }

    #[Test]
    public function paymentLabelTelr(): void
    {
        $this->assertSame('Card (Telr)', payment_method_label('telr'));
    }

    #[Test]
    public function paymentLabelDefaultFallback(): void
    {
        $result = payment_method_label('apple_pay');
        $this->assertSame('Apple Pay', $result);
    }
}
