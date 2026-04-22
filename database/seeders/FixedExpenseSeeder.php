<?php

namespace Database\Seeders;

use App\Domain\Category\Models\Category;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class FixedExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $expenses = [
            ['name' => 'Condomínio', 'amount' => 340, 'due_day' => 10],
            ['name' => 'Internet', 'amount' => 120, 'due_day' => 15],
            ['name' => 'Financiamento Imóvel 1', 'amount' => 2300, 'due_day' => 10],
            ['name' => 'Financiamento Carro 1', 'amount' => 1300, 'due_day' => 12],
            ['name' => 'Empréstimo ITAÚ 1', 'amount' => 1200, 'due_day' => 8],
            ['name' => 'Mercado|Alimentação', 'amount' => 2500, 'due_day' => 1],
            ['name' => 'Corte de cabelo 1', 'amount' => 40, 'due_day' => 15],
            ['name' => 'Netflix', 'amount' => 60, 'due_day' => 5],
            ['name' => 'Prime Video', 'amount' => 20, 'due_day' => 5],
            ['name' => 'Linha de telefone 1', 'amount' => 90, 'due_day' => 18],
            ['name' => 'Seguro carro 1', 'amount' => 230, 'due_day' => 20],
            ['name' => 'PUC Celly', 'amount' => 550, 'due_day' => 10],
            ['name' => 'Despesas Moradia Belém', 'amount' => 600, 'due_day' => 1],
            ['name' => 'Fortnite Clube 1', 'amount' => 40, 'due_day' => 1],
            ['name' => 'Fortnite Clube 2', 'amount' => 40, 'due_day' => 1],
        ];

        foreach ($users as $user) {
            $outflowCategories = Category::forUser($user->uid)->outflow()->get();

            if ($outflowCategories->isEmpty()) {
                continue;
            }

            foreach ($expenses as $expense) {
                FixedExpense::create([
                    'user_uid' => $user->uid,
                    'category_uid' => $outflowCategories->random()->uid,
                    'name' => $expense['name'],
                    'amount' => $expense['amount'],
                    'due_day' => $expense['due_day'],
                    'active' => true,
                ]);
            }
        }
    }
}
