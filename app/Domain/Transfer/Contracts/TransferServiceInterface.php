<?php

namespace App\Domain\Transfer\Contracts;

use App\Domain\Transfer\Models\Transfer;

interface TransferServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?Transfer;

    public function create(array $data, string $userUid): Transfer;

    public function delete(string $uid, string $userUid): bool;
}
