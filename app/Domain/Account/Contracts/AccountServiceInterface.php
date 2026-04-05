<?php

namespace App\Domain\Account\Contracts;

use App\Domain\Account\Models\Account;

interface AccountServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?Account;

    public function create(array $data, string $userUid): Account;

    public function update(string $uid, array $data, string $userUid): ?Account;

    public function delete(string $uid, string $userUid): bool;

    public function updateBalance(string $uid, float $amount, string $userUid): ?Account;
}
