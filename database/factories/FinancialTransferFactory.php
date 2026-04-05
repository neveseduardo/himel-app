<?php

namespace Database\Factories;

use App\Domain\Account\Models\Account;
use App\Domain\Transfer\Models\Transfer;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class FinancialTransferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'from_account_uid' => fn () => Account::factory()->create()->uid,
            'to_account_uid' => fn () => Account::factory()->create()->uid,
            'amount' => fake()->randomFloat(2, 1, 5000),
        ];
    }
}
