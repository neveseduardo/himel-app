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

class PeriodReportTest extends TestCase
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
            'name' => 'Main Account',
            'type' => Account::TYPE_CHECKING,
            'balance' => 10000,
        ]);
        $this->outflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Moradia',
            'direction' => Category::DIRECTION_OUTFLOW,
        ]);
        $this->inflowCategory = Category::create([
            'user_uid' => $this->user->uid,
            'name' => 'Salário',
            'direction' => Category::DIRECTION_INFLOW,
        ]);
    }

    private function createPeriod(int $month = 1, int $year = 2025): Period
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
            'due_date' => Carbon::create(2025, 1, 15),
            'amount' => 200.00,
            'paid_at' => null,
        ], $installmentOverrides));

        return ['card' => $card, 'charge' => $charge, 'installment' => $installment];
    }

    // =========================================================================
    // Feature Tests — Period Report Endpoint
    // =========================================================================

    public function test_report_endpoint_returns_200_with_pdf_content_type_for_valid_period(): void
    {
        $period = $this->createPeriod();

        $response = $this->actingAs($this->user)
            ->get(route('periods.report', ['uid' => $period->uid]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_report_endpoint_returns_404_for_nonexistent_period(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('periods.report', ['uid' => 'nonexistent-uid']));

        $response->assertStatus(404);
    }

    public function test_report_endpoint_returns_404_for_period_belonging_to_another_user(): void
    {
        $otherUser = User::factory()->create();
        $period = Period::create([
            'user_uid' => $otherUser->uid,
            'month' => 3,
            'year' => 2025,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('periods.report', ['uid' => $period->uid]));

        $response->assertStatus(404);
    }

    public function test_report_content_disposition_contains_correct_filename(): void
    {
        $period = $this->createPeriod(3, 2025);

        $response = $this->actingAs($this->user)
            ->get(route('periods.report', ['uid' => $period->uid]));

        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('relatorio-periodo-03-2025.pdf', $contentDisposition);
    }

    public function test_report_generated_successfully_for_period_with_no_transactions(): void
    {
        $period = $this->createPeriod(7, 2025);

        $response = $this->actingAs($this->user)
            ->get(route('periods.report', ['uid' => $period->uid]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }

    public function test_report_generated_successfully_for_period_with_all_data_types(): void
    {
        $period = $this->createPeriod(1, 2025);

        // Fixed expense
        $expense = $this->createFixedExpense(['name' => 'Internet', 'amount' => 120.00, 'due_day' => 5]);
        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 120.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_FIXED,
            'reference_id' => $expense->uid,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 1, 1),
        ]);

        // Credit card installment
        $chain = $this->createCreditCardChain(
            ['name' => 'Nubank'],
            ['description' => 'TV Samsung', 'total_installments' => 10],
            ['installment_number' => 3, 'amount' => 350.00, 'due_date' => Carbon::create(2025, 1, 15)]
        );
        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 350.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_CREDIT_CARD,
            'reference_id' => $chain['installment']->uid,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 1, 1),
        ]);

        // Inflow transaction
        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->inflowCategory->uid,
            'amount' => 5000.00,
            'direction' => Transaction::DIRECTION_INFLOW,
            'status' => Transaction::STATUS_PAID,
            'source' => Transaction::SOURCE_MANUAL,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 1, 5),
        ]);

        // Outflow transaction (manual)
        Transaction::create([
            'user_uid' => $this->user->uid,
            'account_uid' => $this->account->uid,
            'category_uid' => $this->outflowCategory->uid,
            'amount' => 200.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PAID,
            'source' => Transaction::SOURCE_MANUAL,
            'period_uid' => $period->uid,
            'occurred_at' => Carbon::create(2025, 1, 10),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('periods.report', ['uid' => $period->uid]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }
}
