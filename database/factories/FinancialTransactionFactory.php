<?php

namespace Database\Factories;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class FinancialTransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_uid' => fn () => User::factory()->create()->uid,
            'account_uid' => fn () => Account::factory()->create()->uid,
            'category_uid' => fn () => Category::factory()->create()->uid,
            'amount' => fake()->randomFloat(2, 1, 1000),
            'direction' => fake()->randomElement(Transaction::getDirections()),
            'status' => fake()->randomElement(Transaction::getStatuses()),
            'source' => fake()->randomElement(Transaction::getSources()),
            'occurred_at' => now(),
            'due_date' => null,
            'paid_at' => null,
            'reference_id' => null,
        ];
    }
}
