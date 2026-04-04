<?php

namespace Database\Factories;

use App\Models\FinancialAccount;
use App\Models\FinancialTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransfer>
 */
class FinancialTransferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'from_account_uid' => fn () => FinancialAccount::factory()->create()->uid,
            'to_account_uid' => fn () => FinancialAccount::factory()->create()->uid,
            'amount' => fake()->randomFloat(2, 1, 5000),
        ];
    }
}
