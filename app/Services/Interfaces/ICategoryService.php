<?php

namespace App\Services\Interfaces;

use App\Models\FinancialCategory;

interface ICategoryService
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?FinancialCategory;

    public function create(array $data, string $userUid): FinancialCategory;

    public function update(string $uid, array $data, string $userUid): ?FinancialCategory;

    public function delete(string $uid, string $userUid): bool;

    public function getByDirection(string $userUid, string $direction): array;
}
