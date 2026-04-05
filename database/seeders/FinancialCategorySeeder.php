<?php

namespace Database\Seeders;

use App\Domain\Category\Models\Category;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class FinancialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $inflowCategories = ['Salário', 'Freelance', 'Investimentos', 'Reembolso', 'Outros Ingressos'];
        $outflowCategories = ['Alimentação', 'Transporte', 'Moradia', 'Lazer', 'Saúde'];

        foreach ($users as $user) {
            foreach ($inflowCategories as $name) {
                Category::create([
                    'user_uid' => $user->uid,
                    'name' => $name,
                    'direction' => Category::DIRECTION_INFLOW,
                ]);
            }

            foreach ($outflowCategories as $name) {
                Category::create([
                    'user_uid' => $user->uid,
                    'name' => $name,
                    'direction' => Category::DIRECTION_OUTFLOW,
                ]);
            }
        }
    }
}
