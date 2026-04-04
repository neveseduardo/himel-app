<?php

namespace Database\Seeders;

use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $accounts = FinancialAccount::forUser($user->uid)->get();
            $inflowCategories = FinancialCategory::forUser($user->uid)->inflow()->get();
            $outflowCategories = FinancialCategory::forUser($user->uid)->outflow()->get();

            if ($accounts->isEmpty() || $inflowCategories->isEmpty() || $outflowCategories->isEmpty()) {
                continue;
            }

            $account = $accounts->first();

            for ($i = 0; $i < 10; $i++) {
                $category = $inflowCategories->random();
                FinancialTransaction::create([
                    'user_uid' => $user->uid,
                    'financial_account_uid' => $account->uid,
                    'financial_category_uid' => $category->uid,
                    'amount' => rand(1000, 10000),
                    'direction' => FinancialTransaction::DIRECTION_INFLOW,
                    'status' => FinancialTransaction::STATUS_PAID,
                    'source' => FinancialTransaction::SOURCE_MANUAL,
                    'occurred_at' => now()->subDays(rand(0, 30)),
                    'paid_at' => now()->subDays(rand(0, 30)),
                ]);
            }

            for ($i = 0; $i < 10; $i++) {
                $category = $outflowCategories->random();
                $status = rand(0, 2) === 0 ? FinancialTransaction::STATUS_PENDING : FinancialTransaction::STATUS_PAID;

                FinancialTransaction::create([
                    'user_uid' => $user->uid,
                    'financial_account_uid' => $account->uid,
                    'financial_category_uid' => $category->uid,
                    'amount' => rand(50, 500),
                    'direction' => FinancialTransaction::DIRECTION_OUTFLOW,
                    'status' => $status,
                    'source' => FinancialTransaction::SOURCE_MANUAL,
                    'occurred_at' => now()->subDays(rand(0, 30)),
                    'due_date' => now()->addDays(rand(1, 15)),
                    'paid_at' => $status === FinancialTransaction::STATUS_PAID ? now()->subDays(rand(0, 10)) : null,
                ]);
            }
        }
    }
}
