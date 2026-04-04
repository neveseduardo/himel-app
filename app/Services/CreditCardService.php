<?php

namespace App\Services;

use App\Models\FinancialCreditCard;
use App\Services\Interfaces\ICreditCardService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditCardService implements ICreditCardService
{
    public function getAll(string $userUid): array
    {
        return FinancialCreditCard::forUser($userUid)->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialCreditCard::forUser($userUid);

        $query->when($filters['card_type'] ?? null, fn ($q, $cardType) => $q->where('card_type', $cardType));

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

    public function getByUid(string $uid, string $userUid): ?FinancialCreditCard
    {
        return FinancialCreditCard::where('uid', $uid)
            ->forUser($userUid)
            ->first();
    }

    public function create(array $data, string $userUid): FinancialCreditCard
    {
        return DB::transaction(function () use ($data, $userUid) {
            $card = FinancialCreditCard::create([
                'user_uid' => $userUid,
                'name' => $data['name'],
                'card_type' => $data['card_type'],
                'due_day' => $data['due_day'],
            ]);

            Log::info('FinancialCreditCard created', [
                'uid' => $card->uid,
                'user_uid' => $userUid,
            ]);

            return $card;
        });
    }

    public function update(string $uid, array $data, string $userUid): ?FinancialCreditCard
    {
        $card = $this->getByUid($uid, $userUid);

        if (! $card) {
            return null;
        }

        return DB::transaction(function () use ($card, $data) {
            $card->update(array_filter($data, fn ($value) => $value !== null));

            Log::info('FinancialCreditCard updated', ['uid' => $card->uid]);

            return $card->fresh();
        });
    }

    public function delete(string $uid, string $userUid): bool
    {
        $card = $this->getByUid($uid, $userUid);

        if (! $card) {
            return false;
        }

        return DB::transaction(function () use ($card) {
            Log::info('FinancialCreditCard deleted', ['uid' => $card->uid]);

            return $card->delete();
        });
    }
}
