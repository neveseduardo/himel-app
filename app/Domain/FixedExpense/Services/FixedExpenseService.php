<?php

namespace App\Domain\FixedExpense\Services;

use App\Domain\FixedExpense\Contracts\FixedExpenseServiceInterface;
use App\Domain\FixedExpense\Models\FixedExpense;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixedExpenseService implements FixedExpenseServiceInterface
{
    public function getAll(string $userUid): array
    {
        return FixedExpense::forUser($userUid)
            ->with(['category'])
            ->get()
            ->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FixedExpense::forUser($userUid)
            ->with(['category']);

        $query->when($filters['active'] ?? null, fn ($q, $active) => $q->where('active', filter_var($active, FILTER_VALIDATE_BOOLEAN)));

        $query->when($filters['category_uid'] ?? null, fn ($q, $categoryUid) => $q->where('category_uid', $categoryUid));

        $query->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"));

        $query->orderByDesc('created_at');

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

    public function getByUid(string $uid, string $userUid): ?FixedExpense
    {
        return FixedExpense::where('uid', $uid)
            ->forUser($userUid)
            ->with(['category'])
            ->first();
    }

    public function create(array $data, string $userUid): FixedExpense
    {
        try {
            return DB::transaction(function () use ($data, $userUid) {
                $expense = FixedExpense::create([
                    'user_uid' => $userUid,
                    'category_uid' => $data['category_uid'],
                    'name' => $data['name'],
                    'amount' => $data['amount'],
                    'due_day' => $data['due_day'],
                    'active' => $data['active'] ?? true,
                ]);

                Log::info('FixedExpense created', [
                    'uid' => $expense->uid,
                    'user_uid' => $userUid,
                ]);

                return $expense;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create fixed expense', [
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(string $uid, array $data, string $userUid): ?FixedExpense
    {
        $expense = $this->getByUid($uid, $userUid);

        if (! $expense) {
            return null;
        }

        try {
            return DB::transaction(function () use ($expense, $data) {
                $expense->update(array_filter($data, fn ($value) => $value !== null));

                Log::info('FixedExpense updated', ['uid' => $expense->uid]);

                return $expense->fresh();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to update fixed expense', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete(string $uid, string $userUid): bool
    {
        $expense = $this->getByUid($uid, $userUid);

        if (! $expense) {
            return false;
        }

        try {
            return DB::transaction(function () use ($expense) {
                Log::info('FixedExpense deleted', ['uid' => $expense->uid]);

                return $expense->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to delete fixed expense', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function toggleActive(string $uid, string $userUid): ?FixedExpense
    {
        $expense = $this->getByUid($uid, $userUid);

        if (! $expense) {
            return null;
        }

        try {
            return DB::transaction(function () use ($expense) {
                $expense->active = ! $expense->active;
                $expense->save();

                Log::info('FixedExpense toggled', [
                    'uid' => $expense->uid,
                    'active' => $expense->active,
                ]);

                return $expense;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to toggle fixed expense', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getActive(string $userUid): array
    {
        return FixedExpense::forUser($userUid)
            ->active()
            ->with(['category'])
            ->get()
            ->toArray();
    }
}
