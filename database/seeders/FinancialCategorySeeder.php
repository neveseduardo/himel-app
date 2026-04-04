<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use App\Models\User;
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
                FinancialCategory::create([
                    'user_uid' => $user->uid,
                    'name' => $name,
                    'direction' => FinancialCategory::DIRECTION_INFLOW,
                ]);
            }

            foreach ($outflowCategories as $name) {
                FinancialCategory::create([
                    'user_uid' => $user->uid,
                    'name' => $name,
                    'direction' => FinancialCategory::DIRECTION_OUTFLOW,
                ]);
            }
        }
    }
}
