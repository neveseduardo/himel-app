<?php

namespace App\Domain\CreditCard\Services;

use App\Domain\CreditCard\Contracts\CreditCardServiceInterface;
use App\Domain\CreditCard\Models\CreditCard;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditCardService implements CreditCardServiceInterface
{
    public function getAll(string $userUid): array
    {
        return CreditCard::forUser($userUid)->get()->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = CreditCard::forUser($userUid);

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

    public function getByUid(string $uid, string $userUid): ?CreditCard
    {
        return CreditCard::where('uid', $uid)
            ->forUser($userUid)
            ->first();
    }

    public function create(array $data, string $userUid): CreditCard
    {
        try {
            return DB::transaction(function () use ($data, $userUid) {
                $card = CreditCard::create([
                    'user_uid' => $userUid,
                    'name' => $data['name'],
                    'card_type' => $data['card_type'],
                    'due_day' => $data['due_day'],
                    'closing_day' => $data['closing_day'],
                    'last_four_digits' => $data['last_four_digits'],
                ]);

                Log::info('CreditCard created', [
                    'uid' => $card->uid,
                    'user_uid' => $userUid,
                ]);

                return $card;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create credit card', [
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(string $uid, array $data, string $userUid): ?CreditCard
    {
        $card = $this->getByUid($uid, $userUid);

        if (! $card) {
            return null;
        }

        try {
            return DB::transaction(function () use ($card, $data) {
                $card->update(array_filter($data, fn ($value) => $value !== null));

                Log::info('CreditCard updated', ['uid' => $card->uid]);

                return $card->fresh();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to update credit card', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete(string $uid, string $userUid): bool
    {
        $card = $this->getByUid($uid, $userUid);

        if (! $card) {
            return false;
        }

        try {
            return DB::transaction(function () use ($card) {
                Log::info('CreditCard deleted', ['uid' => $card->uid]);

                return $card->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to delete credit card', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
