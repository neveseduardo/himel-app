<?php

namespace Tests\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Category $outflowCategory;

    private Category $inflowCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->account = Account::create([
            'user_uid' => $this->user->uid,
            'name' => 'Test Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 5000,
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
    }

    // ---------------------------------------------------------------
    // 8.1 — StoreTransactionRequest validation
    // ---------------------------------------------------------------

    public function test_outflow_requires_category_uid(): void
    {
        $response = $this->actingAs($this->user)
            ->from('/transactions')
            ->post('/transactions', [
                'account_uid' => $this->account->uid,
                'amount' => 100,
                'direction' => 'OUTFLOW',
                'status' => 'PAID',
                'source' => 'MANUAL',
                'occurred_at' => '2025-01-15',
            ]);

        $response->assertSessionHasErrors(['category_uid']);
    }

    public function test_outflow_requires_status(): void
    {
        $response = $this->actingAs($this->user)
            ->from('/transactions')
            ->post('/transactions', [
                'account_uid' => $this->account->uid,
                'category_uid' => $this->outflowCategory->uid,
                'amount' => 100,
                'direction' => 'OUTFLOW',
                'source' => 'MANUAL',
                'occurred_at' => '2025-01-15',
            ]);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_inflow_accepts_minimal_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/transactions', [
                'account_uid' => $this->account->uid,
                'amount' => 500,
                'direction' => 'INFLOW',
                'occurred_at' => '2025-01-15',
            ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('transactions.index'));
    }

    public function test_inflow_defaults_status_to_paid(): void
    {
        $this->actingAs($this->user)
            ->post('/transactions', [
                'account_uid' => $this->account->uid,
                'amount' => 300,
                'direction' => 'INFLOW',
                'occurred_at' => '2025-01-15',
            ]);

        $transaction = Transaction::where('user_uid', $this->user->uid)
            ->latest('created_at')->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('PAID', $transaction->status);
    }

    public function test_inflow_defaults_source_to_manual(): void
    {
        $this->actingAs($this->user)
            ->post('/transactions', [
                'account_uid' => $this->account->uid,
                'amount' => 300,
                'direction' => 'INFLOW',
                'occurred_at' => '2025-01-15',
            ]);

        $transaction = Transaction::where('user_uid', $this->user->uid)
            ->latest('created_at')->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('MANUAL', $transaction->source);
    }

    public function test_inflow_accepts_optional_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/transactions', [
                'account_uid' => $this->account->uid,
                'amount' => 500,
                'direction' => 'INFLOW',
                'occurred_at' => '2025-01-15',
                'category_uid' => $this->inflowCategory->uid,
                'status' => 'PAID',
                'source' => 'MANUAL',
                'description' => 'Salary payment',
            ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('transactions.index'));
    }

    // ---------------------------------------------------------------
    // 8.2 — UpdateTransactionRequest validation
    // ---------------------------------------------------------------

    public function test_update_inflow_applies_conditional_rules(): void
    {
        $transaction = Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'amount' => 500,
            'direction' => Transaction::DIRECTION_INFLOW,
            'status' => Transaction::STATUS_PAID,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->put("/transactions/{$transaction->uid}", [
                'amount' => 600,
                'direction' => 'INFLOW',
                'occurred_at' => '2025-02-01',
            ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('transactions.index'));
    }

    public function test_update_outflow_requires_category_uid(): void
    {
        $transaction = Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 200,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PAID,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->from('/transactions')
            ->put("/transactions/{$transaction->uid}", [
                'amount' => 250,
                'direction' => 'OUTFLOW',
                'occurred_at' => '2025-02-01',
                'status' => 'PAID',
                'source' => 'MANUAL',
            ]);

        $response->assertSessionHasErrors(['category_uid']);
    }
}
