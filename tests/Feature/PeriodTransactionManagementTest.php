<?php

namespace Tests\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Period\Models\Period;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodTransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Category $category;

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
        $this->category = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Moradia',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
    }

    // ---------------------------------------------------------------
    // 4.1 — Detach scenarios
    // ---------------------------------------------------------------

    public function test_user_can_detach_all_transactions_from_period(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 6,
            'year' => 2025,
        ]);

        $txUids = [];
        for ($i = 0; $i < 3; $i++) {
            $tx = Transaction::create([
                'user_uid' => $this->user->uid,
                'account_uid' => $this->account->uid,
                'category_uid' => $this->category->uid,
                'amount' => 100 + $i,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_MANUAL,
                'occurred_at' => now(),
                'period_uid' => $period->uid,
            ]);
            $txUids[] = $tx->uid;
        }

        $response = $this->actingAs($this->user)
            ->delete("/finance/periods/{$period->uid}/transactions");

        $response->assertRedirect();

        foreach ($txUids as $uid) {
            $this->assertDatabaseHas('financial_transactions', [
                'uid' => $uid,
                'period_uid' => null,
            ]);
        }

        // None deleted
        $this->assertDatabaseCount('financial_transactions', 3);
    }

    public function test_detach_returns_success_message_with_count(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 7,
            'year' => 2025,
        ]);

        for ($i = 0; $i < 5; $i++) {
            Transaction::create([
                'user_uid' => $this->user->uid,
                'account_uid' => $this->account->uid,
                'category_uid' => $this->category->uid,
                'amount' => 50 + $i,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_MANUAL,
                'occurred_at' => now(),
                'period_uid' => $period->uid,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->from("/finance/periods/{$period->uid}")
            ->delete("/finance/periods/{$period->uid}/transactions");

        $response->assertRedirect(route('finance.finance.periods.show', $period->uid));
        $response->assertSessionHas('success', '5 transação(ões) desvinculada(s) do período.');
    }

    public function test_detach_empty_period_returns_success(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 8,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->from("/finance/periods/{$period->uid}")
            ->delete("/finance/periods/{$period->uid}/transactions");

        $response->assertRedirect(route('finance.finance.periods.show', $period->uid));
        $response->assertSessionHas('success', '0 transação(ões) desvinculada(s) do período.');
    }

    public function test_user_cannot_detach_transactions_from_another_users_period(): void
    {
        $otherUser = User::factory()->create();
        $otherAccount = Account::create([
            'user_uid' => $otherUser->uid,
            'name' => 'Other Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 1000,
        ]);
        $otherCategory = Category::create([
            'user_uid' => $otherUser->uid,
            'name' => 'Alimentação',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);

        $otherPeriod = Period::create([
            'user_uid' => $otherUser->uid,
            'month' => 9,
            'year' => 2025,
        ]);

        $tx = Transaction::create([
            'user_uid' => $otherUser->uid,
            'account_uid' => $otherAccount->uid,
            'category_uid' => $otherCategory->uid,
            'amount' => 300,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => now(),
            'period_uid' => $otherPeriod->uid,
        ]);

        // Authenticated as $this->user, trying to detach from another user's period
        $response = $this->actingAs($this->user)
            ->delete("/finance/periods/{$otherPeriod->uid}/transactions");

        $response->assertRedirect();

        // Transaction should still be linked to the period (not detached)
        $this->assertDatabaseHas('financial_transactions', [
            'uid' => $tx->uid,
            'period_uid' => $otherPeriod->uid,
        ]);
    }

    // ---------------------------------------------------------------
    // 4.2 — Store transaction scenarios
    // ---------------------------------------------------------------

    public function test_user_can_create_transaction_linked_to_period(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 3,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/finance/periods/{$period->uid}/transactions", [
                'account_uid' => $this->account->uid,
                'category_uid' => $this->category->uid,
                'amount' => 250.50,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_MANUAL,
                'occurred_at' => '2025-03-01',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('financial_transactions', [
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 250.50,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'period_uid' => $period->uid,
        ]);
    }

    public function test_store_transaction_redirects_to_period_show(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 4,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/finance/periods/{$period->uid}/transactions", [
                'account_uid' => $this->account->uid,
                'category_uid' => $this->category->uid,
                'amount' => 100,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_MANUAL,
                'occurred_at' => '2025-04-01',
            ]);

        $response->assertRedirect(route('finance.finance.periods.show', $period->uid));
        $response->assertSessionHas('success', 'Transação criada com sucesso.');
    }

    public function test_store_transaction_validates_period_uid(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 5,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->from("/finance/periods/{$period->uid}")
            ->post("/finance/periods/{$period->uid}/transactions", [
                'account_uid' => $this->account->uid,
                'category_uid' => $this->category->uid,
                'amount' => 100,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_MANUAL,
                'occurred_at' => '2025-05-01',
                'period_uid' => 'not-a-valid-uuid',
            ]);

        $response->assertRedirect("/finance/periods/{$period->uid}");
        $response->assertSessionHasErrors(['period_uid']);
    }

    public function test_period_show_includes_accounts_and_categories_props(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 1,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/finance/periods/{$period->uid}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('finance/periods/Show')
            ->has('accounts')
            ->has('categories')
        );
    }

    public function test_period_show_includes_account_and_category_in_transactions(): void
    {
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 2,
            'year' => 2025,
        ]);

        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 500,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'occurred_at' => '2025-02-15',
            'period_uid' => $period->uid,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/finance/periods/{$period->uid}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('finance/periods/Show')
            ->has('transactions', 1, fn ($tx) => $tx
                ->has('account')
                ->has('category')
                ->where('account.name', 'Main Account')
                ->where('category.name', 'Moradia')
                ->etc()
            )
        );
    }

    // ---------------------------------------------------------------
    // 4.4 — Property 2: Transaction creation with period_uid links correctly
    // ---------------------------------------------------------------

    /**
     * **Validates: Requirements 5.5**
     *
     * For any valid transaction data that includes a valid period_uid,
     * after creation, the resulting transaction in the database SHALL have
     * period_uid equal to the provided value, and the period's transaction
     * count SHALL increase by one.
     */
    public function test_property_transaction_creation_links_to_period(): void
    {
        $inflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Salário',
            'direction' => Category::DIRECTION_INFLOW,
        ]);

        $iterations = 20;

        for ($i = 0; $i < $iterations; $i++) {
            $period = Period::create([
                'user_uid' => $this->user->uid,
                'month' => (($i % 12) + 1),
                'year' => 2040 + intdiv($i, 12),
            ]);

            $countBefore = Transaction::where('period_uid', $period->uid)->count();
            $this->assertEquals(0, $countBefore, "Iteration {$i}: period should start with 0 transactions");

            $direction = rand(0, 1) ? Transaction::DIRECTION_INFLOW : Transaction::DIRECTION_OUTFLOW;
            $categoryUid = $direction === Transaction::DIRECTION_INFLOW
                ? $inflowCategory->uid
                : $this->category->uid;

            $amount = round(rand(1, 999999) / 100, 2);
            $day = rand(1, 28);
            $occurredAt = sprintf('%04d-%02d-%02d', $period->year, $period->month, $day);

            $response = $this->actingAs($this->user)
                ->post("/finance/periods/{$period->uid}/transactions", [
                    'account_uid' => $this->account->uid,
                    'category_uid' => $categoryUid,
                    'amount' => $amount,
                    'direction' => $direction,
                    'status' => Transaction::STATUS_PENDING,
                    'source' => Transaction::SOURCE_MANUAL,
                    'occurred_at' => $occurredAt,
                ]);

            $response->assertRedirect();

            $countAfter = Transaction::where('period_uid', $period->uid)->count();
            $this->assertEquals(
                $countBefore + 1,
                $countAfter,
                "Iteration {$i}: period transaction count should increase by 1 (direction={$direction}, amount={$amount})"
            );

            $transaction = Transaction::where('period_uid', $period->uid)->latest('created_at')->first();
            $this->assertNotNull($transaction, "Iteration {$i}: transaction should exist");
            $this->assertEquals($period->uid, $transaction->period_uid, "Iteration {$i}: transaction should be linked to the period");
            $this->assertEquals($amount, (float) $transaction->amount, "Iteration {$i}: amount should match");
            $this->assertEquals($direction, $transaction->direction, "Iteration {$i}: direction should match");
        }
    }

    // ---------------------------------------------------------------
    // 4.3 — Property 1: Detach preserves transaction existence
    // ---------------------------------------------------------------

    // ---------------------------------------------------------------
    // 4.5 — Property 3: period_uid validation
    // ---------------------------------------------------------------

    /**
     * **Validates: Requirements 5.8**
     *
     * For any valid UUID v4 string that corresponds to an existing period,
     * the StoreTransactionRequest SHALL accept the period_uid field.
     * For any non-UUID string, the request SHALL reject it with a validation error.
     */
    public function test_property_period_uid_validation(): void
    {
        $iterations = 20;

        // --- Part 1: Valid UUIDs (existing periods) should be accepted ---
        for ($i = 0; $i < $iterations; $i++) {
            $period = Period::create([
                'user_uid' => $this->user->uid,
                'month' => (($i % 12) + 1),
                'year' => 2050 + intdiv($i, 12),
            ]);

            $amount = round(rand(1, 999999) / 100, 2);
            $day = rand(1, 28);
            $occurredAt = sprintf('%04d-%02d-%02d', $period->year, $period->month, $day);

            $response = $this->actingAs($this->user)
                ->post("/finance/periods/{$period->uid}/transactions", [
                    'account_uid' => $this->account->uid,
                    'category_uid' => $this->category->uid,
                    'amount' => $amount,
                    'direction' => Transaction::DIRECTION_OUTFLOW,
                    'status' => Transaction::STATUS_PENDING,
                    'source' => Transaction::SOURCE_MANUAL,
                    'occurred_at' => $occurredAt,
                    'period_uid' => $period->uid,
                ]);

            $response->assertRedirect();
            $response->assertSessionDoesntHaveErrors(['period_uid']);
        }

        // --- Part 2: Invalid strings should be rejected ---
        $period = Period::create([
            'user_uid' => $this->user->uid,
            'month' => 1,
            'year' => 2060,
        ]);

        $invalidStrings = [
            'not-a-uuid',
            '12345',
            'abc',
            '550e8400-e29b-41d4-a716',           // partial UUID
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            '!@#$%^&*()',
            'null',
            str_repeat('a', 100),
            '550e8400-e29b-41d4-a716-44665544000',  // wrong length
            'g50e8400-e29b-41d4-a716-446655440000',  // invalid hex char
        ];

        // Add random words and numbers
        for ($i = 0; $i < 8; $i++) {
            $invalidStrings[] = match (rand(0, 3)) {
                0 => 'word'.rand(1, 99999),
                1 => (string) rand(1, 999999),
                2 => substr(md5((string) rand()), 0, rand(5, 20)),
                3 => str_repeat(chr(rand(65, 90)), rand(1, 10)),
            };
        }

        foreach ($invalidStrings as $index => $invalidValue) {
            $response = $this->actingAs($this->user)
                ->from("/finance/periods/{$period->uid}")
                ->post("/finance/periods/{$period->uid}/transactions", [
                    'account_uid' => $this->account->uid,
                    'category_uid' => $this->category->uid,
                    'amount' => 100,
                    'direction' => Transaction::DIRECTION_OUTFLOW,
                    'status' => Transaction::STATUS_PENDING,
                    'source' => Transaction::SOURCE_MANUAL,
                    'occurred_at' => '2060-01-15',
                    'period_uid' => $invalidValue,
                ]);

            $response->assertRedirect("/finance/periods/{$period->uid}");
            $response->assertSessionHasErrors(
                ['period_uid'],
                '',
                'default',
            );

            // Flush session to avoid stale errors in next iteration
            session()->flush();
        }
    }

    // ---------------------------------------------------------------
    // 4.3 — Property 1: Detach preserves transaction existence
    // ---------------------------------------------------------------

    /**
     * **Validates: Requirements 4.3**
     *
     * For any period with N linked transactions (N >= 0), after calling
     * detachAllTransactions, all N transactions SHALL still exist in the
     * database with period_uid = null, and the total count of transactions
     * in the system SHALL remain unchanged.
     */
    public function test_property_detach_preserves_transaction_existence(): void
    {
        $iterations = 20;

        for ($i = 0; $i < $iterations; $i++) {
            $n = rand(0, 50);

            $period = Period::create([
                'user_uid' => $this->user->uid,
                'month' => (($i % 12) + 1),
                'year' => 2030 + intdiv($i, 12),
            ]);

            $txUids = [];
            for ($j = 0; $j < $n; $j++) {
                $tx = Transaction::create([
                    'user_uid' => $this->user->uid,
                    'account_uid' => $this->account->uid,
                    'category_uid' => $this->category->uid,
                    'amount' => round(rand(1, 100000) / 100, 2),
                    'direction' => rand(0, 1) ? Transaction::DIRECTION_INFLOW : Transaction::DIRECTION_OUTFLOW,
                    'status' => Transaction::STATUS_PENDING,
                    'source' => Transaction::SOURCE_MANUAL,
                    'occurred_at' => now(),
                    'period_uid' => $period->uid,
                ]);
                $txUids[] = $tx->uid;
            }

            $totalBefore = Transaction::count();

            $response = $this->actingAs($this->user)
                ->delete("/finance/periods/{$period->uid}/transactions");

            $response->assertRedirect();

            // All transactions still exist with period_uid = null
            foreach ($txUids as $uid) {
                $this->assertDatabaseHas('financial_transactions', [
                    'uid' => $uid,
                    'period_uid' => null,
                ]);
            }

            // Total count unchanged — no transactions were deleted
            $this->assertEquals(
                $totalBefore,
                Transaction::count(),
                "Iteration {$i} (N={$n}): total transaction count changed after detach"
            );
        }
    }
}
