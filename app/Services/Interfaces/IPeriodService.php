<?php

namespace App\Services\Interfaces;

use App\Models\FinancialPeriod;

interface IPeriodService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialPeriod;

    public function getOrCreate(string $userUid, int $month, int $year): FinancialPeriod;

    public function getCurrent(string $userUid): ?FinancialPeriod;

    public function delete(string $uid, string $userUid): bool;
}
