<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Models\FinancialTransfer;
use App\Services\Interfaces\ITransferService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService implements ITransferService
{
    public function getAll(string $userUid): array
    {
        return FinancialTransfer::forUser($userUid)
            ->with(['fromAccount', 'toAccount'])
            ->get()
            ->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialTransfer::forUser($userUid)
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

    public function getByUid(string $uid, string $userUid): ?FinancialTransfer
    {
        return FinancialTransfer::where('uid', $uid)
            ->forUser($userUid)
            ->with(['fromAccount', 'toAccount'])
            ->first();
    }

    public function create(array $data, string $userUid): FinancialTransfer
    {
        return DB::transaction(function () use ($data, $userUid) {
            $fromAccount = FinancialAccount::where('uid', $data['from_account_uid'])
                ->forUser($userUid)
                ->first();

            $toAccount = FinancialAccount::where('uid', $data['to_account_uid'])
                ->forUser($userUid)
                ->first();

            if (! $fromAccount || ! $toAccount) {
                throw new \InvalidArgumentException('Conta(s) não encontrada(s).');
            }

            $transfer = FinancialTransfer::create([
                'user_uid' => $userUid,
                'from_account_uid' => $data['from_account_uid'],
                'to_account_uid' => $data['to_account_uid'],
                'amount' => $data['amount'],
            ]);

            $fromAccount->balance -= $data['amount'];
            $fromAccount->save();

            $toAccount->balance += $data['amount'];
            $toAccount->save();

            FinancialTransaction::create([
                'user_uid' => $userUid,
                'financial_account_uid' => $data['from_account_uid'],
                'financial_category_uid' => null,
                'amount' => $data['amount'],
                'direction' => FinancialTransaction::DIRECTION_OUTFLOW,
                'status' => FinancialTransaction::STATUS_PAID,
                'source' => FinancialTransaction::SOURCE_TRANSFER,
                'occurred_at' => now(),
                'reference_id' => $transfer->uid,
            ]);

            FinancialTransaction::create([
                'user_uid' => $userUid,
                'financial_account_uid' => $data['to_account_uid'],
                'financial_category_uid' => null,
                'amount' => $data['amount'],
                'direction' => FinancialTransaction::DIRECTION_INFLOW,
                'status' => FinancialTransaction::STATUS_PAID,
                'source' => FinancialTransaction::SOURCE_TRANSFER,
                'occurred_at' => now(),
                'reference_id' => $transfer->uid,
            ]);

            Log::info('FinancialTransfer created', [
                'uid' => $transfer->uid,
                'user_uid' => $userUid,
            ]);

            return $transfer;
        });
    }

    public function delete(string $uid, string $userUid): bool
    {
        $transfer = $this->getByUid($uid, $userUid);

        if (! $transfer) {
            return false;
        }

        return DB::transaction(function () use ($transfer) {
            $fromAccount = FinancialAccount::where('uid', $transfer->from_account_uid)->first();
            $toAccount = FinancialAccount::where('uid', $transfer->to_account_uid)->first();

            if ($fromAccount) {
                $fromAccount->balance += $transfer->amount;
                $fromAccount->save();
            }

            if ($toAccount) {
                $toAccount->balance -= $transfer->amount;
                $toAccount->save();
            }

            Log::info('FinancialTransfer deleted', ['uid' => $transfer->uid]);

            return $transfer->delete();
        });
    }
}
