<?php

namespace Database\Factories;

use App\Models\FinancialCategory;
use App\Models\FinancialFixedExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialFixedExpense>
 */
class FinancialFixedExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'financial_category_uid' => fn () => FinancialCategory::factory()->create()->uid,
            'name' => fake()->word(),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'due_day' => fake()->numberBetween(1, 28),
            'active' => true,
        ];
    }
}
