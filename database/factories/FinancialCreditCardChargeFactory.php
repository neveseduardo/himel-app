<?php

namespace Database\Factories;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditCardCharge>
 */
class FinancialCreditCardChargeFactory extends Factory
{
    protected $model = CreditCardCharge::class;

    public function definition(): array
    {
        return [
            'credit_card_uid' => fn () => CreditCard::factory()->create()->uid,
            'amount' => fake()->randomFloat(2, 50, 5000),
            'description' => fake()->sentence(),
            'total_installments' => fake()->numberBetween(1, 12),
        ];
    }
}
