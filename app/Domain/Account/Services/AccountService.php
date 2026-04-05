<?php

namespace App\Domain\Account\Services;

use App\Domain\Account\Contracts\AccountServiceInterface;
use App\Domain\Account\Models\Account;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountService implements AccountServiceInterface
{
    public function getAll(string $userUid): array
    {
        return Account::forUser($userUid)->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = Account::forUser($userUid);

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

    public function getByUid(string $uid, string $userUid): ?Account
    {
        return Account::where('uid', $uid)
            ->forUser($userUid)
            ->first();
    }

    public function create(array $data, string $userUid): Account
    {
        try {
            return DB::transaction(function () use ($data, $userUid) {
                $account = Account::create([
                    'user_uid' => $userUid,
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'balance' => $data['balance'] ?? 0,
                ]);

                Log::info('Account created', [
                    'uid' => $account->uid,
                    'user_uid' => $userUid,
                ]);

                return $account;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create account', [
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(string $uid, array $data, string $userUid): ?Account
    {
        $account = $this->getByUid($uid, $userUid);

        if (! $account) {
            return null;
        }

        try {
            return DB::transaction(function () use ($account, $data) {
                $account->update(array_filter($data, fn ($value) => $value !== null));

                Log::info('Account updated', ['uid' => $account->uid]);

                return $account->fresh();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to update account', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete(string $uid, string $userUid): bool
    {
        $account = $this->getByUid($uid, $userUid);

        if (! $account) {
            return false;
        }

        try {
            return DB::transaction(function () use ($account) {
                Log::info('Account deleted', ['uid' => $account->uid]);

                return $account->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to delete account', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function updateBalance(string $uid, float $amount, string $userUid): ?Account
    {
        $account = $this->getByUid($uid, $userUid);

        if (! $account) {
            return null;
        }

        try {
            return DB::transaction(function () use ($account, $amount) {
                $account->balance += $amount;
                $account->save();

                return $account;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to update account balance', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
