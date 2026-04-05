<?php

namespace Database\Seeders;

use App\Domain\Account\Models\Account;
use App\Domain\Transfer\Models\Transfer;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class FinancialTransferSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $accounts = Account::forUser($user->uid)->get();

            if ($accounts->count() < 2) {
                continue;
            }

            for ($i = 0; $i < 5; $i++) {
                $accountsArray = $accounts->toArray();
                $fromAccount = $accounts->random();
                $toAccount = $accounts->where('uid', '!=', $fromAccount->uid)->random();

                Transfer::create([
                    'user_uid' => $user->uid,
                    'from_account_uid' => $fromAccount->uid,
                    'to_account_uid' => $toAccount->uid,
                    'amount' => rand(100, 1000),
                ]);
            }
        }
    }
}
