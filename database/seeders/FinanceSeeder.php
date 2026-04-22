<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding financial data...');

        $this->call([
            AccountSeeder::class,
            CategorySeeder::class,
            PeriodSeeder::class,
            CreditCardSeeder::class,
            TransferSeeder::class,
            FixedExpenseSeeder::class,
            CreditCardChargeSeeder::class,
            CreditCardInstallmentSeeder::class,
            TransactionSeeder::class,
        ]);

        $this->command->info('Financial data seeded successfully!');
    }
}
