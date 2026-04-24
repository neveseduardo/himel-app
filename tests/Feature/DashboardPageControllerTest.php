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
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardPageControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    private Category $inflowCategory;

    private Category $outflowCategory;

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
            'name' => 'Alimentação',
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
            'occurred_at' => Carbon::create($period->year, $period->month, 1),
            'period_uid' => $period->uid,
        ], $overrides));
    }

    private function createFixedExpense(array $overrides = []): FixedExpense
    {
        $uid = Str::uuid()->toString();
        $data = array_merge([
            'uid' => $uid,
            'user_uid' => $this->user->uid,
            'category_uid' => $this->outflowCategory->uid,
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

    // =========================================================================
    // Authenticated user gets all expected Inertia props
    // =========================================================================

    public function test_authenticated_user_can_access_dashboard_with_all_expected_props(): void
    {
        $period = $this->createPeriod();

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('period')
                ->has('summary')
                ->has('cardBreakdown')
                ->has('periods')
                ->has('statusCounts')
                ->has('categoryBreakdown')
            );
    }

    // =========================================================================
    // Respects ?period=uid query param
    // =========================================================================

    public function test_respects_period_query_param_to_select_specific_period(): void
    {
        $period1 = $this->createPeriod(1, 2025);
        $period2 = $this->createPeriod(6, 2025);

        $response = $this->actingAs($this->user)->get('/dashboard?period='.$period2->uid);

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('period.uid', $period2->uid)
                ->where('period.month', 6)
                ->where('period.year', 2025)
            );
    }

    // =========================================================================
    // Returns empty/zeroed data when user has no periods
    // =========================================================================

    public function test_returns_empty_zeroed_data_when_user_has_no_periods(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('period', null)
                ->where('summary.total_inflow', 0)
                ->where('summary.total_outflow', 0)
                ->where('summary.balance', 0)
                ->where('summary.total_fixed_expenses', 0)
                ->where('summary.total_credit_card_installments', 0)
                ->where('summary.total_manual', 0)
                ->where('summary.total_transfer', 0)
                ->where('periods', [])
                ->where('statusCounts.pending', 0)
                ->where('statusCounts.paid', 0)
                ->where('statusCounts.overdue', 0)
                ->where('categoryBreakdown', [])
                ->where('cardBreakdown.cards', [])
                ->where('cardBreakdown.grand_total', 0)
            );
    }

    // =========================================================================
    // Returns correct data for period with mixed transactions
    // =========================================================================

    public function test_returns_correct_data_for_period_with_mixed_transactions(): void
    {
        $period = $this->createPeriod();

        $moradiaCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Moradia',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);

        // INFLOW — MANUAL — PAID
        $this->createTransaction($period, [
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 5000.00,
            'direction' => Transaction::DIRECTION_INFLOW,
            'status' => Transaction::STATUS_PAID,
            'source' => Transaction::SOURCE_MANUAL,
        ]);

        // OUTFLOW — MANUAL — PENDING
        $this->createTransaction($period, [
            'amount' => 300.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
        ]);

        // OUTFLOW — FIXED — OVERDUE
        $expense = $this->createFixedExpense(['amount' => 1500.00]);
        $this->createTransaction($period, [
            'category_uid' => $moradiaCategory->uid,
            'amount' => 1500.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_OVERDUE,
            'source' => Transaction::SOURCE_FIXED,
            'reference_id' => $expense->uid,
        ]);

        // OUTFLOW — CREDIT_CARD — PAID
        $card = CreditCard::create([
            'user_uid' => $this->user->uid,
            'name' => 'Nubank',
            'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
            'due_day' => 15,
        ]);
        $charge = CreditCardCharge::create([
            'credit_card_uid' => $card->uid,
            'amount' => 600.00,
            'description' => 'Notebook',
            'total_installments' => 3,
        ]);
        $installment = CreditCardInstallment::create([
            'credit_card_charge_uid' => $charge->uid,
            'installment_number' => 1,
            'due_date' => Carbon::create(2025, 6, 15),
            'amount' => 200.00,
        ]);
        $this->createTransaction($period, [
            'amount' => 200.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PAID,
            'source' => Transaction::SOURCE_CREDIT_CARD,
            'reference_id' => $installment->uid,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard?period='.$period->uid);

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('period')
                // Status counts: 1 PENDING, 2 PAID, 1 OVERDUE
                ->where('statusCounts.pending', 1)
                ->where('statusCounts.paid', 2)
                ->where('statusCounts.overdue', 1)
                // Category breakdown: only OUTFLOW transactions
                ->has('categoryBreakdown', 2)
                // Card breakdown
                ->has('cardBreakdown.cards', 1)
            );

        // Verify summary values separately (Inertia JSON serialization may cast floats to ints)
        $page = $response->original->getData()['page'];
        $summary = $page['props']['summary'];
        $this->assertEqualsWithDelta(5000.0, $summary['total_inflow'], 0.01);
        $this->assertEqualsWithDelta(2000.0, $summary['total_outflow'], 0.01);
        $this->assertEqualsWithDelta(3000.0, $summary['balance'], 0.01);
    }

    // =========================================================================
    // Unauthenticated user is redirected to login
    // =========================================================================

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }
}
