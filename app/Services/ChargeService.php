<?php

namespace App\Services;

use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardCharge;
use App\Models\FinancialCreditCardInstallment;
use App\Services\Interfaces\IChargeService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChargeService implements IChargeService
{
    public function getAll(string $userUid): array
    {
        return FinancialCreditCardCharge::whereHas('creditCard', function ($query) use ($userUid) {
            $query->where('user_uid', $userUid);
        })->with(['creditCard', 'installments'])->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialCreditCardCharge::whereHas('creditCard', function ($query) use ($userUid) {
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

    public function getByUid(string $uid, string $userUid): ?FinancialCreditCardCharge
    {
        return FinancialCreditCardCharge::where('uid', $uid)
            ->whereHas('creditCard', function ($query) use ($userUid) {
                $query->where('user_uid', $userUid);
            })
            ->with(['creditCard', 'installments'])
            ->first();
    }

    public function create(array $data, string $userUid): FinancialCreditCardCharge
    {
        return DB::transaction(function () use ($data, $userUid) {
            $card = FinancialCreditCard::where('uid', $data['credit_card_uid'])
                ->where('user_uid', $userUid)
                ->first();

            if (! $card) {
                throw new \InvalidArgumentException('Cartão de crédito não encontrado.');
            }

            $charge = FinancialCreditCardCharge::create([
                'credit_card_uid' => $data['credit_card_uid'],
                'amount' => $data['amount'],
                'description' => $data['description'],
                'total_installments' => $data['total_installments'],
            ]);

            $installmentAmount = $data['amount'] / $data['total_installments'];

            for ($i = 1; $i <= $data['total_installments']; $i++) {
                $dueDate = now()->addMonths($i)->day($card->due_day);

                FinancialCreditCardInstallment::create([
                    'credit_card_charge_uid' => $charge->uid,
                    'financial_transaction_uid' => null,
                    'installment_number' => $i,
                    'due_date' => $dueDate,
                    'amount' => $installmentAmount,
                    'paid_at' => null,
                ]);
            }

            Log::info('FinancialCreditCardCharge created', [
                'uid' => $charge->uid,
                'user_uid' => $userUid,
            ]);

            return $charge;
        });
    }

    public function update(string $uid, array $data, string $userUid): ?FinancialCreditCardCharge
    {
        $charge = $this->getByUid($uid, $userUid);

        if (! $charge) {
            return null;
        }

        return DB::transaction(function () use ($charge, $data) {
            $charge->update(array_filter($data, fn ($value) => $value !== null));

            Log::info('FinancialCreditCardCharge updated', ['uid' => $charge->uid]);

            return $charge->fresh();
        });
    }

    public function delete(string $uid, string $userUid): bool
    {
        $charge = $this->getByUid($uid, $userUid);

        if (! $charge) {
            return false;
        }

        return DB::transaction(function () use ($charge) {
            $charge->installments()->delete();

            Log::info('FinancialCreditCardCharge deleted', ['uid' => $charge->uid]);

            return $charge->delete();
        });
    }
}
