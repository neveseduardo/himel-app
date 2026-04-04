<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Services\Interfaces\ITransactionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService implements ITransactionService
{
    public function getAll(string $userUid): array
    {
        return FinancialTransaction::forUser($userUid)
            ->with(['account', 'category'])
            ->get()
            ->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialTransaction::forUser($userUid)
            ->with(['account', 'category']);

        $query->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));

        $query->when($filters['direction'] ?? null, fn ($q, $direction) => $q->where('direction', $direction));

        $query->when($filters['source'] ?? null, fn ($q, $source) => $q->where('source', $source));

        $query->when($filters['account_uid'] ?? null, fn ($q, $accountUid) => $q->where('financial_account_uid', $accountUid));

        $query->when($filters['category_uid'] ?? null, fn ($q, $categoryUid) => $q->where('financial_category_uid', $categoryUid));

        $query->when($filters['date_from'] ?? null, fn ($q, $dateFrom) => $q->where('occurred_at', '>=', $dateFrom));

        $query->when($filters['date_to'] ?? null, fn ($q, $dateTo) => $q->where('occurred_at', '<=', $dateTo));

        $query->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('account', fn ($aq) => $aq->where('name', 'like', "%{$search}%")));

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

    public function getByUid(string $uid, string $userUid): ?FinancialTransaction
    {
        return FinancialTransaction::where('uid', $uid)
            ->forUser($userUid)
            ->with(['account', 'category'])
            ->first();
    }

    public function create(array $data, string $userUid): FinancialTransaction
    {
        return DB::transaction(function () use ($data, $userUid) {
            $category = FinancialCategory::where('uid', $data['financial_category_uid'])->first();

            if ($category && $category->direction !== $data['direction']) {
                throw new \InvalidArgumentException(
                    'A categoria não corresponde à direção da transação.'
                );
            }

            $transaction = FinancialTransaction::create([
                'user_uid' => $userUid,
                'financial_account_uid' => $data['financial_account_uid'],
                'financial_category_uid' => $data['financial_category_uid'],
                'amount' => $data['amount'],
                'direction' => $data['direction'],
                'status' => $data['status'],
                'source' => $data['source'],
                'occurred_at' => $data['occurred_at'],
                'due_date' => $data['due_date'] ?? null,
                'paid_at' => $data['paid_at'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
            ]);

            if ($transaction->status === FinancialTransaction::STATUS_PAID) {
                $this->updateAccountBalance(
                    $transaction->financial_account_uid,
                    $transaction->amount,
                    $transaction->direction
                );
            }

            Log::info('FinancialTransaction created', [
                'uid' => $transaction->uid,
                'user_uid' => $userUid,
            ]);

            return $transaction;
        });
    }

    public function update(string $uid, array $data, string $userUid): ?FinancialTransaction
    {
        $transaction = $this->getByUid($uid, $userUid);

        if (! $transaction) {
            return null;
        }

        return DB::transaction(function () use ($transaction, $data) {
            $oldStatus = $transaction->status;

            $transaction->update(array_filter($data, fn ($value) => $value !== null));

            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                if ($data['status'] === FinancialTransaction::STATUS_PAID && $oldStatus !== FinancialTransaction::STATUS_PAID) {
                    $this->updateAccountBalance(
                        $transaction->financial_account_uid,
                        $transaction->amount,
                        $transaction->direction
                    );
                } elseif ($oldStatus === FinancialTransaction::STATUS_PAID && $data['status'] !== FinancialTransaction::STATUS_PAID) {
                    $this->updateAccountBalance(
                        $transaction->financial_account_uid,
                        $transaction->amount,
                        $transaction->direction,
                        true
                    );
                }
            }

            Log::info('FinancialTransaction updated', ['uid' => $transaction->uid]);

            return $transaction->fresh();
        });
    }

    public function delete(string $uid, string $userUid): bool
    {
        $transaction = $this->getByUid($uid, $userUid);

        if (! $transaction) {
            return false;
        }

        return DB::transaction(function () use ($transaction) {
            if ($transaction->status === FinancialTransaction::STATUS_PAID) {
                $this->updateAccountBalance(
                    $transaction->financial_account_uid,
                    $transaction->amount,
                    $transaction->direction,
                    true
                );
            }

            Log::info('FinancialTransaction deleted', ['uid' => $transaction->uid]);

            return $transaction->delete();
        });
    }

    public function markAsPaid(string $uid, string $userUid): ?FinancialTransaction
    {
        return $this->update($uid, [
            'status' => FinancialTransaction::STATUS_PAID,
            'paid_at' => now(),
        ], $userUid);
    }

    public function markAsPending(string $uid, string $userUid): ?FinancialTransaction
    {
        return $this->update($uid, [
            'status' => FinancialTransaction::STATUS_PENDING,
            'paid_at' => null,
        ], $userUid);
    }

    private function updateAccountBalance(string $accountUid, float $amount, string $direction, bool $reverse = false): void
    {
        $account = FinancialAccount::where('uid', $accountUid)->first();

        if ($account) {
            $modifier = $direction === FinancialTransaction::DIRECTION_INFLOW ? 1 : -1;
            if ($reverse) {
                $modifier *= -1;
            }
            $account->balance += $amount * $modifier;
            $account->save();
        }
    }
}
