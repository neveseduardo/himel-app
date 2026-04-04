<?php

namespace App\Services;

use App\Models\FinancialCategory;
use App\Services\Interfaces\ICategoryService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService implements ICategoryService
{
    public function getAll(string $userUid): array
    {
        return FinancialCategory::forUser($userUid)->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialCategory::forUser($userUid);

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

    public function getByUid(string $uid, string $userUid): ?FinancialCategory
    {
        return FinancialCategory::where('uid', $uid)
            ->forUser($userUid)
            ->first();
    }

    public function create(array $data, string $userUid): FinancialCategory
    {
        return DB::transaction(function () use ($data, $userUid) {
            $category = FinancialCategory::create([
                'user_uid' => $userUid,
                'name' => $data['name'],
                'direction' => $data['direction'],
            ]);

            Log::info('FinancialCategory created', [
                'uid' => $category->uid,
                'user_uid' => $userUid,
            ]);

            return $category;
        });
    }

    public function update(string $uid, array $data, string $userUid): ?FinancialCategory
    {
        $category = $this->getByUid($uid, $userUid);

        if (! $category) {
            return null;
        }

        return DB::transaction(function () use ($category, $data) {
            $category->update(array_filter($data, fn ($value) => $value !== null));

            Log::info('FinancialCategory updated', ['uid' => $category->uid]);

            return $category->fresh();
        });
    }

    public function delete(string $uid, string $userUid): bool
    {
        $category = $this->getByUid($uid, $userUid);

        if (! $category) {
            return false;
        }

        return DB::transaction(function () use ($category) {
            Log::info('FinancialCategory deleted', ['uid' => $category->uid]);

            return $category->delete();
        });
    }

    public function getByDirection(string $userUid, string $direction): array
    {
        return FinancialCategory::forUser($userUid)
            ->where('direction', $direction)
            ->get()
            ->toArray();
    }
}
