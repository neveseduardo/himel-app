<?php

namespace App\Domain\FixedExpense\Contracts;

use App\Domain\FixedExpense\Models\FixedExpense;

interface FixedExpenseServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FixedExpense;

    public function create(array $data, string $userUid): FixedExpense;

    public function update(string $uid, array $data, string $userUid): ?FixedExpense;

    public function delete(string $uid, string $userUid): bool;

    public function toggleActive(string $uid, string $userUid): ?FixedExpense;

    public function getActive(string $userUid): array;
}
