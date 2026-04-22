<?php

namespace Tests\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Exceptions\InsufficientBalanceException;
use App\Domain\Transaction\Services\TransactionService;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionBalanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Category $outflowCategory;

    private Category $inflowCategory;

    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->account = Account::create([
            'user_uid' => $this->user->uid,
            'name' => 'Main Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 1000,
        ]);
        $this->outflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Alimentação',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
        $this->inflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Salário',
            'direction' => Category::DIRECTION_INFLOW,
        ]);
        $this->service = app(TransactionService::class);
    }

    // ---------------------------------------------------------------
    // 9.1 — TransactionService.create() balance tests
    // ---------------------------------------------------------------

    public function test_inflow_credits_account_balance_immediately(): void
    {
        $balanceBefore = (float) $this->account->balance;

        $this->service->create([
            'account_uid' => $this->account->uid,
            'amount' => 500,
            'direction' => 'INFLOW',
            'status' => 'PAID',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $this->assertEquals(
            $balanceBefore + 500,
            (float) $this->account->fresh()->balance
        );
    }

    public function test_outflow_paid_debits_account_balance(): void
    {
        $balanceBefore = (float) $this->account->balance;

        $this->service->create([
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 200,
            'direction' => 'OUTFLOW',
            'status' => 'PAID',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $this->assertEquals(
            $balanceBefore - 200,
            (float) $this->account->fresh()->balance
        );
    }

    public function test_outflow_pending_does_not_affect_balance(): void
    {
        $balanceBefore = (float) $this->account->balance;

        $this->service->create([
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 300,
            'direction' => 'OUTFLOW',
            'status' => 'PENDING',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $this->assertEquals(
            $balanceBefore,
            (float) $this->account->fresh()->balance
        );
    }

    public function test_insufficient_balance_throws_exception(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $this->service->create([
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 5000,
            'direction' => 'OUTFLOW',
            'status' => 'PAID',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);
    }

    public function test_account_ownership_validation(): void
    {
        $otherUser = User::factory()->create();
        $otherAccount = Account::create([
            'user_uid' => $otherUser->uid,
            'name' => 'Other Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 2000,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->create([
            'account_uid' => $otherAccount->uid,
            'amount' => 100,
            'direction' => 'INFLOW',
            'status' => 'PAID',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);
    }

    // ---------------------------------------------------------------
    // 9.2 — TransactionService.update() balance tests
    // ---------------------------------------------------------------

    public function test_inflow_update_adjusts_balance_by_difference(): void
    {
        $transaction = $this->service->create([
            'account_uid' => $this->account->uid,
            'amount' => 300,
            'direction' => 'INFLOW',
            'status' => 'PAID',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $balanceAfterCreate = (float) $this->account->fresh()->balance;

        $this->service->update($transaction->uid, [
            'amount' => 500,
        ], $this->user->uid);

        $this->assertEquals(
            $balanceAfterCreate + 200,
            (float) $this->account->fresh()->balance
        );
    }

    public function test_outflow_pending_to_paid_debits_balance(): void
    {
        $transaction = $this->service->create([
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 200,
            'direction' => 'OUTFLOW',
            'status' => 'PENDING',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $balanceBeforeUpdate = (float) $this->account->fresh()->balance;

        $this->service->update($transaction->uid, [
            'status' => 'PAID',
        ], $this->user->uid);

        $this->assertEquals(
            $balanceBeforeUpdate - 200,
            (float) $this->account->fresh()->balance
        );
    }

    public function test_outflow_paid_to_pending_credits_balance(): void
    {
        $transaction = $this->service->create([
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 200,
            'direction' => 'OUTFLOW',
            'status' => 'PAID',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $balanceAfterPaid = (float) $this->account->fresh()->balance;

        $this->service->update($transaction->uid, [
            'status' => 'PENDING',
        ], $this->user->uid);

        $this->assertEquals(
            $balanceAfterPaid + 200,
            (float) $this->account->fresh()->balance
        );
    }

    public function test_insufficient_balance_on_pending_to_paid(): void
    {
        // Create a PENDING outflow that exceeds current balance
        $transaction = $this->service->create([
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 5000,
            'direction' => 'OUTFLOW',
            'status' => 'PENDING',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $this->expectException(InsufficientBalanceException::class);

        $this->service->update($transaction->uid, [
            'status' => 'PAID',
        ], $this->user->uid);
    }

    // ---------------------------------------------------------------
    // 9.3 — TransactionService.delete() balance tests
    // ---------------------------------------------------------------

    public function test_inflow_delete_reverses_balance(): void
    {
        $balanceBefore = (float) $this->account->balance;

        $transaction = $this->service->create([
            'account_uid' => $this->account->uid,
            'amount' => 400,
            'direction' => 'INFLOW',
            'status' => 'PAID',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $this->service->delete($transaction->uid, $this->user->uid);

        $this->assertEquals(
            $balanceBefore,
            (float) $this->account->fresh()->balance
        );
    }

    public function test_outflow_paid_delete_reverses_balance(): void
    {
        $balanceBefore = (float) $this->account->balance;

        $transaction = $this->service->create([
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 300,
            'direction' => 'OUTFLOW',
            'status' => 'PAID',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $this->service->delete($transaction->uid, $this->user->uid);

        $this->assertEquals(
            $balanceBefore,
            (float) $this->account->fresh()->balance
        );
    }

    public function test_outflow_pending_delete_does_not_affect_balance(): void
    {
        $balanceBefore = (float) $this->account->balance;

        $transaction = $this->service->create([
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 300,
            'direction' => 'OUTFLOW',
            'status' => 'PENDING',
            'source' => 'MANUAL',
            'occurred_at' => '2025-01-15',
        ], $this->user->uid);

        $this->service->delete($transaction->uid, $this->user->uid);

        $this->assertEquals(
            $balanceBefore,
            (float) $this->account->fresh()->balance
        );
    }
}
