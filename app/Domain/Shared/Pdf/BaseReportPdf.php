<?php

namespace App\Domain\Shared\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

abstract class BaseReportPdf
{
    protected string $title;

    protected string $generatedAt;

    public function __construct(string $title)
    {
        $this->title = $title;
        $this->generatedAt = now()->format('d/m/Y H:i');
    }

    public function generate(): Response
    {
        $data = array_merge($this->getViewData(), [
            'title' => $this->title,
            'generatedAt' => $this->generatedAt,
        ]);

        $pdf = Pdf::loadView($this->getViewName(), $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download($this->getFileName());
    }

    abstract protected function getViewName(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function getViewData(): array;

    abstract protected function getFileName(): string;

    protected function formatCurrency(float $value): string
    {
        return 'R$ '.number_format($value, 2, ',', '.');
    }

    protected function formatDate(?string $date): string
    {
        if (! $date) {
            return '—';
        }

        return Carbon::parse($date)->format('d/m/Y');
    }

    protected function getMonthName(int $month): string
    {
        $months = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
            4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
            10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return $months[$month] ?? '';
    }
}
