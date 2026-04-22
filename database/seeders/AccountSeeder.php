<?php

namespace Database\Seeders;

use App\Domain\Account\Models\Account;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Account::create([
                'user_uid' => $user->uid,
                'name' => 'Conta Corrente',
                'type' => Account::TYPE_CHECKING,
                'balance' => rand(1000, 10000),
            ]);

            Account::create([
                'user_uid' => $user->uid,
                'name' => 'Poupança',
                'type' => Account::TYPE_SAVINGS,
                'balance' => rand(5000, 50000),
            ]);

            Account::create([
                'user_uid' => $user->uid,
                'name' => 'Dinheiro',
                'type' => Account::TYPE_CASH,
                'balance' => rand(500, 2000),
            ]);
        }
    }
}
