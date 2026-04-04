<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use App\Models\FinancialFixedExpense;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialFixedExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $expenseNames = ['Aluguel', 'Internet', 'Luz', 'Água', 'Telefone'];

        foreach ($users as $user) {
            $outflowCategories = FinancialCategory::forUser($user->uid)->outflow()->get();

            if ($outflowCategories->isEmpty()) {
                continue;
            }

            foreach ($expenseNames as $name) {
                FinancialFixedExpense::create([
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
