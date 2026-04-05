<?php

namespace App\Domain\Category\Services;

use App\Domain\Category\Contracts\CategoryServiceInterface;
use App\Domain\Category\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService implements CategoryServiceInterface
{
    public function getAll(string $userUid): array
    {
        return Category::forUser($userUid)->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = Category::forUser($userUid);

        $query->when($filters['direction'] ?? null, fn ($q, $direction) => $q->where('direction', $direction));

        $query->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"));

        $query->orderBy('name');

        $total = $query->count();
        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    public function getByUid(string $uid, string $userUid): ?Category
    {
        return Category::where('uid', $uid)
            ->forUser($userUid)
            ->first();
    }

    public function create(array $data, string $userUid): Category
    {
        try {
            return DB::transaction(function () use ($data, $userUid) {
                $category = Category::create([
                    'user_uid' => $userUid,
                    'name' => $data['name'],
                    'direction' => $data['direction'],
                ]);

                Log::info('Category created', [
                    'uid' => $category->uid,
                    'user_uid' => $userUid,
                ]);

                return $category;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create category', [
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(string $uid, array $data, string $userUid): ?Category
    {
        $category = $this->getByUid($uid, $userUid);

        if (! $category) {
            return null;
        }

        try {
            return DB::transaction(function () use ($category, $data) {
                $category->update(array_filter($data, fn ($value) => $value !== null));

                Log::info('Category updated', ['uid' => $category->uid]);

                return $category->fresh();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to update category', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete(string $uid, string $userUid): bool
    {
        $category = $this->getByUid($uid, $userUid);

        if (! $category) {
            return false;
        }

        try {
            return DB::transaction(function () use ($category) {
                Log::info('Category deleted', ['uid' => $category->uid]);

                return $category->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to delete category', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getByDirection(string $userUid, string $direction): array
    {
        return Category::forUser($userUid)
            ->where('direction', $direction)
            ->get()
            ->toArray();
    }
}
