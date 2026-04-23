<?php

namespace Tests\Feature\Domain\Period;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Period\Models\Period;
use App\Domain\Period\Services\PeriodService;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodServiceQueryTest extends TestCase
{
    use RefreshDatabase;

    private PeriodService $service;

    private User $user;

    private Account $account;

    private Category $inflowCategory;

    private Category $outflowCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PeriodService;
        $this->user = User::factory()->create();
        $this->account = Account::create([
            'user_uid' => $this->user->uid,
            'name' => 'Main Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 5000,
        ]);
        $this->inflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Salary',
            'direction' => Category::DIRECTION_INFLOW,
        ]);
        $this->outflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Bills',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
    }

    private function createPeriod(int $month = 6, int $year = 2025): Period
    {
        return Period::create([
            'user_uid' => $this->user->uid,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
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
            'occurred_at' => now(),
            'period_uid' => $period->uid,
        ], $overrides));
    }

    // ── getByUidWithSummary ──────────────────────────────────────────

    public function test_get_by_uid_with_summary_returns_null_for_nonexistent_period(): void
    {
        $result = $this->service->getByUidWithSummary('nonexistent-uid', $this->user->uid);

        $this->assertNull($result);
    }

    public function test_get_by_uid_with_summary_returns_null_for_other_user_period(): void
    {
        $otherUser = User::factory()->create();
        $period = Period::create([
            'user_uid' => $otherUser->uid,
            'month' => 6,
            'year' => 2025,
        ]);

        $result = $this->service->getByUidWithSummary($period->uid, $this->user->uid);

        $this->assertNull($result);
    }

    public function test_get_by_uid_with_summary_returns_zeros_for_empty_period(): void
    {
        $period = $this->createPeriod();

        $result = $this->service->getByUidWithSummary($period->uid, $this->user->uid);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Period::class, $result['period']);
        $this->assertEquals($period->uid, $result['period']->uid);
        $this->assertEquals(0.0, $result['total_inflow']);
        $this->assertEquals(0.0, $result['total_outflow']);
        $this->assertEquals(0.0, $result['balance']);
    }

    public function test_get_by_uid_with_summary_calculates_inflow_total(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, [
            'direction' => Transaction::DIRECTION_INFLOW,
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 500.50,
        ]);
        $this->createTransaction($period, [
            'direction' => Transaction::DIRECTION_INFLOW,
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 300.25,
        ]);

        $result = $this->service->getByUidWithSummary($period->uid, $this->user->uid);

        $this->assertEquals(800.75, $result['total_inflow']);
        $this->assertEquals(0.0, $result['total_outflow']);
        $this->assertEquals(800.75, $result['balance']);
    }

    public function test_get_by_uid_with_summary_calculates_outflow_total(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, ['amount' => 200.00]);
        $this->createTransaction($period, ['amount' => 150.75]);

        $result = $this->service->getByUidWithSummary($period->uid, $this->user->uid);

        $this->assertEquals(0.0, $result['total_inflow']);
        $this->assertEquals(350.75, $result['total_outflow']);
        $this->assertEquals(-350.75, $result['balance']);
    }

    public function test_get_by_uid_with_summary_calculates_balance_correctly(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, [
            'direction' => Transaction::DIRECTION_INFLOW,
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 1000.00,
        ]);
        $this->createTransaction($period, ['amount' => 350.00]);
        $this->createTransaction($period, ['amount' => 250.00]);

        $result = $this->service->getByUidWithSummary($period->uid, $this->user->uid);

        $this->assertEquals(1000.00, $result['total_inflow']);
        $this->assertEquals(600.00, $result['total_outflow']);
        $this->assertEquals(400.00, $result['balance']);
    }

    public function test_get_by_uid_with_summary_excludes_transactions_from_other_periods(): void
    {
        $period1 = $this->createPeriod(5, 2025);
        $period2 = $this->createPeriod(6, 2025);

        $this->createTransaction($period1, [
            'direction' => Transaction::DIRECTION_INFLOW,
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 999.00,
        ]);
        $this->createTransaction($period2, [
            'direction' => Transaction::DIRECTION_INFLOW,
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 100.00,
        ]);

        $result = $this->service->getByUidWithSummary($period2->uid, $this->user->uid);

        $this->assertEquals(100.00, $result['total_inflow']);
        $this->assertEquals(0.0, $result['total_outflow']);
        $this->assertEquals(100.00, $result['balance']);
    }

    // ── getTransactionsForPeriod ─────────────────────────────────────

    public function test_get_transactions_for_period_returns_transactions(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, ['amount' => 100.00]);
        $this->createTransaction($period, ['amount' => 200.00]);

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(2, $result);
    }

    public function test_get_transactions_for_period_returns_empty_for_no_transactions(): void
    {
        $period = $this->createPeriod();

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(0, $result);
    }

    public function test_get_transactions_for_period_returns_all_statuses(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, ['status' => Transaction::STATUS_PENDING]);
        $this->createTransaction($period, ['status' => Transaction::STATUS_PAID, 'paid_at' => now()]);
        $this->createTransaction($period, ['status' => Transaction::STATUS_OVERDUE]);

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(3, $result);
    }

    public function test_get_transactions_for_period_returns_all_directions(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, [
            'direction' => Transaction::DIRECTION_INFLOW,
            'category_uid' => $this->inflowCategory->uid,
        ]);
        $this->createTransaction($period, [
            'direction' => Transaction::DIRECTION_OUTFLOW,
        ]);

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(2, $result);
    }

    public function test_get_transactions_for_period_returns_all_sources(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, ['source' => Transaction::SOURCE_MANUAL]);
        $this->createTransaction($period, ['source' => Transaction::SOURCE_FIXED]);
        $this->createTransaction($period, ['source' => Transaction::SOURCE_CREDIT_CARD]);

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(3, $result);
    }

    public function test_get_transactions_for_period_returns_all_transactions(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, [
            'status' => Transaction::STATUS_PENDING,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'source' => Transaction::SOURCE_FIXED,
        ]);
        $this->createTransaction($period, [
            'status' => Transaction::STATUS_PAID,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'source' => Transaction::SOURCE_FIXED,
            'paid_at' => now(),
        ]);
        $this->createTransaction($period, [
            'status' => Transaction::STATUS_PENDING,
            'direction' => Transaction::DIRECTION_INFLOW,
            'category_uid' => $this->inflowCategory->uid,
            'source' => Transaction::SOURCE_MANUAL,
        ]);

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(3, $result);
    }

    public function test_get_transactions_for_period_returns_all_without_pagination(): void
    {
        $period = $this->createPeriod();

        for ($i = 0; $i < 5; $i++) {
            $this->createTransaction($period, ['amount' => ($i + 1) * 100]);
        }

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(5, $result);
    }

    public function test_get_transactions_for_period_excludes_other_user_transactions(): void
    {
        $period = $this->createPeriod();
        $otherUser = User::factory()->create();
        $otherAccount = Account::create([
            'user_uid' => $otherUser->uid,
            'name' => 'Other Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 0,
        ]);

        $this->createTransaction($period, ['amount' => 100.00]);

        // Create a transaction from another user but linked to same period
        Transaction::create([
            'user_uid' => $otherUser->uid,
            'account_uid' => $otherAccount->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 999.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => now(),
            'period_uid' => $period->uid,
        ]);

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(1, $result);
        $this->assertEquals(100.00, $result[0]->amount);
    }

    public function test_get_transactions_for_period_returns_all(): void
    {
        $period = $this->createPeriod();

        $this->createTransaction($period, ['amount' => 100.00]);

        $result = $this->service->getTransactionsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(1, $result);
    }
}
