<?php

namespace Database\Seeders;

use App\Models\FinancialPeriod;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = 0; $i < 6; $i++) {
                $date = now()->subMonths($i);
                FinancialPeriod::create([
                    'user_uid' => $user->uid,
                    'month' => (int) $date->format('m'),
                    'year' => (int) $date->format('Y'),
                ]);
            }
        }
    }
}
