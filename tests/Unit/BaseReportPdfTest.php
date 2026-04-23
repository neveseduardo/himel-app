<?php

namespace Tests\Unit;

use App\Domain\Shared\Pdf\BaseReportPdf;
use PHPUnit\Framework\TestCase;

/**
 * Concrete test subclass to access protected methods of BaseReportPdf.
 */
class ConcreteReportPdf extends BaseReportPdf
{
    public function __construct()
    {
        parent::__construct('Test Report');
    }

    protected function getViewName(): string
    {
        return 'test-view';
    }

    protected function getViewData(): array
    {
        return [];
    }

    protected function getFileName(): string
    {
        return 'test.pdf';
    }

    /** Expose protected formatCurrency for testing. */
    public function testFormatCurrency(float $value): string
    {
        return $this->formatCurrency($value);
    }

    /** Expose protected formatDate for testing. */
    public function testFormatDate(?string $date): string
    {
        return $this->formatDate($date);
    }

    /** Expose protected getMonthName for testing. */
    public function testGetMonthName(int $month): string
    {
        return $this->getMonthName($month);
    }
}

class BaseReportPdfTest extends TestCase
{
    private ConcreteReportPdf $report;

    protected function setUp(): void
    {
        parent::setUp();
        $this->report = new ConcreteReportPdf;
    }

    // =========================================================================
    // formatCurrency
    // =========================================================================

    public function test_format_currency_with_positive_value(): void
    {
        $this->assertSame('R$ 1.234,56', $this->report->testFormatCurrency(1234.56));
    }

    public function test_format_currency_with_zero(): void
    {
        $this->assertSame('R$ 0,00', $this->report->testFormatCurrency(0));
    }

    public function test_format_currency_with_negative_value(): void
    {
        $this->assertSame('R$ -500,00', $this->report->testFormatCurrency(-500.00));
    }

    public function test_format_currency_with_large_value(): void
    {
        $this->assertSame('R$ 1.000.000,99', $this->report->testFormatCurrency(1000000.99));
    }

    // =========================================================================
    // formatDate
    // =========================================================================

    public function test_format_date_with_valid_date_string(): void
    {
        $this->assertSame('15/06/2025', $this->report->testFormatDate('2025-06-15'));
    }

    public function test_format_date_with_null_returns_dash(): void
    {
        $this->assertSame('—', $this->report->testFormatDate(null));
    }

    public function test_format_date_with_iso_8601_format(): void
    {
        $this->assertSame('01/01/2025', $this->report->testFormatDate('2025-01-01T10:30:00Z'));
    }

    // =========================================================================
    // getMonthName
    // =========================================================================

    public function test_get_month_name_with_valid_months(): void
    {
        $expected = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
            4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
            10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        foreach ($expected as $month => $name) {
            $this->assertSame($name, $this->report->testGetMonthName($month), "Month {$month} should be {$name}");
        }
    }

    public function test_get_month_name_with_invalid_month_returns_empty_string(): void
    {
        $this->assertSame('', $this->report->testGetMonthName(0));
        $this->assertSame('', $this->report->testGetMonthName(13));
    }
}
