<?php

namespace Tests\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\Period\Models\Period;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PeriodInitializationTest extends TestCase
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

    private function createPeriod(int $month, int $year): Period
    {
        return Period::create([
            'user_uid' => $this->user->uid,
            'month' => $month,
            'year' => $year,
        ]);
    }

    private function createFixedExpense(array $overrides = []): FixedExpense
    {
        $uid = Str::uuid()->toString();
        $data = array_merge([
            'uid' => $uid,
            'user_uid' => $this->user->uid,
            'financial_category_uid' => $this->category->uid,
            'name' => 'Aluguel',
            'amount' => 1500.00,
            'due_day' => 10,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        DB::table('financial_fixed_expenses')->insert($data);

        return FixedExpense::find($uid);
    }

    private function createInstallment(array $overrides = []): CreditCardInstallment
    {
        $card = CreditCard::create([
            'user_uid' => $this->user->uid,
            'name' => 'Nubank',
            'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
            'due_day' => 15,
        ]);

        $charge = CreditCardCharge::create([
            'credit_card_uid' => $card->uid,
            'amount' => 600.00,
            'description' => 'Compra parcelada',
            'total_installments' => 3,
        ]);

        return CreditCardInstallment::create(array_merge([
            'credit_card_charge_uid' => $charge->uid,
            'installment_number' => 1,
            'due_date' => Carbon::create(2025, 6, 15),
            'amount' => 200.00,
            'paid_at' => null,
        ], $overrides));
    }

    // 17.1 — Test initialization with active fixed expenses (correct transaction creation)

    public function test_initialization_creates_transactions_from_active_fixed_expenses(): void
    {
        $period = $this->createPeriod(6, 2025);
        $expense1 = $this->createFixedExpense(['name' => 'Aluguel', 'amount' => 1500.00, 'due_day' => 10]);
        $expense2 = $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.00, 'due_day' => 20]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response->assertStatus(200)
            ->assertJsonPath('data.fixed_created', 2);

        $this->assertDatabaseHas('financial_transactions', [
            'period_uid' => $period->uid,
            'reference_id' => $expense1->uid,
            'source' => Transaction::SOURCE_FIXED,
            'status' => Transaction::STATUS_PENDING,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'amount' => 1500.00,
            'category_uid' => $this->category->uid,
        ]);

        $this->assertDatabaseHas('financial_transactions', [
            'period_uid' => $period->uid,
            'reference_id' => $expense2->uid,
            'source' => Transaction::SOURCE_FIXED,
            'amount' => 120.00,
        ]);
    }

    public function test_initialization_ignores_inactive_fixed_expenses(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense(['active' => true]);
        $this->createFixedExpense(['active' => false, 'name' => 'Gym']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response->assertStatus(200)
            ->assertJsonPath('data.fixed_created', 1);

        $this->assertDatabaseCount('financial_transactions', 1);
    }

    // 17.2 — Test initialization with credit card installments (linking and creation)

    public function test_initialization_creates_transactions_from_unpaid_installments(): void
    {
        $period = $this->createPeriod(6, 2025);
        $installment = $this->createInstallment([
            'due_date' => Carbon::create(2025, 6, 15),
            'amount' => 200.00,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response->assertStatus(200)
            ->assertJsonPath('data.installments_created', 1);

        $this->assertDatabaseHas('financial_transactions', [
            'period_uid' => $period->uid,
            'reference_id' => $installment->uid,
            'source' => Transaction::SOURCE_CREDIT_CARD,
            'status' => Transaction::STATUS_PENDING,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'amount' => 200.00,
        ]);
    }

    public function test_initialization_links_existing_transaction_from_installment(): void
    {
        $period = $this->createPeriod(6, 2025);

        $existingTransaction = Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 200.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_CREDIT_CARD,
            'occurred_at' => Carbon::create(2025, 6, 1),
            'due_date' => Carbon::create(2025, 6, 15),
        ]);

        $card = CreditCard::create([
            'user_uid' => $this->user->uid,
            'name' => 'Nubank',
            'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
            'due_day' => 15,
        ]);

        $charge = CreditCardCharge::create([
            'credit_card_uid' => $card->uid,
            'amount' => 600.00,
            'description' => 'Compra',
            'total_installments' => 3,
        ]);

        $installment = CreditCardInstallment::create([
            'credit_card_charge_uid' => $charge->uid,
            'installment_number' => 1,
            'due_date' => Carbon::create(2025, 6, 15),
            'amount' => 200.00,
            'paid_at' => null,
        ]);

        DB::table('financial_credit_card_installments')
            ->where('uid', $installment->uid)
            ->update(['financial_transaction_uid' => $existingTransaction->uid]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response->assertStatus(200)
            ->assertJsonPath('data.installments_linked', 1)
            ->assertJsonPath('data.installments_created', 0);

        $this->assertDatabaseHas('financial_transactions', [
            'uid' => $existingTransaction->uid,
            'period_uid' => $period->uid,
        ]);

        $this->assertDatabaseCount('financial_transactions', 1);
    }

    // 17.3 — Test idempotency (re-execution without duplicates)

    public function test_initialization_is_idempotent_for_fixed_expenses(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense();

        $response1 = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response1->assertStatus(200)
            ->assertJsonPath('data.fixed_created', 1);

        $response2 = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response2->assertStatus(200)
            ->assertJsonPath('data.fixed_created', 0)
            ->assertJsonPath('data.skipped', 1);

        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_initialization_is_idempotent_for_installments(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createInstallment([
            'due_date' => Carbon::create(2025, 6, 15),
        ]);

        $response1 = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response1->assertStatus(200)
            ->assertJsonPath('data.installments_created', 1);

        $response2 = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response2->assertStatus(200)
            ->assertJsonPath('data.installments_created', 0)
            ->assertJsonPath('data.skipped', 1);
    }

    // 17.4 — Test initialization summary (correct counts)

    public function test_initialization_returns_correct_summary_counts(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense(['name' => 'Aluguel']);
        $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.00]);
        $this->createInstallment([
            'due_date' => Carbon::create(2025, 6, 15),
            'amount' => 200.00,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response->assertStatus(200)
            ->assertJsonPath('data.fixed_created', 2)
            ->assertJsonPath('data.installments_created', 1)
            ->assertJsonPath('data.installments_linked', 0)
            ->assertJsonPath('data.skipped', 0);

        $this->assertDatabaseCount('financial_transactions', 3);
    }

    public function test_initialization_summary_counts_skipped_on_rerun(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense(['name' => 'Aluguel']);
        $this->createInstallment([
            'due_date' => Carbon::create(2025, 6, 15),
        ]);

        // First run
        $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        // Second run — everything should be skipped
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response->assertStatus(200)
            ->assertJsonPath('data.fixed_created', 0)
            ->assertJsonPath('data.installments_created', 0)
            ->assertJsonPath('data.installments_linked', 0)
            ->assertJsonPath('data.skipped', 2);
    }

    // 17.5 — Test due_date clamping for short months (feb, apr, jun, sep, nov)

    public function test_clamps_due_date_for_february(): void
    {
        $period = $this->createPeriod(2, 2025);
        $this->createFixedExpense(['due_day' => 31]);

        $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize")
            ->assertStatus(200);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-02-28', $transaction->due_date->format('Y-m-d'));
    }

    public function test_clamps_due_date_for_april(): void
    {
        $period = $this->createPeriod(4, 2025);
        $this->createFixedExpense(['due_day' => 31]);

        $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize")
            ->assertStatus(200);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-04-30', $transaction->due_date->format('Y-m-d'));
    }

    public function test_clamps_due_date_for_june(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense(['due_day' => 31]);

        $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize")
            ->assertStatus(200);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-06-30', $transaction->due_date->format('Y-m-d'));
    }

    public function test_clamps_due_date_for_september(): void
    {
        $period = $this->createPeriod(9, 2025);
        $this->createFixedExpense(['due_day' => 31]);

        $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize")
            ->assertStatus(200);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-09-30', $transaction->due_date->format('Y-m-d'));
    }

    public function test_clamps_due_date_for_november(): void
    {
        $period = $this->createPeriod(11, 2025);
        $this->createFixedExpense(['due_day' => 31]);

        $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize")
            ->assertStatus(200);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-11-30', $transaction->due_date->format('Y-m-d'));
    }

    // 17.6 — Test incremental initialization (new fixed expense added between executions)

    public function test_incremental_initialization_creates_only_new_fixed_expense(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense(['name' => 'Aluguel']);

        $response1 = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response1->assertStatus(200)
            ->assertJsonPath('data.fixed_created', 1);

        // Add a new fixed expense between executions
        $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.00]);

        $response2 = $this->actingAs($this->user)
            ->postJson("/api/v1/periods/{$period->uid}/initialize");

        $response2->assertStatus(200)
            ->assertJsonPath('data.fixed_created', 1)
            ->assertJsonPath('data.skipped', 1);

        $this->assertDatabaseCount('financial_transactions', 2);
    }
}
