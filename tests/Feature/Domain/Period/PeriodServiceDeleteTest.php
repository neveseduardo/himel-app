<?php

namespace Tests\Feature\Domain\Period;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Period\Exceptions\PeriodHasPaidTransactionsException;
use App\Domain\Period\Models\Period;
use App\Domain\Period\Services\PeriodService;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodServiceDeleteTest extends TestCase
{
    use RefreshDatabase;

    private PeriodService $service;

    private User $user;

    private Account $account;

    private Category $category;

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
        $this->category = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Moradia',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
    }

    private function createPeriod(int $month = 6, int $year = 2025): Period
    {
        return $this->service->create($this->user->uid, $month, $year);
    }

    private function createTransaction(Period $period, string $status): Transaction
    {
        return Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 100.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => $status,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => now(),
            'period_uid' => $period->uid,
        ]);
    }

    public function test_deletes_period_without_transactions(): void
    {
        $period = $this->createPeriod();

        $result = $this->service->delete($period->uid, $this->user->uid);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('periods', ['uid' => $period->uid]);
    }

    public function test_returns_false_for_nonexistent_period(): void
    {
        $result = $this->service->delete('nonexistent-uid', $this->user->uid);

        $this->assertFalse($result);
    }

    public function test_throws_exception_when_period_has_paid_transactions(): void
    {
        $period = $this->createPeriod();
        $this->createTransaction($period, Transaction::STATUS_PAID);

        $this->expectException(PeriodHasPaidTransactionsException::class);

        $this->service->delete($period->uid, $this->user->uid);
    }

    public function test_period_not_deleted_when_paid_transactions_exist(): void
    {
        $period = $this->createPeriod();
        $transaction = $this->createTransaction($period, Transaction::STATUS_PAID);

        try {
            $this->service->delete($period->uid, $this->user->uid);
        } catch (PeriodHasPaidTransactionsException) {
            // expected
        }

        $this->assertDatabaseHas('periods', ['uid' => $period->uid]);
        $this->assertDatabaseHas('transactions', [
            'uid' => $transaction->uid,
            'period_uid' => $period->uid,
        ]);
    }

    public function test_unlinks_pending_transactions_before_deletion(): void
    {
        $period = $this->createPeriod();
        $transaction = $this->createTransaction($period, Transaction::STATUS_PENDING);

        $result = $this->service->delete($period->uid, $this->user->uid);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('periods', ['uid' => $period->uid]);
        $this->assertDatabaseHas('transactions', [
            'uid' => $transaction->uid,
            'period_uid' => null,
        ]);
    }

    public function test_unlinks_overdue_transactions_before_deletion(): void
    {
        $period = $this->createPeriod();
        $transaction = $this->createTransaction($period, Transaction::STATUS_OVERDUE);

        $result = $this->service->delete($period->uid, $this->user->uid);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('periods', ['uid' => $period->uid]);
        $this->assertDatabaseHas('transactions', [
            'uid' => $transaction->uid,
            'period_uid' => null,
        ]);
    }

    public function test_unlinks_mixed_pending_and_overdue_transactions(): void
    {
        $period = $this->createPeriod();
        $pending = $this->createTransaction($period, Transaction::STATUS_PENDING);
        $overdue = $this->createTransaction($period, Transaction::STATUS_OVERDUE);

        $result = $this->service->delete($period->uid, $this->user->uid);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('periods', ['uid' => $period->uid]);
        $this->assertDatabaseHas('transactions', [
            'uid' => $pending->uid,
            'period_uid' => null,
        ]);
        $this->assertDatabaseHas('transactions', [
            'uid' => $overdue->uid,
            'period_uid' => null,
        ]);
    }

    public function test_rejects_deletion_when_mix_of_paid_and_pending_exist(): void
    {
        $period = $this->createPeriod();
        $this->createTransaction($period, Transaction::STATUS_PENDING);
        $this->createTransaction($period, Transaction::STATUS_PAID);

        $this->expectException(PeriodHasPaidTransactionsException::class);

        $this->service->delete($period->uid, $this->user->uid);
    }

    public function test_does_not_affect_transactions_from_other_periods(): void
    {
        $period1 = $this->createPeriod(6, 2025);
        $period2 = $this->createPeriod(7, 2025);

        $this->createTransaction($period1, Transaction::STATUS_PENDING);
        $otherTransaction = $this->createTransaction($period2, Transaction::STATUS_PENDING);

        $this->service->delete($period1->uid, $this->user->uid);

        $this->assertDatabaseHas('transactions', [
            'uid' => $otherTransaction->uid,
            'period_uid' => $period2->uid,
        ]);
    }
}
