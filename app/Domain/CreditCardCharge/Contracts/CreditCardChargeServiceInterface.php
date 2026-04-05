<?php

namespace App\Domain\CreditCardCharge\Contracts;

use App\Domain\CreditCardCharge\Models\CreditCardCharge;

interface CreditCardChargeServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?CreditCardCharge;

    public function create(array $data, string $userUid): CreditCardCharge;

    public function update(string $uid, array $data, string $userUid): ?CreditCardCharge;

    public function delete(string $uid, string $userUid): bool;
}
