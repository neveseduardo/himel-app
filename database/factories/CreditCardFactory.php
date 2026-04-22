<?php

namespace Database\Factories;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditCard>
 */
class CreditCardFactory extends Factory
{
    protected $model = CreditCard::class;

    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'name' => fake()->word(),
            'card_type' => fake()->randomElement(CreditCard::getCardTypes()),
            'due_day' => fake()->numberBetween(1, 28),
            'closing_day' => fake()->numberBetween(1, 28),
            'last_four_digits' => fake()->numerify('####'),
        ];
    }
}
