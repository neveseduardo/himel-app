<?php

namespace Database\Factories;

use App\Domain\Category\Models\Category;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'name' => fake()->word(),
            'direction' => fake()->randomElement(Category::getDirections()),
        ];
    }
}
