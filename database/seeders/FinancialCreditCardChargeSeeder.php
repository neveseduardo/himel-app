<?php

namespace Database\Seeders;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class FinancialCreditCardChargeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $cards = CreditCard::forUser($user->uid)->get();

            if ($cards->isEmpty()) {
                continue;
            }

            for ($i = 0; $i < 3; $i++) {
                $card = $cards->random();
                $totalInstallments = rand(2, 12);

                $charge = CreditCardCharge::create([
                    'credit_card_uid' => $card->uid,
                    'amount' => rand(500, 3000),
                    'description' => 'Compra parcelada '.($i + 1),
                    'total_installments' => $totalInstallments,
                ]);
            }
        }
    }
}
