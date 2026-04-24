<?php

namespace Tests\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Dashboard\Contracts\DashboardServiceInterface;
use App\Domain\Period\Models\Period;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Category $inflowCategory;

    private Category $outflowCategory;

    private DashboardServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->account = Account::create([
            'user_uid' => $this->user->uid,
            'name' => 'Main Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 5000,
        ]);
        $this->inflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Salário',
            'direction' => Category::DIRECTION_INFLOW,
        ]);
        $this->outflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Alimentação',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
        $this->service = app()->make(DashboardServiceInterface::class);
    }

    private function createPeriod(int $month = 6, int $year = 2025): Period
    {
        return Period::create([
            'user_uid' => $this->user->uid,
            'month' => $month,
            'year' => $year,
        ]);
    }

    private function createTransaction(Period $period, array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 100.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => Carbon::create($period->year, $period->month, 1),
            'period_uid' => $period->uid,
        ], $overrides));
    }

    // =========================================================================
    // getStatusCountsForPeriod — correct counts per status
    // =========================================================================

    public function test_get_status_counts_returns_correct_counts_per_status(): void
    {
        $period = $this->createPeriod();

        // 2 PENDING
        $this->createTransaction($period, ['status' => Transaction::STATUS_PENDING]);
        $this->createTransaction($period, ['status' => Transaction::STATUS_PENDING]);

        // 3 PAID
        $this->createTransaction($period, ['status' => Transaction::STATUS_PAID]);
        $this->createTransaction($period, ['status' => Transaction::STATUS_PAID]);
        $this->createTransaction($period, ['status' => Transaction::STATUS_PAID]);

        // 1 OVERDUE
        $this->createTransaction($period, ['status' => Transaction::STATUS_OVERDUE]);

        $result = $this->service->getStatusCountsForPeriod($period->uid, $this->user->uid);

        $this->assertSame(2, $result['pending']);
        $this->assertSame(3, $result['paid']);
        $this->assertSame(1, $result['overdue']);
    }

    // =========================================================================
    // getStatusCountsForPeriod — zeroed counts for empty period
    // =========================================================================

    public function test_get_status_counts_returns_zeroed_counts_for_period_with_no_transactions(): void
    {
        $period = $this->createPeriod();

        $result = $this->service->getStatusCountsForPeriod($period->uid, $this->user->uid);

        $this->assertSame(0, $result['pending']);
        $this->assertSame(0, $result['paid']);
        $this->assertSame(0, $result['overdue']);
    }

    // =========================================================================
    // getCategoryBreakdownForPeriod — sorted by total DESC
    // =========================================================================

    public function test_get_category_breakdown_returns_categories_sorted_by_total_desc(): void
    {
        $period = $this->createPeriod();

        $transporteCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Transporte',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
        $saudeCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Saúde',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);

        // Alimentação: 300 + 200 = 500
        $this->createTransaction($period, [
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 300.00,
        ]);
        $this->createTransaction($period, [
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 200.00,
        ]);

        // Transporte: 800
        $this->createTransaction($period, [
            'category_uid' => $transporteCategory->uid,
            'amount' => 800.00,
        ]);

        // Saúde: 150
        $this->createTransaction($period, [
            'category_uid' => $saudeCategory->uid,
            'amount' => 150.00,
        ]);

        $result = $this->service->getCategoryBreakdownForPeriod($period->uid, $this->user->uid);

        $this->assertCount(3, $result);

        // Sorted DESC: Transporte (800), Alimentação (500), Saúde (150)
        $this->assertSame('Transporte', $result[0]['category_name']);
        $this->assertEqualsWithDelta(800.00, $result[0]['total'], 0.01);

        $this->assertSame('Alimentação', $result[1]['category_name']);
        $this->assertEqualsWithDelta(500.00, $result[1]['total'], 0.01);

        $this->assertSame('Saúde', $result[2]['category_name']);
        $this->assertEqualsWithDelta(150.00, $result[2]['total'], 0.01);
    }

    // =========================================================================
    // getCategoryBreakdownForPeriod — empty for no OUTFLOW transactions
    // =========================================================================

    public function test_get_category_breakdown_returns_empty_for_period_with_no_outflow_transactions(): void
    {
        $period = $this->createPeriod();

        // Only INFLOW transactions
        $this->createTransaction($period, [
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 5000.00,
            'direction' => Transaction::DIRECTION_INFLOW,
        ]);

        $result = $this->service->getCategoryBreakdownForPeriod($period->uid, $this->user->uid);

        $this->assertSame([], $result);
    }

    // =========================================================================
    // getCategoryBreakdownForPeriod — only counts OUTFLOW (not INFLOW)
    // =========================================================================

    public function test_get_category_breakdown_only_counts_outflow_transactions(): void
    {
        $period = $this->createPeriod();

        // INFLOW — should NOT appear in breakdown
        $this->createTransaction($period, [
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 5000.00,
            'direction' => Transaction::DIRECTION_INFLOW,
        ]);

        // OUTFLOW — should appear
        $this->createTransaction($period, [
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 300.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
        ]);

        $result = $this->service->getCategoryBreakdownForPeriod($period->uid, $this->user->uid);

        $this->assertCount(1, $result);
        $this->assertSame('Alimentação', $result[0]['category_name']);
        $this->assertEqualsWithDelta(300.00, $result[0]['total'], 0.01);
    }
}
