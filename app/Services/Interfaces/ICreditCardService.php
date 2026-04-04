<?php

namespace App\Services\Interfaces;

use App\Models\FinancialCreditCard;

interface ICreditCardService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialCreditCard;

    public function create(array $data, string $userUid): FinancialCreditCard;

    public function update(string $uid, array $data, string $userUid): ?FinancialCreditCard;

    public function delete(string $uid, string $userUid): bool;
}
