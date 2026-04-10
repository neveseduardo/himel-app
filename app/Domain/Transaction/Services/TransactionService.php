<?php

namespace App\Domain\Transaction\Services;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Contracts\TransactionServiceInterface;
use App\Domain\Transaction\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService implements TransactionServiceInterface
{
    public function getAll(string $userUid): array
    {
        return Transaction::forUser($userUid)
            ->with(['account', 'category'])
            ->get()
            ->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = Transaction::forUser($userUid)
            ->with(['account', 'category']);

        $query->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));

        $query->when($filters['direction'] ?? null, fn ($q, $direction) => $q->where('direction', $direction));

        $query->when($filters['source'] ?? null, fn ($q, $source) => $q->where('source', $source));

        $query->when($filters['account_uid'] ?? null, fn ($q, $accountUid) => $q->where('account_uid', $accountUid));

        $query->when($filters['category_uid'] ?? null, fn ($q, $categoryUid) => $q->where('category_uid', $categoryUid));

        $query->when($filters['date_from'] ?? null, fn ($q, $dateFrom) => $q->where('occurred_at', '>=', $dateFrom));

        $query->when($filters['date_to'] ?? null, fn ($q, $dateTo) => $q->where('occurred_at', '<=', $dateTo));

        $query->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($subQ) use ($search) {
            $subQ->where('description', 'like', "%{$search}%")
                ->orWhereHas('account', fn ($aq) => $aq->where('name', 'like', "%{$search}%"));
        }));

        $query->orderByDesc('occurred_at');

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

    public function getByUid(string $uid, string $userUid): ?Transaction
    {
        return Transaction::where('uid', $uid)
            ->forUser($userUid)
            ->with(['account', 'category'])
            ->first();
    }

    public function create(array $data, string $userUid): Transaction
    {
        try {
            return DB::transaction(function () use ($data, $userUid) {
                $category = Category::where('uid', $data['category_uid'])->first();

                if ($category && $category->direction !== $data['direction']) {
                    throw new \InvalidArgumentException(
                        'A categoria não corresponde à direção da transação.'
                    );
                }

                $transaction = Transaction::create([
                    'user_uid' => $userUid,
                    'account_uid' => $data['account_uid'],
                    'category_uid' => $data['category_uid'],
                    'amount' => $data['amount'],
                    'direction' => $data['direction'],
                    'status' => $data['status'],
                    'source' => $data['source'],
                    'description' => $data['description'] ?? null,
                    'occurred_at' => $data['occurred_at'],
                    'due_date' => $data['due_date'] ?? null,
                    'paid_at' => $data['paid_at'] ?? null,
                    'reference_id' => $data['reference_id'] ?? null,
                    'period_uid' => $data['period_uid'] ?? null,
                ]);

                if ($transaction->status === Transaction::STATUS_PAID) {
                    $this->updateAccountBalance(
                        $transaction->account_uid,
                        $transaction->amount,
                        $transaction->direction
                    );
                }

                Log::info('Transaction created', [
                    'uid' => $transaction->uid,
                    'user_uid' => $userUid,
                ]);

                return $transaction;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create transaction', [
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(string $uid, array $data, string $userUid): ?Transaction
    {
        $transaction = $this->getByUid($uid, $userUid);

        if (! $transaction) {
            return null;
        }

        try {
            return DB::transaction(function () use ($transaction, $data) {
                $oldStatus = $transaction->status;

                $transaction->update(array_filter($data, fn ($value) => $value !== null));

                if (isset($data['status']) && $data['status'] !== $oldStatus) {
                    if ($data['status'] === Transaction::STATUS_PAID && $oldStatus !== Transaction::STATUS_PAID) {
                        $this->updateAccountBalance(
                            $transaction->account_uid,
                            $transaction->amount,
                            $transaction->direction
                        );
                    } elseif ($oldStatus === Transaction::STATUS_PAID && $data['status'] !== Transaction::STATUS_PAID) {
                        $this->updateAccountBalance(
                            $transaction->account_uid,
                            $transaction->amount,
                            $transaction->direction,
                            true
                        );
                    }
                }

                Log::info('Transaction updated', ['uid' => $transaction->uid]);

                return $transaction->fresh();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to update transaction', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete(string $uid, string $userUid): bool
    {
        $transaction = $this->getByUid($uid, $userUid);

        if (! $transaction) {
            return false;
        }

        try {
            return DB::transaction(function () use ($transaction) {
                if ($transaction->status === Transaction::STATUS_PAID) {
                    $this->updateAccountBalance(
                        $transaction->account_uid,
                        $transaction->amount,
                        $transaction->direction,
                        true
                    );
                }

                Log::info('Transaction deleted', ['uid' => $transaction->uid]);

                return $transaction->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to delete transaction', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function markAsPaid(string $uid, string $userUid): ?Transaction
    {
        return $this->update($uid, [
            'status' => Transaction::STATUS_PAID,
            'paid_at' => now(),
        ], $userUid);
    }

    public function markAsPending(string $uid, string $userUid): ?Transaction
    {
        return $this->update($uid, [
            'status' => Transaction::STATUS_PENDING,
            'paid_at' => null,
        ], $userUid);
    }

    private function updateAccountBalance(string $accountUid, float $amount, string $direction, bool $reverse = false): void
    {
        $account = Account::where('uid', $accountUid)->first();

        if ($account) {
            $modifier = $direction === Transaction::DIRECTION_INFLOW ? 1 : -1;
            if ($reverse) {
                $modifier *= -1;
            }
            $account->balance += $amount * $modifier;
            $account->save();
        }
    }
}
