<?php

namespace App\Domain\CreditCardCharge\Services;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Contracts\CreditCardChargeServiceInterface;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\Transaction\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditCardChargeService implements CreditCardChargeServiceInterface
{
    public function getAll(string $userUid): array
    {
        return CreditCardCharge::whereHas('creditCard', function ($query) use ($userUid) {
            $query->where('user_uid', $userUid);
        })->with(['creditCard', 'installments'])->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = CreditCardCharge::whereHas('creditCard', function ($query) use ($userUid) {
            $query->where('user_uid', $userUid);
        })->with(['creditCard', 'installments']);

        $query->when($filters['card_uid'] ?? null, fn ($q, $cardUid) => $q->where('credit_card_uid', $cardUid));

        $query->when($filters['search'] ?? null, fn ($q, $search) => $q->where('description', 'like', "%{$search}%"));

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

    public function getByUid(string $uid, string $userUid): ?CreditCardCharge
    {
        return CreditCardCharge::where('uid', $uid)
            ->whereHas('creditCard', function ($query) use ($userUid) {
                $query->where('user_uid', $userUid);
            })
            ->with(['creditCard', 'installments'])
            ->first();
    }

    public function create(array $data, string $userUid): CreditCardCharge
    {
        if ($data['total_installments'] < 1 || $data['total_installments'] > 48) {
            throw new \InvalidArgumentException('O número de parcelas deve ser entre 1 e 48.');
        }

        try {
            return DB::transaction(function () use ($data, $userUid) {
                $card = CreditCard::where('uid', $data['credit_card_uid'])
                    ->where('user_uid', $userUid)
                    ->first();

                if (! $card) {
                    throw new \InvalidArgumentException('Cartão de crédito não encontrado.');
                }

                $charge = CreditCardCharge::create([
                    'credit_card_uid' => $data['credit_card_uid'],
                    'amount' => $data['amount'],
                    'description' => $data['description'],
                    'total_installments' => $data['total_installments'],
                    'purchase_date' => $data['purchase_date'],
                ]);

                $totalCents = (int) round($data['amount'] * 100);
                $baseCents = intdiv($totalCents, $data['total_installments']);
                $remainder = $totalCents % $data['total_installments'];

                $canCreateTransactions = ! empty($data['account_uid']) && ! empty($data['category_uid']);

                $purchaseDate = Carbon::parse($data['purchase_date']);

                for ($i = 1; $i <= $data['total_installments']; $i++) {
                    $installmentCents = $baseCents + ($i === $data['total_installments'] ? $remainder : 0);
                    $installmentAmount = $installmentCents / 100;
                    $dueDate = $purchaseDate->copy()->addMonths($i)->day($card->due_day);

                    $transactionUid = null;

                    if ($canCreateTransactions) {
                        $transaction = Transaction::create([
                            'user_uid' => $userUid,
                            'account_uid' => $data['account_uid'],
                            'category_uid' => $data['category_uid'],
                            'amount' => $installmentAmount,
                            'direction' => Transaction::DIRECTION_OUTFLOW,
                            'status' => Transaction::STATUS_PENDING,
                            'source' => Transaction::SOURCE_CREDIT_CARD,
                            'description' => $charge->description." ({$i}/{$data['total_installments']})",
                            'occurred_at' => $dueDate,
                            'due_date' => $dueDate,
                            'reference_id' => $charge->uid,
                        ]);

                        $transactionUid = $transaction->uid;
                    }

                    CreditCardInstallment::create([
                        'credit_card_charge_uid' => $charge->uid,
                        'transaction_uid' => $transactionUid,
                        'installment_number' => $i,
                        'due_date' => $dueDate,
                        'amount' => $installmentAmount,
                        'paid_at' => null,
                    ]);
                }

                Log::info('CreditCardCharge created', [
                    'uid' => $charge->uid,
                    'user_uid' => $userUid,
                ]);

                return $charge;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create credit card charge', [
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(string $uid, array $data, string $userUid): ?CreditCardCharge
    {
        $charge = $this->getByUid($uid, $userUid);

        if (! $charge) {
            return null;
        }

        try {
            return DB::transaction(function () use ($charge, $data) {
                $charge->update(array_filter($data, fn ($value) => $value !== null));

                Log::info('CreditCardCharge updated', ['uid' => $charge->uid]);

                return $charge->fresh();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to update credit card charge', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete(string $uid, string $userUid): bool
    {
        $charge = $this->getByUid($uid, $userUid);

        if (! $charge) {
            return false;
        }

        try {
            return DB::transaction(function () use ($charge) {
                $charge->installments()->delete();

                Log::info('CreditCardCharge deleted', ['uid' => $charge->uid]);

                return $charge->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to delete credit card charge', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
