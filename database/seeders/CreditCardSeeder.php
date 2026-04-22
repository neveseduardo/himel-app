<?php

namespace Database\Seeders;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class CreditCardSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $cards = [
            ['name' => 'C6 BANK 1', 'last_four_digits' => '1234', 'card_type' => CreditCard::CARD_TYPE_PHYSICAL, 'closing_day' => 7, 'due_day' => 15],
            ['name' => 'NUBANK 1', 'last_four_digits' => '1235', 'card_type' => CreditCard::CARD_TYPE_PHYSICAL, 'closing_day' => 3, 'due_day' => 10],
            ['name' => 'NUBANK 2', 'last_four_digits' => '1236', 'card_type' => CreditCard::CARD_TYPE_VIRTUAL, 'closing_day' => 3, 'due_day' => 10],
            ['name' => 'CASAS BAHIA 1', 'last_four_digits' => '1237', 'card_type' => CreditCard::CARD_TYPE_PHYSICAL, 'closing_day' => 10, 'due_day' => 20],
        ];

        foreach ($users as $user) {
            foreach ($cards as $card) {
                CreditCard::create(array_merge($card, ['user_uid' => $user->uid]));
            }
        }
    }
}
