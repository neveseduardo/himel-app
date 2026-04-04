<?php

namespace Database\Factories;

use App\Models\FinancialCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialCategory>
 */
class FinancialCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'name' => fake()->word(),
            'direction' => fake()->randomElement(FinancialCategory::getDirections()),
        ];
    }
}
