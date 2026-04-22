<?php

namespace App\Domain\Transaction\Services;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Contracts\TransactionServiceInterface;
use App\Domain\Transaction\Exceptions\InsufficientBalanceException;
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
        $perPage = min($filters['per_page'] ?? 10, 100);

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
                // 1. Validate account ownership
                $account = Account::where('uid', $data['account_uid'])
                    ->where('user_uid', $userUid)
                    ->firstOrFail();

                // 2. Validate category (only when provided)
                if (! empty($data['category_uid'])) {
                    $category = Category::where('uid', $data['category_uid'])->first();
                    if ($category && $category->direction !== $data['direction']) {
                        throw new \InvalidArgumentException(
                            'A categoria não corresponde à direção da transação.'
                        );
                    }
                }

                // 3. Create transaction with nullable fields
                $transaction = Transaction::create([
                    'user_uid' => $userUid,
                    'account_uid' => $data['account_uid'],
                    'category_uid' => $data['category_uid'] ?? null,
                    'amount' => $data['amount'],
                    'direction' => $data['direction'],
                    'status' => $data['status'] ?? 'PAID',
                    'source' => $data['source'] ?? 'MANUAL',
                    'description' => $data['description'] ?? null,
                    'occurred_at' => $data['occurred_at'],
                    'due_date' => $data['due_date'] ?? null,
                    'paid_at' => $data['paid_at'] ?? null,
                    'reference_id' => $data['reference_id'] ?? null,
                    'period_uid' => $data['period_uid'] ?? null,
                ]);

                // 4. Balance logic by direction
                if ($transaction->direction === Transaction::DIRECTION_INFLOW) {
                    $account->balance += $transaction->amount;
                    $account->save();
                } elseif ($transaction->status === Transaction::STATUS_PAID) {
                    if ($account->balance < $transaction->amount) {
                        throw new InsufficientBalanceException($account, (float) $transaction->amount);
                    }
                    $account->balance -= $transaction->amount;
                    $account->save();
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
            return DB::transaction(function () use ($transaction, $data, $userUid) {
                // Store old values before updating
                $oldStatus = $transaction->status;
                $oldAmount = (float) $transaction->amount;
                $oldDirection = $transaction->direction;

                // Load account with ownership check
                $account = Account::where('uid', $transaction->account_uid)
                    ->where('user_uid', $userUid)
                    ->first();

                // Validate category direction compatibility (only when category_uid is provided and not empty)
                if (! empty($data['category_uid'])) {
                    $direction = $data['direction'] ?? $oldDirection;
                    $category = Category::where('uid', $data['category_uid'])->first();
                    if ($category && $category->direction !== $direction) {
                        throw new \InvalidArgumentException(
                            'A categoria não corresponde à direção da transação.'
                        );
                    }
                }

                $transaction->update(array_filter($data, fn ($value) => $value !== null));

                $newAmount = (float) $transaction->amount;
                $newStatus = $transaction->status;

                // Balance logic by direction
                if ($oldDirection === Transaction::DIRECTION_INFLOW) {
                    // INFLOW: always adjust balance by the difference
                    if ($account && $newAmount !== $oldAmount) {
                        $account->balance += ($newAmount - $oldAmount);
                        $account->save();
                    }
                } else {
                    // OUTFLOW balance logic
                    if ($account) {
                        if ($oldStatus !== Transaction::STATUS_PAID && $newStatus === Transaction::STATUS_PAID) {
                            // PENDING → PAID: check sufficient balance, then debit
                            if ($account->balance < $newAmount) {
                                throw new InsufficientBalanceException($account, $newAmount);
                            }
                            $account->balance -= $newAmount;
                            $account->save();
                        } elseif ($oldStatus === Transaction::STATUS_PAID && $newStatus !== Transaction::STATUS_PAID) {
                            // PAID → PENDING: credit the amount back
                            $account->balance += $oldAmount;
                            $account->save();
                        } elseif ($oldStatus === Transaction::STATUS_PAID && $newStatus === Transaction::STATUS_PAID && $newAmount !== $oldAmount) {
                            // PAID stays PAID but amount changes: adjust by difference
                            $account->balance += $oldAmount;
                            if ($account->balance < $newAmount) {
                                throw new InsufficientBalanceException($account, $newAmount);
                            }
                            $account->balance -= $newAmount;
                            $account->save();
                        }
                        // PENDING stays PENDING: no balance change
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
            return DB::transaction(function () use ($transaction, $userUid) {
                $account = Account::where('uid', $transaction->account_uid)
                    ->where('user_uid', $userUid)
                    ->first();

                if ($account) {
                    if ($transaction->direction === Transaction::DIRECTION_INFLOW) {
                        // INFLOW: always reverse balance
                        $account->balance -= $transaction->amount;
                        $account->save();
                    } elseif ($transaction->status === Transaction::STATUS_PAID) {
                        // OUTFLOW + PAID: reverse balance (credit back)
                        $account->balance += $transaction->amount;
                        $account->save();
                    }
                    // OUTFLOW + PENDING/OVERDUE: no balance change
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
}
