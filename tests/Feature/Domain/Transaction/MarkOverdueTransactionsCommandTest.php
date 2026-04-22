<?php

namespace Tests\Feature\Domain\Transaction;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MarkOverdueTransactionsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function createTransaction(array $overrides = []): Transaction
    {
        $user = User::factory()->create();

        $account = Account::create([
            'user_uid' => $user->uid,
            'name' => 'Test Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 1000,
        ]);

        $category = Category::create([
            'user_uid' => $user->uid,
            'name' => 'Test Category',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);

        return Transaction::create(array_merge([
            'user_uid' => $user->uid,
            'account_uid' => $account->uid,
            'category_uid' => $category->uid,
            'amount' => 100.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => now(),
            'due_date' => now()->subDay(),
        ], $overrides));
    }

    public function test_marks_pending_transactions_with_past_due_date_as_overdue(): void
    {
        $transaction = $this->createTransaction([
            'status' => Transaction::STATUS_PENDING,
            'due_date' => now()->subDays(3),
        ]);

        $this->artisan('transactions:mark-overdue')
            ->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'uid' => $transaction->uid,
            'status' => Transaction::STATUS_OVERDUE,
        ]);
    }

    public function test_does_not_mark_pending_transactions_with_future_due_date(): void
    {
        $transaction = $this->createTransaction([
            'status' => Transaction::STATUS_PENDING,
            'due_date' => now()->addDays(3),
        ]);

        $this->artisan('transactions:mark-overdue')
            ->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'uid' => $transaction->uid,
            'status' => Transaction::STATUS_PENDING,
        ]);
    }

    public function test_does_not_mark_paid_transactions_as_overdue(): void
    {
        $transaction = $this->createTransaction([
            'status' => Transaction::STATUS_PAID,
            'due_date' => now()->subDays(3),
        ]);

        $this->artisan('transactions:mark-overdue')
            ->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'uid' => $transaction->uid,
            'status' => Transaction::STATUS_PAID,
        ]);
    }

    public function test_logs_count_of_updated_transactions(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn (string $message) => str_contains($message, '2'));

        $this->createTransaction([
            'status' => Transaction::STATUS_PENDING,
            'due_date' => now()->subDay(),
        ]);

        $this->createTransaction([
            'status' => Transaction::STATUS_PENDING,
            'due_date' => now()->subDays(2),
        ]);

        $this->createTransaction([
            'status' => Transaction::STATUS_PENDING,
            'due_date' => now()->addDay(),
        ]);

        $this->artisan('transactions:mark-overdue')
            ->assertSuccessful();
    }

    public function test_does_not_mark_pending_transactions_with_today_due_date(): void
    {
        $transaction = $this->createTransaction([
            'status' => Transaction::STATUS_PENDING,
            'due_date' => now()->startOfDay(),
        ]);

        $this->artisan('transactions:mark-overdue')
            ->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'uid' => $transaction->uid,
            'status' => Transaction::STATUS_PENDING,
        ]);
    }
}
