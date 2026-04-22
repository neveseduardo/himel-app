<?php

namespace Database\Factories;

use App\Domain\Category\Models\Category;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FixedExpense>
 */
class FixedExpenseFactory extends Factory
{
    protected $model = FixedExpense::class;

    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'category_uid' => fn () => Category::factory()->create()->uid,
            'name' => fake()->word(),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'due_day' => fake()->numberBetween(1, 28),
            'active' => true,
        ];
    }
}
