<?php

namespace Database\Factories;

use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'financial_account_uid' => fn () => FinancialAccount::factory()->create()->uid,
            'financial_category_uid' => fn () => FinancialCategory::factory()->create()->uid,
            'amount' => fake()->randomFloat(2, 1, 1000),
            'direction' => fake()->randomElement(FinancialTransaction::getDirections()),
            'status' => fake()->randomElement(FinancialTransaction::getStatuses()),
            'source' => fake()->randomElement(FinancialTransaction::getSources()),
            'occurred_at' => now(),
            'due_date' => null,
            'paid_at' => null,
            'reference_id' => null,
        ];
    }
}
