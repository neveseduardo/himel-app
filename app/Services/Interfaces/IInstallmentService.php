<?php

namespace App\Services\Interfaces;

use App\Models\FinancialCreditCardInstallment;

interface IInstallmentService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialCreditCardInstallment;

    public function getByChargeUid(string $chargeUid, string $userUid): array;

    public function markAsPaid(string $uid, string $userUid): ?FinancialCreditCardInstallment;
}
