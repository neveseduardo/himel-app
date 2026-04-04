<?php

namespace Database\Seeders;

use App\Models\FinancialCreditCard;
use App\Models\FinancialCreditCardCharge;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialCreditCardChargeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $cards = FinancialCreditCard::forUser($user->uid)->get();

            if ($cards->isEmpty()) {
                continue;
            }

            for ($i = 0; $i < 3; $i++) {
                $card = $cards->random();
                $totalInstallments = rand(2, 12);

                $charge = FinancialCreditCardCharge::create([
                    'credit_card_uid' => $card->uid,
                    'amount' => rand(500, 3000),
                    'description' => 'Compra parcelada '.($i + 1),
                    'total_installments' => $totalInstallments,
                ]);
            }
        }
    }
}
