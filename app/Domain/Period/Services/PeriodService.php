<?php

namespace App\Domain\Period\Services;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\Period\Contracts\PeriodServiceInterface;
use App\Domain\Period\Exceptions\PeriodAlreadyExistsException;
use App\Domain\Period\Exceptions\PeriodHasPaidTransactionsException;
use App\Domain\Period\Models\Period;
use App\Domain\Transaction\Models\Transaction;
use Carbon\Carbon;
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
        $perPage = min($filters['per_page'] ?? 10, 100);

        $query = Period::forUser($userUid)->withCount('transactions');

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

    public function create(string $userUid, int $month, int $year): Period
    {
        $exists = Period::forUser($userUid)->forMonthYear($month, $year)->exists();

        if ($exists) {
            throw PeriodAlreadyExistsException::forMonthYear($month, $year);
        }

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
        } catch (PeriodAlreadyExistsException $e) {
            throw $e;
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

        $hasPaidTransactions = Transaction::where('period_uid', $period->uid)
            ->where('status', Transaction::STATUS_PAID)
            ->exists();

        if ($hasPaidTransactions) {
            throw new PeriodHasPaidTransactionsException;
        }

        try {
            return DB::transaction(function () use ($period) {
                Transaction::where('period_uid', $period->uid)
                    ->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_OVERDUE])
                    ->update(['period_uid' => null]);

                Log::info('Period deleted', ['uid' => $period->uid]);

                return $period->delete();
            });
        } catch (PeriodHasPaidTransactionsException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to delete period', [
                'uid' => $uid,
                'user_uid' => $userUid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function initializePeriod(string $uid, string $userUid): array
    {
        $period = $this->getByUid($uid, $userUid);

        if (! $period) {
            throw new \InvalidArgumentException('Período não encontrado.');
        }

        $account = Account::forUser($userUid)->first();

        if (! $account) {
            throw new \InvalidArgumentException('É necessário ter ao menos uma conta para inicializar o período.');
        }

        return DB::transaction(function () use ($period, $userUid, $account) {
            $summary = [
                'fixed_created' => 0,
                'installments_linked' => 0,
                'installments_created' => 0,
                'skipped' => 0,
            ];

            $this->processFixedExpenses($period, $userUid, $account, $summary);
            $this->processCreditCardInstallments($period, $userUid, $account, $summary);

            Log::info('Period initialized', [
                'period_uid' => $period->uid,
                'user_uid' => $userUid,
                'summary' => $summary,
            ]);

            return $summary;
        });
    }

    /**
     * @param  array{fixed_created: int, installments_linked: int, installments_created: int, skipped: int}  $summary
     */
    private function processFixedExpenses(Period $period, string $userUid, Account $account, array &$summary): void
    {
        $fixedExpenses = FixedExpense::forUser($userUid)->active()->get();

        $existingReferenceIds = Transaction::where('period_uid', $period->uid)
            ->where('source', Transaction::SOURCE_FIXED)
            ->pluck('reference_id')
            ->toArray();

        foreach ($fixedExpenses as $fixedExpense) {
            if (in_array($fixedExpense->uid, $existingReferenceIds)) {
                $summary['skipped']++;

                continue;
            }

            $dueDate = $this->clampDueDay($fixedExpense->due_day, $period->month, $period->year);

            Transaction::create([
                'user_uid' => $userUid,
                'account_uid' => $account->uid,
                'category_uid' => $fixedExpense->category_uid,
                'amount' => $fixedExpense->amount,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_FIXED,
                'reference_id' => $fixedExpense->uid,
                'period_uid' => $period->uid,
                'due_date' => $dueDate,
                'occurred_at' => Carbon::create($period->year, $period->month, 1)->startOfDay(),
            ]);

            $summary['fixed_created']++;
        }
    }

    /**
     * @param  array{fixed_created: int, installments_linked: int, installments_created: int, skipped: int}  $summary
     */
    private function processCreditCardInstallments(Period $period, string $userUid, Account $account, array &$summary): void
    {
        $startOfMonth = Carbon::create($period->year, $period->month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $installments = CreditCardInstallment::whereHas('charge.creditCard', function ($query) use ($userUid) {
            $query->where('user_uid', $userUid);
        })
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->whereNull('paid_at')
            ->get();

        $existingReferenceIds = Transaction::where('period_uid', $period->uid)
            ->where('source', Transaction::SOURCE_CREDIT_CARD)
            ->pluck('reference_id')
            ->toArray();

        foreach ($installments as $installment) {
            if (in_array($installment->uid, $existingReferenceIds)) {
                $summary['skipped']++;

                continue;
            }

            $transactionUid = $installment->transaction_uid;

            if ($transactionUid) {
                $existingTransaction = Transaction::find($transactionUid);

                if ($existingTransaction) {
                    if ($existingTransaction->period_uid === $period->uid) {
                        $summary['skipped']++;

                        continue;
                    }

                    $existingTransaction->update(['period_uid' => $period->uid]);
                    $summary['installments_linked']++;

                    continue;
                }
            }

            $categoryUid = $this->getDefaultOutflowCategoryUid($userUid);

            Transaction::create([
                'user_uid' => $userUid,
                'account_uid' => $account->uid,
                'category_uid' => $categoryUid,
                'amount' => $installment->amount,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_CREDIT_CARD,
                'reference_id' => $installment->uid,
                'period_uid' => $period->uid,
                'due_date' => $installment->due_date,
                'occurred_at' => Carbon::create($period->year, $period->month, 1)->startOfDay(),
            ]);

            $summary['installments_created']++;
        }
    }

    private function clampDueDay(int $dueDay, int $month, int $year): Carbon
    {
        $lastDayOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->day;
        $clampedDay = min($dueDay, $lastDayOfMonth);

        return Carbon::create($year, $month, $clampedDay)->startOfDay();
    }

    private function getDefaultOutflowCategoryUid(string $userUid): string
    {
        $category = Category::forUser($userUid)
            ->outflow()
            ->first();

        if (! $category) {
            throw new \InvalidArgumentException('É necessário ter ao menos uma categoria de saída para inicializar o período.');
        }

        return $category->uid;
    }

    public function getByUidWithSummary(string $uid, string $userUid): ?array
    {
        $period = $this->getByUid($uid, $userUid);

        if (! $period) {
            return null;
        }

        $totals = Transaction::where('period_uid', $period->uid)
            ->selectRaw('COALESCE(SUM(CASE WHEN direction = ? THEN amount ELSE 0 END), 0) as total_inflow', [Transaction::DIRECTION_INFLOW])
            ->selectRaw('COALESCE(SUM(CASE WHEN direction = ? THEN amount ELSE 0 END), 0) as total_outflow', [Transaction::DIRECTION_OUTFLOW])
            ->first();

        $totalInflow = (float) $totals->total_inflow;
        $totalOutflow = (float) $totals->total_outflow;

        return [
            'period' => $period,
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'balance' => $totalInflow - $totalOutflow,
        ];
    }

    public function detachAllTransactions(string $periodUid, string $userUid): int
    {
        return DB::transaction(function () use ($periodUid, $userUid) {
            $count = Transaction::where('period_uid', $periodUid)
                ->forUser($userUid)
                ->update(['period_uid' => null]);

            Log::info('All transactions detached from period', [
                'period_uid' => $periodUid,
                'user_uid' => $userUid,
                'count' => $count,
            ]);

            return $count;
        });
    }

    public function getTransactionsForPeriod(string $periodUid, string $userUid, array $filters = []): array
    {
        $page = $filters['page'] ?? 1;
        $perPage = min($filters['per_page'] ?? 10, 100);

        $query = Transaction::where('period_uid', $periodUid)
            ->forUser($userUid)
            ->with(['account', 'category']);

        $query->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
        $query->when($filters['direction'] ?? null, fn ($q, $direction) => $q->where('direction', $direction));
        $query->when($filters['source'] ?? null, fn ($q, $source) => $q->where('source', $source));

        $query->orderBy('due_date', 'asc')->orderBy('created_at', 'desc');

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
}
