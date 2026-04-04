<?php

namespace App\Services;

use App\Models\FinancialPeriod;
use App\Services\Interfaces\IPeriodService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeriodService implements IPeriodService
{
    public function getAll(string $userUid): array
    {
        return FinancialPeriod::forUser($userUid)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->toArray();
    }

    public function getAllWithFilters(string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 15, 100);

        $query = FinancialPeriod::forUser($userUid);

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

    public function getByUid(string $uid, string $userUid): ?FinancialPeriod
    {
        return FinancialPeriod::where('uid', $uid)
            ->forUser($userUid)
            ->first();
    }

    public function getOrCreate(string $userUid, int $month, int $year): FinancialPeriod
    {
        $period = FinancialPeriod::forUser($userUid)
            ->forMonthYear($month, $year)
            ->first();

        if (! $period) {
            $period = DB::transaction(function () use ($userUid, $month, $year) {
                $period = FinancialPeriod::create([
                    'user_uid' => $userUid,
                    'month' => $month,
                    'year' => $year,
                ]);

                Log::info('FinancialPeriod created', [
                    'uid' => $period->uid,
                    'user_uid' => $userUid,
                    'month' => $month,
                    'year' => $year,
                ]);

                return $period;
            });
        }

        return $period;
    }

    public function getCurrent(string $userUid): ?FinancialPeriod
    {
        $month = now()->month;
        $year = now()->year;

        return FinancialPeriod::forUser($userUid)
            ->forMonthYear($month, $year)
            ->first();
    }

    public function delete(string $uid, string $userUid): bool
    {
        $period = $this->getByUid($uid, $userUid);

        if (! $period) {
            return false;
        }

        return DB::transaction(function () use ($period) {
            Log::info('FinancialPeriod deleted', ['uid' => $period->uid]);

            return $period->delete();
        });
    }
}
