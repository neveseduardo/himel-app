<?php

namespace App\Domain\Period\Pdf;

use App\Domain\Period\Models\Period;
use App\Domain\Shared\Pdf\BaseReportPdf;
use App\Domain\Transaction\Models\Transaction;

class PeriodReportPdf extends BaseReportPdf
{
    /**
     * @param  array{
     *     period: Period,
     *     summary: array<string, mixed>,
     *     fixedExpenses: array{items: array<int, array<string, mixed>>, subtotal: float},
     *     installments: array{items: array<int, array<string, mixed>>, subtotal: float},
     *     cardBreakdown: array{cards: array<int, array<string, mixed>>, grand_total: float},
     *     inflowTransactions: array<int, Transaction>,
     *     outflowTransactions: array<int, Transaction>,
     * }  $periodData
     */
    public function __construct(private readonly array $periodData)
    {
        $period = $periodData['period'];
        $monthName = $this->getMonthName($period->month);
        parent::__construct("Relatório Financeiro — {$monthName} {$period->year}");
    }

    protected function getViewName(): string
    {
        return 'pdf.period-report';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $formatCurrency = fn (float $value): string => $this->formatCurrency($value);
        $formatDate = fn (?string $date): string => $this->formatDate($date);
        $getMonthName = fn (int $month): string => $this->getMonthName($month);

        return [
            'period' => $this->periodData['period'],
            'summary' => $this->periodData['summary'],
            'fixedExpenses' => $this->periodData['fixedExpenses'],
            'installments' => $this->periodData['installments'],
            'cardBreakdown' => $this->periodData['cardBreakdown'],
            'inflowTransactions' => $this->periodData['inflowTransactions'],
            'outflowTransactions' => $this->periodData['outflowTransactions'],
            'formatCurrency' => $formatCurrency,
            'formatDate' => $formatDate,
            'getMonthName' => $getMonthName,
        ];
    }

    protected function getFileName(): string
    {
        $period = $this->periodData['period'];
        $month = str_pad((string) $period->month, 2, '0', STR_PAD_LEFT);

        return "relatorio-periodo-{$month}-{$period->year}.pdf";
    }
}
