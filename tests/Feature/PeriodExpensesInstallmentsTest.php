<?php

namespace Tests\Feature;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\Period\Contracts\PeriodServiceInterface;
use App\Domain\Period\Models\Period;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PeriodExpensesInstallmentsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Category $category;

    private PeriodServiceInterface $service;

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
        $this->service = app(PeriodServiceInterface::class);
    }

    private function createPeriod(int $month = 6, int $year = 2025): Period
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
            'category_uid' => $this->category->uid,
            'name' => 'Aluguel',
            'amount' => 1500.00,
            'due_day' => 10,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        DB::table('fixed_expenses')->insert($data);

        return FixedExpense::find($data['uid']);
    }

    private function createFixedTransaction(Period $period, FixedExpense $fixedExpense, array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => $fixedExpense->amount,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_FIXED,
            'reference_id' => $fixedExpense->uid,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create($period->year, $period->month, 1),
        ], $overrides));
    }

    private function createCreditCardChain(array $cardOverrides = [], array $chargeOverrides = [], array $installmentOverrides = []): array
    {
        $card = CreditCard::create(array_merge([
            'user_uid' => $this->user->uid,
            'name' => 'Nubank',
            'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
            'due_day' => 15,
        ], $cardOverrides));

        $charge = CreditCardCharge::create(array_merge([
            'credit_card_uid' => $card->uid,
            'amount' => 600.00,
            'description' => 'Compra parcelada',
            'total_installments' => 3,
        ], $chargeOverrides));

        $installment = CreditCardInstallment::create(array_merge([
            'credit_card_charge_uid' => $charge->uid,
            'installment_number' => 1,
            'due_date' => Carbon::create(2025, 6, 15),
            'amount' => 200.00,
            'paid_at' => null,
        ], $installmentOverrides));

        return ['card' => $card, 'charge' => $charge, 'installment' => $installment];
    }

    private function createCreditCardTransaction(Period $period, CreditCardInstallment $installment, array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => $installment->amount,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_CREDIT_CARD,
            'reference_id' => $installment->uid,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create($period->year, $period->month, 1),
        ], $overrides));
    }

    // =========================================================================
    // 9.1 — getFixedExpensesForPeriod
    // =========================================================================

    public function test_get_fixed_expenses_returns_empty_for_period_with_no_fixed_transactions(): void
    {
        $period = $this->createPeriod();

        $result = $this->service->getFixedExpensesForPeriod($period->uid, $this->user->uid);

        $this->assertSame([], $result['items']);
        $this->assertSame(0, $result['subtotal']);
    }

    public function test_get_fixed_expenses_returns_one_item_with_correct_fields(): void
    {
        $period = $this->createPeriod();
        $expense = $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.50, 'due_day' => 15]);
        $this->createFixedTransaction($period, $expense);

        $result = $this->service->getFixedExpensesForPeriod($period->uid, $this->user->uid);

        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];
        $this->assertSame('Internet', $item['description']);
        $this->assertSame(120.50, $item['amount']);
        $this->assertSame(15, $item['due_day']);
        $this->assertSame('Moradia', $item['category_name']);
        $this->assertArrayHasKey('transaction_uid', $item);
        $this->assertSame(120.50, $result['subtotal']);
    }

    public function test_get_fixed_expenses_returns_n_items_with_correct_subtotal(): void
    {
        $period = $this->createPeriod();
        $expense1 = $this->createFixedExpense(['name' => 'Aluguel', 'amount' => 1500.00, 'due_day' => 10]);
        $expense2 = $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.00, 'due_day' => 20]);
        $expense3 = $this->createFixedExpense(['name' => 'Energia', 'amount' => 250.00, 'due_day' => 5]);

        $this->createFixedTransaction($period, $expense1);
        $this->createFixedTransaction($period, $expense2);
        $this->createFixedTransaction($period, $expense3);

        $result = $this->service->getFixedExpensesForPeriod($period->uid, $this->user->uid);

        $this->assertCount(3, $result['items']);
        $this->assertEqualsWithDelta(1870.00, $result['subtotal'], 0.01);
    }

    public function test_get_fixed_expenses_returns_null_fields_for_invalid_reference_id(): void
    {
        $period = $this->createPeriod();

        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 100.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_FIXED,
            'reference_id' => 'nonexistent-uid',
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 6, 1),
        ]);

        $result = $this->service->getFixedExpensesForPeriod($period->uid, $this->user->uid);

        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];
        $this->assertNull($item['description']);
        $this->assertNull($item['due_day']);
        $this->assertNull($item['category_name']);
        $this->assertSame(100.00, $item['amount']);
        $this->assertEqualsWithDelta(100.00, $result['subtotal'], 0.01);
    }

    // =========================================================================
    // 9.2 — getInstallmentsForPeriod
    // =========================================================================

    public function test_get_installments_returns_empty_for_period_with_no_credit_card_transactions(): void
    {
        $period = $this->createPeriod();

        $result = $this->service->getInstallmentsForPeriod($period->uid, $this->user->uid);

        $this->assertSame([], $result['items']);
        $this->assertSame(0, $result['subtotal']);
    }

    public function test_get_installments_returns_one_item_with_correct_fields(): void
    {
        $period = $this->createPeriod();
        $chain = $this->createCreditCardChain(
            ['name' => 'Nubank'],
            ['description' => 'TV Samsung', 'total_installments' => 10],
            ['installment_number' => 3, 'amount' => 350.00, 'due_date' => Carbon::create(2025, 6, 15)]
        );
        $this->createCreditCardTransaction($period, $chain['installment']);

        $result = $this->service->getInstallmentsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];
        $this->assertSame('TV Samsung', $item['charge_description']);
        $this->assertSame(350.00, $item['amount']);
        $this->assertSame('2025-06-15', $item['due_date']);
        $this->assertSame(3, $item['installment_number']);
        $this->assertSame(10, $item['total_installments']);
        $this->assertSame('Nubank', $item['credit_card_name']);
        $this->assertArrayHasKey('transaction_uid', $item);
        $this->assertEqualsWithDelta(350.00, $result['subtotal'], 0.01);
    }

    public function test_get_installments_returns_n_items_with_correct_subtotal(): void
    {
        $period = $this->createPeriod();

        $chain1 = $this->createCreditCardChain(
            ['name' => 'Nubank'],
            ['description' => 'Compra 1', 'total_installments' => 3],
            ['installment_number' => 1, 'amount' => 200.00]
        );
        $chain2 = $this->createCreditCardChain(
            ['name' => 'Inter'],
            ['description' => 'Compra 2', 'total_installments' => 6],
            ['installment_number' => 4, 'amount' => 150.00]
        );

        $this->createCreditCardTransaction($period, $chain1['installment']);
        $this->createCreditCardTransaction($period, $chain2['installment']);

        $result = $this->service->getInstallmentsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(2, $result['items']);
        $this->assertEqualsWithDelta(350.00, $result['subtotal'], 0.01);
    }

    public function test_get_installments_returns_null_fields_for_invalid_reference_id(): void
    {
        $period = $this->createPeriod();

        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 100.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_CREDIT_CARD,
            'reference_id' => 'nonexistent-uid',
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 6, 1),
        ]);

        $result = $this->service->getInstallmentsForPeriod($period->uid, $this->user->uid);

        $this->assertCount(1, $result['items']);
        $item = $result['items'][0];
        $this->assertNull($item['charge_description']);
        $this->assertNull($item['due_date']);
        $this->assertNull($item['installment_number']);
        $this->assertNull($item['total_installments']);
        $this->assertNull($item['credit_card_name']);
        $this->assertSame(100.00, $item['amount']);
    }

    public function test_get_installments_correctly_populates_installment_number_and_total(): void
    {
        $period = $this->createPeriod();
        $chain = $this->createCreditCardChain(
            [],
            ['total_installments' => 12],
            ['installment_number' => 7, 'amount' => 100.00]
        );
        $this->createCreditCardTransaction($period, $chain['installment']);

        $result = $this->service->getInstallmentsForPeriod($period->uid, $this->user->uid);

        $item = $result['items'][0];
        $this->assertSame(7, $item['installment_number']);
        $this->assertSame(12, $item['total_installments']);
    }

    // =========================================================================
    // 9.3 — getCardBreakdownForPeriod
    // =========================================================================

    public function test_get_card_breakdown_returns_empty_for_period_with_no_installments(): void
    {
        $period = $this->createPeriod();

        $result = $this->service->getCardBreakdownForPeriod($period->uid, $this->user->uid);

        $this->assertSame([], $result['cards']);
        $this->assertSame(0, $result['grand_total']);
    }

    public function test_get_card_breakdown_returns_one_card_with_correct_total(): void
    {
        $period = $this->createPeriod();
        $chain1 = $this->createCreditCardChain(
            ['name' => 'Nubank'],
            ['description' => 'Compra 1'],
            ['amount' => 200.00]
        );
        $chain2 = $this->createCreditCardChain(
            [],
            ['description' => 'Compra 2', 'credit_card_uid' => $chain1['card']->uid],
            ['amount' => 300.00]
        );

        $this->createCreditCardTransaction($period, $chain1['installment']);
        $this->createCreditCardTransaction($period, $chain2['installment']);

        $result = $this->service->getCardBreakdownForPeriod($period->uid, $this->user->uid);

        $this->assertCount(1, $result['cards']);
        $this->assertSame('Nubank', $result['cards'][0]['credit_card_name']);
        $this->assertSame($chain1['card']->uid, $result['cards'][0]['credit_card_uid']);
        $this->assertEqualsWithDelta(500.00, $result['cards'][0]['total'], 0.01);
        $this->assertEqualsWithDelta(500.00, $result['grand_total'], 0.01);
    }

    public function test_get_card_breakdown_returns_n_cards_with_correct_totals_and_grand_total(): void
    {
        $period = $this->createPeriod();

        $chain1 = $this->createCreditCardChain(
            ['name' => 'Nubank'],
            ['description' => 'Compra Nubank'],
            ['amount' => 200.00]
        );
        $chain2 = $this->createCreditCardChain(
            ['name' => 'Inter'],
            ['description' => 'Compra Inter'],
            ['amount' => 350.00]
        );

        $this->createCreditCardTransaction($period, $chain1['installment']);
        $this->createCreditCardTransaction($period, $chain2['installment']);

        $result = $this->service->getCardBreakdownForPeriod($period->uid, $this->user->uid);

        $this->assertCount(2, $result['cards']);

        $cardNames = array_column($result['cards'], 'credit_card_name');
        $this->assertContains('Nubank', $cardNames);
        $this->assertContains('Inter', $cardNames);

        $cardTotals = [];
        foreach ($result['cards'] as $card) {
            $cardTotals[$card['credit_card_name']] = $card['total'];
        }
        $this->assertEqualsWithDelta(200.00, $cardTotals['Nubank'], 0.01);
        $this->assertEqualsWithDelta(350.00, $cardTotals['Inter'], 0.01);
        $this->assertEqualsWithDelta(550.00, $result['grand_total'], 0.01);
    }

    // =========================================================================
    // 9.4 — getByUidWithSummary expanded
    // =========================================================================

    public function test_get_by_uid_with_summary_includes_subtotals_by_source(): void
    {
        $period = $this->createPeriod();

        $expense = $this->createFixedExpense(['amount' => 500.00]);
        $this->createFixedTransaction($period, $expense);

        $chain = $this->createCreditCardChain([], [], ['amount' => 300.00]);
        $this->createCreditCardTransaction($period, $chain['installment']);

        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 200.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 6, 1),
        ]);

        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 100.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_TRANSFER,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 6, 1),
        ]);

        $result = $this->service->getByUidWithSummary($period->uid, $this->user->uid);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('total_fixed_expenses', $result);
        $this->assertArrayHasKey('total_credit_card_installments', $result);
        $this->assertArrayHasKey('total_manual', $result);
        $this->assertArrayHasKey('total_transfer', $result);

        $this->assertEqualsWithDelta(500.00, $result['total_fixed_expenses'], 0.01);
        $this->assertEqualsWithDelta(300.00, $result['total_credit_card_installments'], 0.01);
        $this->assertEqualsWithDelta(200.00, $result['total_manual'], 0.01);
        $this->assertEqualsWithDelta(100.00, $result['total_transfer'], 0.01);
    }

    public function test_get_by_uid_with_summary_subtotals_sum_equals_total_outflow(): void
    {
        $period = $this->createPeriod();

        $expense = $this->createFixedExpense(['amount' => 1500.00]);
        $this->createFixedTransaction($period, $expense);

        $chain = $this->createCreditCardChain([], [], ['amount' => 400.00]);
        $this->createCreditCardTransaction($period, $chain['installment']);

        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 250.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 6, 1),
        ]);

        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 50.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_TRANSFER,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 6, 1),
        ]);

        // Also add an inflow to ensure it doesn't affect outflow subtotals
        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->category->uid,
            'amount' => 3000.00,
            'direction' => Transaction::DIRECTION_INFLOW,
            'status' => Transaction::STATUS_PAID,
            'source' => Transaction::SOURCE_MANUAL,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 6, 1),
        ]);

        $result = $this->service->getByUidWithSummary($period->uid, $this->user->uid);

        $this->assertNotNull($result);

        $sumOfSubtotals = $result['total_fixed_expenses']
            + $result['total_credit_card_installments']
            + $result['total_manual']
            + $result['total_transfer'];

        $this->assertEqualsWithDelta($result['total_outflow'], $sumOfSubtotals, 0.01);
        $this->assertEqualsWithDelta(3000.00, $result['total_inflow'], 0.01);
        $this->assertEqualsWithDelta(2200.00, $result['total_outflow'], 0.01);
    }
}
