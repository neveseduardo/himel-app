<?php

namespace Database\Factories;

use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialAccount>
 */
class FinancialAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'name' => fake()->word(),
            'type' => fake()->randomElement(FinancialAccount::getTypes()),
            'balance' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
