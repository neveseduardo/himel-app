<?php

namespace Tests\Feature\Domain\Period;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\Period\Models\Period;
use App\Domain\Period\Services\PeriodService;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PeriodServiceInitializeTest extends TestCase
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

    private function createPeriod(int $month, int $year): Period
    {
        return $this->service->create($this->user->uid, $month, $year);
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

    // ---- Task 5.1: Fixed Expenses ----

    public function test_creates_transactions_from_active_fixed_expenses(): void
    {
        $period = $this->createPeriod(6, 2025);
        $expense1 = $this->createFixedExpense(['name' => 'Aluguel', 'amount' => 1500.00, 'due_day' => 10]);
        $expense2 = $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.00, 'due_day' => 20]);

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(2, $summary['fixed_created']);

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

    public function test_ignores_inactive_fixed_expenses(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense(['active' => true]);
        $this->createFixedExpense(['active' => false, 'name' => 'Gym']);

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(1, $summary['fixed_created']);
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_fixed_expense_due_date_uses_period_month_year(): void
    {
        $period = $this->createPeriod(8, 2025);
        $this->createFixedExpense(['due_day' => 15]);

        $this->service->initializePeriod($period->uid, $this->user->uid);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-08-15', $transaction->due_date->format('Y-m-d'));
    }

    public function test_fixed_expense_occurred_at_is_first_day_of_period(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense();

        $this->service->initializePeriod($period->uid, $this->user->uid);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-06-01', $transaction->occurred_at->format('Y-m-d'));
    }

    public function test_fixed_expense_uses_users_first_account(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense();

        $this->service->initializePeriod($period->uid, $this->user->uid);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals($this->account->uid, $transaction->account_uid);
    }

    // ---- Task 5.1: Due date clamping ----

    public function test_clamps_due_date_for_february(): void
    {
        $period = $this->createPeriod(2, 2025);
        $this->createFixedExpense(['due_day' => 31]);

        $this->service->initializePeriod($period->uid, $this->user->uid);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-02-28', $transaction->due_date->format('Y-m-d'));
    }

    public function test_clamps_due_date_for_february_leap_year(): void
    {
        $period = $this->createPeriod(2, 2024);
        $this->createFixedExpense(['due_day' => 31]);

        $this->service->initializePeriod($period->uid, $this->user->uid);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2024-02-29', $transaction->due_date->format('Y-m-d'));
    }

    public function test_clamps_due_date_for_april(): void
    {
        $period = $this->createPeriod(4, 2025);
        $this->createFixedExpense(['due_day' => 31]);

        $this->service->initializePeriod($period->uid, $this->user->uid);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-04-30', $transaction->due_date->format('Y-m-d'));
    }

    public function test_does_not_clamp_when_due_day_is_valid(): void
    {
        $period = $this->createPeriod(1, 2025);
        $this->createFixedExpense(['due_day' => 31]);

        $this->service->initializePeriod($period->uid, $this->user->uid);

        $transaction = Transaction::where('period_uid', $period->uid)->first();
        $this->assertEquals('2025-01-31', $transaction->due_date->format('Y-m-d'));
    }

    // ---- Task 5.2: Credit Card Installments ----

    public function test_creates_transactions_from_unpaid_installments_in_period(): void
    {
        $period = $this->createPeriod(6, 2025);
        $installment = $this->createInstallment([
            'due_date' => Carbon::create(2025, 6, 15),
            'amount' => 200.00,
        ]);

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(1, $summary['installments_created']);
        $this->assertDatabaseHas('financial_transactions', [
            'period_uid' => $period->uid,
            'reference_id' => $installment->uid,
            'source' => Transaction::SOURCE_CREDIT_CARD,
            'status' => Transaction::STATUS_PENDING,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'amount' => 200.00,
        ]);
    }

    public function test_links_existing_transaction_from_installment_to_period(): void
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

        // Manually set the financial_transaction_uid in DB
        DB::table('financial_credit_card_installments')
            ->where('uid', $installment->uid)
            ->update(['financial_transaction_uid' => $existingTransaction->uid]);

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(1, $summary['installments_linked']);
        $this->assertEquals(0, $summary['installments_created']);

        $this->assertDatabaseHas('financial_transactions', [
            'uid' => $existingTransaction->uid,
            'period_uid' => $period->uid,
        ]);

        // Should not create a new transaction
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_ignores_paid_installments(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createInstallment([
            'due_date' => Carbon::create(2025, 6, 15),
            'paid_at' => Carbon::create(2025, 6, 10),
        ]);

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(0, $summary['installments_created']);
        $this->assertEquals(0, $summary['installments_linked']);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    public function test_ignores_installments_outside_period_month(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createInstallment([
            'due_date' => Carbon::create(2025, 7, 15),
        ]);

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(0, $summary['installments_created']);
        $this->assertDatabaseCount('financial_transactions', 0);
    }

    // ---- Task 5.3: Idempotency ----

    public function test_idempotent_fixed_expenses_not_duplicated(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense();

        $summary1 = $this->service->initializePeriod($period->uid, $this->user->uid);
        $summary2 = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(1, $summary1['fixed_created']);
        $this->assertEquals(0, $summary2['fixed_created']);
        $this->assertEquals(1, $summary2['skipped']);
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_idempotent_installments_not_duplicated(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createInstallment([
            'due_date' => Carbon::create(2025, 6, 15),
        ]);

        $summary1 = $this->service->initializePeriod($period->uid, $this->user->uid);
        $summary2 = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(1, $summary1['installments_created']);
        $this->assertEquals(0, $summary2['installments_created']);
        $this->assertEquals(1, $summary2['skipped']);
    }

    public function test_incremental_initialization_creates_only_new_expenses(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense(['name' => 'Aluguel']);

        $summary1 = $this->service->initializePeriod($period->uid, $this->user->uid);
        $this->assertEquals(1, $summary1['fixed_created']);

        // Add a new fixed expense
        $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.00]);

        $summary2 = $this->service->initializePeriod($period->uid, $this->user->uid);
        $this->assertEquals(1, $summary2['fixed_created']);
        $this->assertEquals(1, $summary2['skipped']);
        $this->assertDatabaseCount('financial_transactions', 2);
    }

    // ---- Task 5.4: DB::transaction and summary ----

    public function test_returns_correct_summary_structure(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense();

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertArrayHasKey('fixed_created', $summary);
        $this->assertArrayHasKey('installments_linked', $summary);
        $this->assertArrayHasKey('installments_created', $summary);
        $this->assertArrayHasKey('skipped', $summary);
    }

    public function test_throws_exception_for_nonexistent_period(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Período não encontrado.');

        $this->service->initializePeriod('nonexistent-uid', $this->user->uid);
    }

    public function test_throws_exception_when_user_has_no_account(): void
    {
        $userWithoutAccount = User::factory()->create();
        $period = Period::create([
            'user_uid' => $userWithoutAccount->uid,
            'month' => 6,
            'year' => 2025,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('É necessário ter ao menos uma conta para inicializar o período.');

        $this->service->initializePeriod($period->uid, $userWithoutAccount->uid);
    }

    public function test_empty_initialization_returns_zero_counts(): void
    {
        $period = $this->createPeriod(6, 2025);

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(0, $summary['fixed_created']);
        $this->assertEquals(0, $summary['installments_linked']);
        $this->assertEquals(0, $summary['installments_created']);
        $this->assertEquals(0, $summary['skipped']);
    }

    public function test_mixed_initialization_with_fixed_and_installments(): void
    {
        $period = $this->createPeriod(6, 2025);
        $this->createFixedExpense(['name' => 'Aluguel']);
        $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.00]);
        $this->createInstallment([
            'due_date' => Carbon::create(2025, 6, 15),
            'amount' => 200.00,
        ]);

        $summary = $this->service->initializePeriod($period->uid, $this->user->uid);

        $this->assertEquals(2, $summary['fixed_created']);
        $this->assertEquals(1, $summary['installments_created']);
        $this->assertEquals(0, $summary['skipped']);
        $this->assertDatabaseCount('financial_transactions', 3);
    }
}
