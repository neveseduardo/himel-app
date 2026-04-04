<?php

namespace Database\Factories;

use App\Models\FinancialCreditCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialCreditCard>
 */
class FinancialCreditCardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'name' => fake()->word(),
            'card_type' => fake()->randomElement(FinancialCreditCard::getCardTypes()),
            'due_day' => fake()->numberBetween(1, 28),
        ];
    }
}
