<?php

namespace App\Services;

use App\Models\FinancialCreditCardInstallment;
use App\Services\Interfaces\IInstallmentService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallmentService implements IInstallmentService
{
    public function getAll(string $userUid): array
    {
        return FinancialCreditCardInstallment::whereHas('charge.creditCard', function ($query) use ($userUid) {
            $query->where('user_uid', $userUid);
        })->with(['charge.creditCard', 'transaction'])->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialCreditCardInstallment::whereHas('charge.creditCard', function ($query) use ($userUid) {
            $query->where('user_uid', $userUid);
        })->with(['charge.creditCard', 'transaction']);

        $query->when($filters['charge_uid'] ?? null, fn ($q, $chargeUid) => $q->where('credit_card_charge_uid', $chargeUid));

        $query->when($filters['paid'] ?? null, fn ($q, $paid) => $q->whereNotNull('paid_at'));

        $query->when($filters['date_from'] ?? null, fn ($q, $dateFrom) => $q->where('due_date', '>=', $dateFrom));

        $query->when($filters['date_to'] ?? null, fn ($q, $dateTo) => $q->where('due_date', '<=', $dateTo));

        $query->orderBy('due_date');

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

    public function getByUid(string $uid, string $userUid): ?FinancialCreditCardInstallment
    {
        return FinancialCreditCardInstallment::where('uid', $uid)
            ->whereHas('charge.creditCard', function ($query) use ($userUid) {
                $query->where('user_uid', $userUid);
            })
            ->with(['charge.creditCard', 'transaction'])
            ->first();
    }

    public function getByChargeUid(string $chargeUid, string $userUid): array
    {
        return FinancialCreditCardInstallment::where('credit_card_charge_uid', $chargeUid)
            ->whereHas('charge.creditCard', function ($query) use ($userUid) {
                $query->where('user_uid', $userUid);
            })
            ->orderBy('installment_number')
            ->get()
            ->toArray();
    }

    public function markAsPaid(string $uid, string $userUid): ?FinancialCreditCardInstallment
    {
        $installment = $this->getByUid($uid, $userUid);

        if (! $installment) {
            return null;
        }

        return DB::transaction(function () use ($installment) {
            $installment->paid_at = now();
            $installment->save();

            Log::info('FinancialCreditCardInstallment marked as paid', [
                'uid' => $installment->uid,
            ]);

            return $installment;
        });
    }
}
