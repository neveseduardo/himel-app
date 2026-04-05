<?php

namespace App\Domain\CreditCardInstallment\Contracts;

use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;

interface CreditCardInstallmentServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?CreditCardInstallment;

    public function getByChargeUid(string $chargeUid, string $userUid): array;

    public function markAsPaid(string $uid, string $userUid): ?CreditCardInstallment;
}
