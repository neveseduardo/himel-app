<?php

namespace Database\Seeders;

use App\Domain\Category\Models\Category;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class FinancialFixedExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $expenseNames = ['Aluguel', 'Internet', 'Luz', 'Água', 'Telefone'];

        foreach ($users as $user) {
            $outflowCategories = Category::forUser($user->uid)->outflow()->get();

            if ($outflowCategories->isEmpty()) {
                continue;
            }

            foreach ($expenseNames as $name) {
                FixedExpense::create([
                    'user_uid' => $user->uid,
                    'financial_category_uid' => $outflowCategories->random()->uid,
                    'name' => $name,
                    'amount' => rand(50, 500),
                    'due_day' => rand(1, 28),
                    'active' => true,
                ]);
            }
        }
    }
}
