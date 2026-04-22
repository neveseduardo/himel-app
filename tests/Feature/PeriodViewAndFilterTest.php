<?php

namespace Tests\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Period\Models\Period;
use App\Domain\Period\Services\PeriodService;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PeriodViewAndFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Category $inflowCategory;

    private Category $outflowCategory;

    private Period $period;

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
            'name' => 'Moradia',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
        $this->period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 6,
            'year' => 2025,
        ]);
    }

    private function createTransaction(array $overrides = []): Transaction
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
            'period_uid' => $this->period->uid,
        ], $overrides));
    }

    // ---------------------------------------------------------------
    // 18.1 — Test show page with correct financial summary
    // ---------------------------------------------------------------

    public function test_show_page_returns_period_and_transactions(): void
    {
        $this->createTransaction(['amount' => 500.00, 'direction' => Transaction::DIRECTION_OUTFLOW]);
        $this->createTransaction(['amount' => 1000.00, 'direction' => Transaction::DIRECTION_INFLOW, 'category_uid' => $this->inflowCategory->uid]);

        $response = $this->actingAs($this->user)
            ->get("/periods/{$this->period->uid}");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('periods/Show')
                ->has('period')
                ->has('transactions')
            );
    }

    public function test_show_page_summary_calculates_totals_correctly(): void
    {
        $this->createTransaction(['amount' => 3000.00, 'direction' => Transaction::DIRECTION_INFLOW, 'category_uid' => $this->inflowCategory->uid]);
        $this->createTransaction(['amount' => 500.00, 'direction' => Transaction::DIRECTION_INFLOW, 'category_uid' => $this->inflowCategory->uid]);
        $this->createTransaction(['amount' => 1200.00, 'direction' => Transaction::DIRECTION_OUTFLOW]);
        $this->createTransaction(['amount' => 300.00, 'direction' => Transaction::DIRECTION_OUTFLOW]);

        /** @var PeriodService $service */
        $service = app(PeriodService::class);
        $result = $service->getByUidWithSummary($this->period->uid, $this->user->uid);

        $this->assertNotNull($result);
        $this->assertEquals(3500.00, $result['total_inflow']);
        $this->assertEquals(1500.00, $result['total_outflow']);
        $this->assertEquals(2000.00, $result['balance']);
    }

    public function test_show_page_summary_is_zero_when_no_transactions(): void
    {
        /** @var PeriodService $service */
        $service = app(PeriodService::class);
        $result = $service->getByUidWithSummary($this->period->uid, $this->user->uid);

        $this->assertNotNull($result);
        $this->assertEquals(0.0, $result['total_inflow']);
        $this->assertEquals(0.0, $result['total_outflow']);
        $this->assertEquals(0.0, $result['balance']);
    }

    public function test_show_page_returns_404_for_nonexistent_period(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/periods/nonexistent-uid');

        $response->assertStatus(404);
    }

    public function test_show_page_returns_404_for_other_users_period(): void
    {
        $otherUser = User::factory()->create();
        $otherPeriod = Period::create([
            'user_uid' => $otherUser->uid,
            'month' => 6,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/periods/{$otherPeriod->uid}");

        $response->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // 18.2 — Test transaction filtering by period, status, direction, and source
    // ---------------------------------------------------------------

    public function test_filters_transactions_by_status(): void
    {
        $this->createTransaction(['status' => Transaction::STATUS_PENDING]);
        $this->createTransaction(['status' => Transaction::STATUS_PAID, 'paid_at' => now()]);
        $this->createTransaction(['status' => Transaction::STATUS_OVERDUE]);

        /** @var PeriodService $service */
        $service = app(PeriodService::class);

        $pending = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid, ['status' => Transaction::STATUS_PENDING]);
        $this->assertCount(1, $pending['data']);

        $paid = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid, ['status' => Transaction::STATUS_PAID]);
        $this->assertCount(1, $paid['data']);

        $overdue = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid, ['status' => Transaction::STATUS_OVERDUE]);
        $this->assertCount(1, $overdue['data']);
    }

    public function test_filters_transactions_by_direction(): void
    {
        $this->createTransaction(['direction' => Transaction::DIRECTION_INFLOW, 'category_uid' => $this->inflowCategory->uid]);
        $this->createTransaction(['direction' => Transaction::DIRECTION_INFLOW, 'category_uid' => $this->inflowCategory->uid]);
        $this->createTransaction(['direction' => Transaction::DIRECTION_OUTFLOW]);

        /** @var PeriodService $service */
        $service = app(PeriodService::class);

        $inflow = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid, ['direction' => Transaction::DIRECTION_INFLOW]);
        $this->assertCount(2, $inflow['data']);

        $outflow = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid, ['direction' => Transaction::DIRECTION_OUTFLOW]);
        $this->assertCount(1, $outflow['data']);
    }

    public function test_filters_transactions_by_source(): void
    {
        $this->createTransaction(['source' => Transaction::SOURCE_MANUAL]);
        $this->createTransaction(['source' => Transaction::SOURCE_FIXED]);
        $this->createTransaction(['source' => Transaction::SOURCE_CREDIT_CARD]);

        /** @var PeriodService $service */
        $service = app(PeriodService::class);

        $manual = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid, ['source' => Transaction::SOURCE_MANUAL]);
        $this->assertCount(1, $manual['data']);

        $fixed = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid, ['source' => Transaction::SOURCE_FIXED]);
        $this->assertCount(1, $fixed['data']);

        $creditCard = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid, ['source' => Transaction::SOURCE_CREDIT_CARD]);
        $this->assertCount(1, $creditCard['data']);
    }

    public function test_filters_transactions_by_period_only(): void
    {
        $otherPeriod = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 7,
            'year' => 2025,
        ]);

        $this->createTransaction(['period_uid' => $this->period->uid]);
        $this->createTransaction(['period_uid' => $this->period->uid]);
        $this->createTransaction(['period_uid' => $otherPeriod->uid]);
        $this->createTransaction(['period_uid' => null]);

        /** @var PeriodService $service */
        $service = app(PeriodService::class);

        $result = $service->getTransactionsForPeriod($this->period->uid, $this->user->uid);
        $this->assertCount(2, $result['data']);

        $otherResult = $service->getTransactionsForPeriod($otherPeriod->uid, $this->user->uid);
        $this->assertCount(1, $otherResult['data']);
    }

    public function test_show_page_accepts_filter_query_params(): void
    {
        $this->createTransaction(['status' => Transaction::STATUS_PENDING, 'source' => Transaction::SOURCE_FIXED]);
        $this->createTransaction(['status' => Transaction::STATUS_PAID, 'paid_at' => now(), 'source' => Transaction::SOURCE_MANUAL]);

        $response = $this->actingAs($this->user)
            ->get("/periods/{$this->period->uid}?status=PENDING");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('periods/Show')
            );
    }

    // ---------------------------------------------------------------
    // 18.3 — Test Transaction→Period and Period→Transactions relationships
    // ---------------------------------------------------------------

    public function test_transaction_belongs_to_period(): void
    {
        $transaction = $this->createTransaction();

        $this->assertInstanceOf(Period::class, $transaction->period);
        $this->assertEquals($this->period->uid, $transaction->period->uid);
    }

    public function test_period_has_many_transactions(): void
    {
        $this->createTransaction();
        $this->createTransaction();
        $this->createTransaction();

        $this->period->refresh();

        $this->assertCount(3, $this->period->transactions);
        $this->assertInstanceOf(Transaction::class, $this->period->transactions->first());
    }

    public function test_transaction_without_period_returns_null(): void
    {
        $transaction = $this->createTransaction(['period_uid' => null]);

        $this->assertNull($transaction->period);
    }

    public function test_period_without_transactions_returns_empty_collection(): void
    {
        $emptyPeriod = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 8,
            'year' => 2025,
        ]);

        $this->assertCount(0, $emptyPeriod->transactions);
    }

    public function test_period_transactions_only_includes_linked_transactions(): void
    {
        $otherPeriod = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 7,
            'year' => 2025,
        ]);

        $linked = $this->createTransaction(['period_uid' => $this->period->uid]);
        $this->createTransaction(['period_uid' => $otherPeriod->uid]);
        $this->createTransaction(['period_uid' => null]);

        $this->period->refresh();

        $this->assertCount(1, $this->period->transactions);
        $this->assertEquals($linked->uid, $this->period->transactions->first()->uid);
    }
}
