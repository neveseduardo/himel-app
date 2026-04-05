<?php

namespace App\Domain\Transaction\Contracts;

use App\Domain\Transaction\Models\Transaction;

interface TransactionServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?Transaction;

    public function create(array $data, string $userUid): Transaction;

    public function update(string $uid, array $data, string $userUid): ?Transaction;

    public function delete(string $uid, string $userUid): bool;

    public function markAsPaid(string $uid, string $userUid): ?Transaction;

    public function markAsPending(string $uid, string $userUid): ?Transaction;
}
