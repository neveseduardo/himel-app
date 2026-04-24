<?php

namespace App\Domain\Dashboard\Contracts;

interface DashboardServiceInterface
{
    /**
     * @return array{pending: int, paid: int, overdue: int}
     */
    public function getStatusCountsForPeriod(string $periodUid, string $userUid): array;

    /**
     * @return array<int, array{category_name: string, total: float}>
     */
    public function getCategoryBreakdownForPeriod(string $periodUid, string $userUid): array;
}
