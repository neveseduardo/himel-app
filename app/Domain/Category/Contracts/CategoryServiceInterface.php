<?php

namespace App\Domain\Category\Contracts;

use App\Domain\Category\Models\Category;

interface CategoryServiceInterface
{
    public function getAll(string $userUid): array;

    public function getAllWithFilters(string $userUid, array $filters = []): array;

    public function getByUid(string $uid, string $userUid): ?Category;

    public function create(array $data, string $userUid): Category;

    public function update(string $uid, array $data, string $userUid): ?Category;

    public function delete(string $uid, string $userUid): bool;

    public function getByDirection(string $userUid, string $direction): array;
}
