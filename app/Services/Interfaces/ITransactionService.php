<?php

namespace App\Services\Interfaces;

use App\Models\FinancialTransaction;

interface ITransactionService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialTransaction;

    public function create(array $data, string $userUid): FinancialTransaction;

    public function update(string $uid, array $data, string $userUid): ?FinancialTransaction;

    public function delete(string $uid, string $userUid): bool;

    public function markAsPaid(string $uid, string $userUid): ?FinancialTransaction;

    public function markAsPending(string $uid, string $userUid): ?FinancialTransaction;
}
