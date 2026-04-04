<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FinancialSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding financial data...');

        $this->call([
            FinancialAccountSeeder::class,
            FinancialCategorySeeder::class,
            FinancialPeriodSeeder::class,
            FinancialCreditCardSeeder::class,
            FinancialTransferSeeder::class,
            FinancialFixedExpenseSeeder::class,
            FinancialCreditCardChargeSeeder::class,
            FinancialCreditCardInstallmentSeeder::class,
            FinancialTransactionSeeder::class,
        ]);

        $this->command->info('Financial data seeded successfully!');
    }
}
