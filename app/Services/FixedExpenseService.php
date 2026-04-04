<?php

namespace App\Services;

use App\Models\FinancialFixedExpense;
use App\Services\Interfaces\IFixedExpenseService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixedExpenseService implements IFixedExpenseService
{
    public function getAll(string $userUid): array
    {
        return FinancialFixedExpense::forUser($userUid)
            ->with(['category'])
            ->get()
            ->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialFixedExpense::forUser($userUid)
            ->with(['category']);

        $query->when($filters['active'] ?? null, fn ($q, $active) => $q->where('active', filter_var($active, FILTER_VALIDATE_BOOLEAN)));

        $query->when($filters['category_uid'] ?? null, fn ($q, $categoryUid) => $q->where('financial_category_uid', $categoryUid));

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

    public function getByUid(string $uid, string $userUid): ?FinancialFixedExpense
    {
        return FinancialFixedExpense::where('uid', $uid)
            ->forUser($userUid)
            ->with(['category'])
            ->first();
    }

    public function create(array $data, string $userUid): FinancialFixedExpense
    {
        return DB::transaction(function () use ($data, $userUid) {
            $expense = FinancialFixedExpense::create([
                'user_uid' => $userUid,
                'financial_category_uid' => $data['financial_category_uid'],
                'name' => $data['name'],
                'amount' => $data['amount'],
                'due_day' => $data['due_day'],
                'active' => $data['active'] ?? true,
            ]);

            Log::info('FinancialFixedExpense created', [
                'uid' => $expense->uid,
                'user_uid' => $userUid,
            ]);

            return $expense;
        });
    }

    public function update(string $uid, array $data, string $userUid): ?FinancialFixedExpense
    {
        $expense = $this->getByUid($uid, $userUid);

        if (! $expense) {
            return null;
        }

        return DB::transaction(function () use ($expense, $data) {
            $expense->update(array_filter($data, fn ($value) => $value !== null));

            Log::info('FinancialFixedExpense updated', ['uid' => $expense->uid]);

            return $expense->fresh();
        });
    }

    public function delete(string $uid, string $userUid): bool
    {
        $expense = $this->getByUid($uid, $userUid);

        if (! $expense) {
            return false;
        }

        return DB::transaction(function () use ($expense) {
            Log::info('FinancialFixedExpense deleted', ['uid' => $expense->uid]);

            return $expense->delete();
        });
    }

    public function toggleActive(string $uid, string $userUid): ?FinancialFixedExpense
    {
        $expense = $this->getByUid($uid, $userUid);

        if (! $expense) {
            return null;
        }

        return DB::transaction(function () use ($expense) {
            $expense->active = ! $expense->active;
            $expense->save();

            Log::info('FinancialFixedExpense toggled', [
                'uid' => $expense->uid,
                'active' => $expense->active,
            ]);

            return $expense;
        });
    }

    public function getActive(string $userUid): array
    {
        return FinancialFixedExpense::forUser($userUid)
            ->active()
            ->with(['category'])
            ->get()
            ->toArray();
    }
}
