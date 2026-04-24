<?php

namespace App\Domain\Dashboard\Controllers;

use App\Domain\Dashboard\Contracts\DashboardServiceInterface;
use App\Domain\Period\Contracts\PeriodServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardPageController
{
    public function __construct(
        private readonly PeriodServiceInterface $periodService,
        private readonly DashboardServiceInterface $dashboardService,
    ) {}

    public function __invoke(Request $request): InertiaResponse
    {
        $userUid = $request->user()->uid;
        $periods = $this->periodService->getAll($userUid);

        // Resolve period: query param > current > null
        $period = null;
        if ($request->query('period')) {
            $period = $this->periodService->getByUid($request->query('period'), $userUid);
        }
        if (! $period) {
            $period = $this->periodService->getCurrent($userUid);
        }

        // Build data (zeroed if no period)
        $summary = $this->buildEmptySummary();
        $cardBreakdown = ['cards' => [], 'grand_total' => 0];
        $statusCounts = ['pending' => 0, 'paid' => 0, 'overdue' => 0];
        $categoryBreakdown = [];

        if ($period) {
            $periodData = $this->periodService->getByUidWithSummary($period->uid, $userUid);
            if ($periodData) {
                $summary = [
                    'total_inflow' => $periodData['total_inflow'],
                    'total_outflow' => $periodData['total_outflow'],
                    'balance' => $periodData['balance'],
                    'total_fixed_expenses' => $periodData['total_fixed_expenses'],
                    'total_credit_card_installments' => $periodData['total_credit_card_installments'],
                    'total_manual' => $periodData['total_manual'],
                    'total_transfer' => $periodData['total_transfer'],
                    'inflow_manual' => $periodData['inflow_manual'],
                    'inflow_transfer' => $periodData['inflow_transfer'],
                ];
            }
            $cardBreakdown = $this->periodService->getCardBreakdownForPeriod($period->uid, $userUid);
            $statusCounts = $this->dashboardService->getStatusCountsForPeriod($period->uid, $userUid);
            $categoryBreakdown = $this->dashboardService->getCategoryBreakdownForPeriod($period->uid, $userUid);
        }

        return Inertia::render('Dashboard', [
            'period' => $period,
            'summary' => $summary,
            'cardBreakdown' => $cardBreakdown,
            'periods' => $periods,
            'statusCounts' => $statusCounts,
            'categoryBreakdown' => $categoryBreakdown,
        ]);
    }

    /**
     * @return array{total_inflow: float, total_outflow: float, balance: float, total_fixed_expenses: float, total_credit_card_installments: float, total_manual: float, total_transfer: float, inflow_manual: float, inflow_transfer: float}
     */
    private function buildEmptySummary(): array
    {
        return [
            'total_inflow' => 0,
            'total_outflow' => 0,
            'balance' => 0,
            'total_fixed_expenses' => 0,
            'total_credit_card_installments' => 0,
            'total_manual' => 0,
            'total_transfer' => 0,
            'inflow_manual' => 0,
            'inflow_transfer' => 0,
        ];
    }
}
