<?php

namespace Database\Factories;

use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardCharge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialCreditCardCharge>
 */
class FinancialCreditCardChargeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'credit_card_uid' => fn () => FinancialCreditCard::factory()->create()->uid,
            'amount' => fake()->randomFloat(2, 50, 5000),
            'description' => fake()->sentence(),
            'total_installments' => fake()->numberBetween(1, 12),
        ];
    }
}
