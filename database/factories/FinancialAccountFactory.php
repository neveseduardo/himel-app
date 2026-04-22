<?php

namespace Database\Factories;

use App\Domain\Account\Models\Account;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class FinancialAccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'name' => fake()->word(),
            'type' => fake()->randomElement(Account::getTypes()),
            'balance' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
