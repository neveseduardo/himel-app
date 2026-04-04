<?php

namespace App\Services\Interfaces;

use App\Models\FinancialCreditCardCharge;

interface IChargeService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialCreditCardCharge;

    public function create(array $data, string $userUid): FinancialCreditCardCharge;

    public function update(string $uid, array $data, string $userUid): ?FinancialCreditCardCharge;

    public function delete(string $uid, string $userUid): bool;
}
