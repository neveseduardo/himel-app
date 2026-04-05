<?php

namespace App\Domain\Transfer\Services;

use App\Domain\Account\Models\Account;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transfer\Contracts\TransferServiceInterface;
use App\Domain\Transfer\Models\Transfer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService implements TransferServiceInterface
{
    public function getAll(string $userUid): array
    {
        return Transfer::forUser($userUid)
            ->with(['fromAccount', 'toAccount'])
            ->get()
            ->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = Transfer::forUser($userUid)
            ->with(['fromAccount', 'toAccount']);

        $query->when($filters['account_uid'] ?? null, function ($q, $accountUid) {
            $q->where(function ($subQ) use ($accountUid) {
                $subQ->where('from_account_uid', $accountUid)
                    ->orWhere('to_account_uid', $accountUid);
            });
        });

        $query->when($filters['date_from'] ?? null, fn ($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom));

        $query->when($filters['date_to'] ?? null, fn ($q, $dateTo) => $q->where('created_at', '<=', $dateTo));

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

    public function getByUid(string $uid, string $userUid): ?Transfer
    {
        return Transfer::where('uid', $uid)
            ->forUser($userUid)
            ->with(['fromAccount', 'toAccount'])
            ->first();
    }

    public function create(array $data, string $userUid): Transfer
    {
        try {
            return DB::transaction(function () use ($data, $userUid) {
                $fromAccount = Account::where('uid', $data['from_account_uid'])
                    ->forUser($userUid)
                    ->first();

                $toAccount = Account::where('uid', $data['to_account_uid'])
                    ->forUser($userUid)
                    ->first();

                if (! $fromAccount || ! $toAccount) {
                    throw new \InvalidArgumentException('Conta(s) não encontrada(s).');
                }

                $transfer = Transfer::create([
                    'user_uid' => $userUid,
                    'from_account_uid' => $data['from_account_uid'],
                    'to_account_uid' => $data['to_account_uid'],
                    'amount' => $data['amount'],
                ]);

                $fromAccount->balance -= $data['amount'];
                $fromAccount->save();

                $toAccount->balance += $data['amount'];
                $toAccount->save();

                Transaction::create([
                    'user_uid' => $userUid,
                    'account_uid' => $data['from_account_uid'],
                    'category_uid' => null,
                    'amount' => $data['amount'],
                    'direction' => Transaction::DIRECTION_OUTFLOW,
                    'status' => Transaction::STATUS_PAID,
                    'source' => Transaction::SOURCE_TRANSFER,
                    'occurred_at' => now(),
                    'reference_id' => $transfer->uid,
                ]);

                Transaction::create([
                    'user_uid' => $userUid,
                    'account_uid' => $data['to_account_uid'],
                    'category_uid' => null,
                    'amount' => $data['amount'],
                    'direction' => Transaction::DIRECTION_INFLOW,
                    'status' => Transaction::STATUS_PAID,
                    'source' => Transaction::SOURCE_TRANSFER,
                    'occurred_at' => now(),
                    'reference_id' => $transfer->uid,
                ]);

                Log::info('Transfer created', [
                    'uid' => $transfer->uid,
                    'user_uid' => $userUid,
                ]);

                return $transfer;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create transfer', [
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete(string $uid, string $userUid): bool
    {
        $transfer = $this->getByUid($uid, $userUid);

        if (! $transfer) {
            return false;
        }

        try {
            return DB::transaction(function () use ($transfer) {
                $fromAccount = Account::where('uid', $transfer->from_account_uid)->first();
                $toAccount = Account::where('uid', $transfer->to_account_uid)->first();

                if ($fromAccount) {
                    $fromAccount->balance += $transfer->amount;
                    $fromAccount->save();
                }

                if ($toAccount) {
                    $toAccount->balance -= $transfer->amount;
                    $toAccount->save();
                }

                Log::info('Transfer deleted', ['uid' => $transfer->uid]);

                return $transfer->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to delete transfer', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
