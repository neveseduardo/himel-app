<?php

namespace App\Domain\Period\Services;

use App\Domain\Period\Contracts\PeriodServiceInterface;
use App\Domain\Period\Models\Period;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeriodService implements PeriodServiceInterface
{
    public function getAll(string $userUid): array
    {
        return Period::forUser($userUid)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = Period::forUser($userUid);

        $query->when($filters['month'] ?? null, fn ($q, $month) => $q->where('month', $month));

        $query->when($filters['year'] ?? null, fn ($q, $year) => $q->where('year', $year));

        $query->orderBy('year', 'desc')->orderBy('month', 'desc');

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

    public function getByUid(string $uid, string $userUid): ?Period
    {
        return Period::where('uid', $uid)
            ->forUser($userUid)
            ->first();
    }

    public function getOrCreate(string $userUid, int $month, int $year): Period
    {
        $period = Period::forUser($userUid)
            ->forMonthYear($month, $year)
            ->first();

        if (! $period) {
            try {
                return DB::transaction(function () use ($userUid, $month, $year) {
                    $period = Period::create([
                        'user_uid' => $userUid,
                        'month' => $month,
                        'year' => $year,
                    ]);

                    Log::info('Period created', [
                        'uid' => $period->uid,
                        'user_uid' => $userUid,
                        'month' => $month,
                        'year' => $year,
                    ]);

                    return $period;
                });
            } catch (\Throwable $e) {
                Log::error('Failed to create period', [
                    'user_uid' => $userUid,
                    'month' => $month,
                    'year' => $year,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        }

        return $period;
    }

    public function getCurrent(string $userUid): ?Period
    {
        $month = now()->month;
        $year = now()->year;

        return Period::forUser($userUid)
            ->forMonthYear($month, $year)
            ->first();
    }

    public function delete(string $uid, string $userUid): bool
    {
        $period = $this->getByUid($uid, $userUid);

        if (! $period) {
            return false;
        }

        try {
            return DB::transaction(function () use ($period) {
                Log::info('Period deleted', ['uid' => $period->uid]);

                return $period->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Failed to delete period', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
