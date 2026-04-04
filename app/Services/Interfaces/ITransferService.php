<?php

namespace App\Services\Interfaces;

use App\Models\FinancialTransfer;

interface ITransferService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialTransfer;

    public function create(array $data, string $userUid): FinancialTransfer;

    public function delete(string $uid, string $userUid): bool;
}
