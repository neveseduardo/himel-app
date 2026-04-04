<?php

namespace App\Services\Interfaces;

use App\Models\FinancialFixedExpense;

interface IFixedExpenseService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialFixedExpense;

    public function create(array $data, string $userUid): FinancialFixedExpense;

    public function update(string $uid, array $data, string $userUid): ?FinancialFixedExpense;

    public function delete(string $uid, string $userUid): bool;

    public function toggleActive(string $uid, string $userUid): ?FinancialFixedExpense;

    public function getActive(string $userUid): array;
}
