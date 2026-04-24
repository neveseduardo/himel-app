<?php

namespace App\Domain\Dashboard\Services;

use App\Domain\Dashboard\Contracts\DashboardServiceInterface;
use App\Domain\Transaction\Models\Transaction;

class DashboardService implements DashboardServiceInterface
{
    /**
     * @return array{pending: int, paid: int, overdue: int}
     */
    public function getStatusCountsForPeriod(string $periodUid, string $userUid): array
    {
        $counts = Transaction::where('period_uid', $periodUid)
            ->forUser($userUid)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'pending' => (int) ($counts[Transaction::STATUS_PENDING] ?? 0),
            'paid' => (int) ($counts[Transaction::STATUS_PAID] ?? 0),
            'overdue' => (int) ($counts[Transaction::STATUS_OVERDUE] ?? 0),
        ];
    }

    /**
     * @return array<int, array{category_name: string, total: float}>
     */
    public function getCategoryBreakdownForPeriod(string $periodUid, string $userUid): array
    {
        return Transaction::where('transactions.period_uid', $periodUid)
            ->where('transactions.user_uid', $userUid)
            ->where('transactions.direction', Transaction::DIRECTION_OUTFLOW)
            ->join('categories', 'transactions.category_uid', '=', 'categories.uid')
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total')
            ->groupBy('transactions.category_uid', 'categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'category_name' => $row->category_name,
                'total' => (float) $row->total,
            ])
            ->values()
            ->toArray();
    }
}
