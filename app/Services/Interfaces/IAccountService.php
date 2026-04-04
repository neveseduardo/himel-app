<?php

namespace App\Services\Interfaces;

use App\Models\FinancialAccount;

interface IAccountService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialAccount;

    public function create(array $data, string $userUid): FinancialAccount;

    public function update(string $uid, array $data, string $userUid): ?FinancialAccount;

    public function delete(string $uid, string $userUid): bool;

    public function updateBalance(string $uid, float $amount, string $userUid): ?FinancialAccount;
}
