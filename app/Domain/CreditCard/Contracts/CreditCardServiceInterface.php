<?php

namespace App\Domain\CreditCard\Contracts;

use App\Domain\CreditCard\Models\CreditCard;

interface CreditCardServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?CreditCard;

    public function create(array $data, string $userUid): CreditCard;

    public function update(string $uid, array $data, string $userUid): ?CreditCard;

    public function delete(string $uid, string $userUid): bool;
}
