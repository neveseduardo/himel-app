<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Services\Interfaces\IAccountService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountService implements IAccountService
{
    public function getAll(string $userUid): array
    {
        return FinancialAccount::forUser($userUid)->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialAccount::forUser($userUid);

        $query->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type));

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

    public function getByUid(string $uid, string $userUid): ?FinancialAccount
    {
        return FinancialAccount::where('uid', $uid)
            ->forUser($userUid)
            ->first();
    }

    public function create(array $data, string $userUid): FinancialAccount
    {
        return DB::transaction(function () use ($data, $userUid) {
            $account = FinancialAccount::create([
                'user_uid' => $userUid,
                'name' => $data['name'],
                'type' => $data['type'],
                'balance' => $data['balance'] ?? 0,
            ]);

            Log::info('FinancialAccount created', [
                'uid' => $account->uid,
                'user_uid' => $userUid,
            ]);

            return $account;
        });
    }

    public function update(string $uid, array $data, string $userUid): ?FinancialAccount
    {
        $account = $this->getByUid($uid, $userUid);

        if (! $account) {
            return null;
        }

        return DB::transaction(function () use ($account, $data) {
            $account->update(array_filter($data, fn ($value) => $value !== null));

            Log::info('FinancialAccount updated', ['uid' => $account->uid]);

            return $account->fresh();
        });
    }

    public function delete(string $uid, string $userUid): bool
    {
        $account = $this->getByUid($uid, $userUid);

        if (! $account) {
            return false;
        }

        return DB::transaction(function () use ($account) {
            Log::info('FinancialAccount deleted', ['uid' => $account->uid]);

            return $account->delete();
        });
    }

    public function updateBalance(string $uid, float $amount, string $userUid): ?FinancialAccount
    {
        $account = $this->getByUid($uid, $userUid);

        if (! $account) {
            return null;
        }

        return DB::transaction(function () use ($account, $amount) {
            $account->balance += $amount;
            $account->save();

            return $account;
        });
    }
}
