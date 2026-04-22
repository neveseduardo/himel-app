<?php

namespace Database\Seeders;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $accounts = Account::forUser($user->uid)->get();
            $inflowCategories = Category::forUser($user->uid)->inflow()->get();
            $outflowCategories = Category::forUser($user->uid)->outflow()->get();

            if ($accounts->isEmpty() || $inflowCategories->isEmpty() || $outflowCategories->isEmpty()) {
                continue;
            }

            $account = $accounts->first();

            for ($i = 0; $i < 10; $i++) {
                $category = $inflowCategories->random();
                Transaction::create([
                    'user_uid' => $user->uid,
                    'account_uid' => $account->uid,
                    'category_uid' => $category->uid,
                    'amount' => rand(1000, 10000),
                    'direction' => Transaction::DIRECTION_INFLOW,
                    'status' => Transaction::STATUS_PAID,
                    'source' => Transaction::SOURCE_MANUAL,
                    'occurred_at' => now()->subDays(rand(0, 30)),
                    'paid_at' => now()->subDays(rand(0, 30)),
                ]);
            }

            for ($i = 0; $i < 10; $i++) {
                $category = $outflowCategories->random();
                $status = rand(0, 2) === 0 ? Transaction::STATUS_PENDING : Transaction::STATUS_PAID;

                Transaction::create([
                    'user_uid' => $user->uid,
                    'account_uid' => $account->uid,
                    'category_uid' => $category->uid,
                    'amount' => rand(50, 500),
                    'direction' => Transaction::DIRECTION_OUTFLOW,
                    'status' => $status,
                    'source' => Transaction::SOURCE_MANUAL,
                    'occurred_at' => now()->subDays(rand(0, 30)),
                    'due_date' => now()->addDays(rand(1, 15)),
                    'paid_at' => $status === Transaction::STATUS_PAID ? now()->subDays(rand(0, 10)) : null,
                ]);
            }
        }
    }
}
