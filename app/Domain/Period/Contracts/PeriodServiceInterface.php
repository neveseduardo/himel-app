<?php

namespace App\Domain\Period\Contracts;

use App\Domain\Period\Models\Period;

interface PeriodServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?Period;

    public function getOrCreate(string $userUid, int $month, int $year): Period;

    public function getCurrent(string $userUid): ?Period;

    public function create(string $userUid, int $month, int $year): Period;

    public function initializePeriod(string $uid, string $userUid): array;

    public function getByUidWithSummary(string $uid, string $userUid): ?array;

    public function getTransactionsForPeriod(string $periodUid, string $userUid, array $filters = []): array;

    public function detachAllTransactions(string $periodUid, string $userUid): int;

    public function delete(string $uid, string $userUid): bool;
}
