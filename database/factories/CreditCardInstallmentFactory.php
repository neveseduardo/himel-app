<?php

namespace Database\Factories;

use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditCardInstallment>
 */
class CreditCardInstallmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'credit_card_charge_uid' => fn () => CreditCardCharge::factory()->create()->uid,
            'transaction_uid' => null,
            'installment_number' => fake()->numberBetween(1, 12),
            'due_date' => now()->addMonths(fake()->numberBetween(1, 12)),
            'amount' => fake()->randomFloat(2, 50, 500),
            'paid_at' => null,
        ];
    }
}
