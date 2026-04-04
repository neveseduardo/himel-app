<?php

namespace Database\Seeders;

use App\Models\FinancialCreditCard;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialCreditCardSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            FinancialCreditCard::create([
                'user_uid' => $user->uid,
                'name' => 'Cartão Principal',
                'card_type' => FinancialCreditCard::CARD_TYPE_PHYSICAL,
                'due_day' => 15,
            ]);

            FinancialCreditCard::create([
                'user_uid' => $user->uid,
                'name' => 'Cartão Virtual',
                'card_type' => FinancialCreditCard::CARD_TYPE_VIRTUAL,
                'due_day' => 20,
            ]);
        }
    }
}
