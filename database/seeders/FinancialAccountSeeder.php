<?php

namespace Database\Seeders;

use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            FinancialAccount::create([
                'user_uid' => $user->uid,
                'name' => 'Conta Corrente',
                'type' => FinancialAccount::TYPE_CHECKING,
                'balance' => rand(1000, 10000),
            ]);

            FinancialAccount::create([
                'user_uid' => $user->uid,
                'name' => 'Poupança',
                'type' => FinancialAccount::TYPE_SAVINGS,
                'balance' => rand(5000, 50000),
            ]);

            FinancialAccount::create([
                'user_uid' => $user->uid,
                'name' => 'Dinheiro',
                'type' => FinancialAccount::TYPE_CASH,
                'balance' => rand(500, 2000),
            ]);
        }
    }
}
