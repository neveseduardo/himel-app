<?php

namespace Database\Factories;

use App\Models\FinancialCreditCardCharge;
use App\Models\FinancialCreditCardInstallment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialCreditCardInstallment>
 */
class FinancialCreditCardInstallmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'credit_card_charge_uid' => fn () => FinancialCreditCardCharge::factory()->create()->uid,
            'financial_transaction_uid' => null,
            'installment_number' => fake()->numberBetween(1, 12),
            'due_date' => now()->addMonths(fake()->numberBetween(1, 12)),
            'amount' => fake()->randomFloat(2, 50, 500),
            'paid_at' => null,
        ];
    }
}
