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

        foreach ($users as $user) {
            CreditCard::create([
                'user_uid' => $user->uid,
                'name' => 'Cartão Principal',
                'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
                'due_day' => 15,
            ]);

            CreditCard::create([
                'user_uid' => $user->uid,
                'name' => 'Cartão Virtual',
                'card_type' => CreditCard::CARD_TYPE_VIRTUAL,
                'due_day' => 20,
            ]);
        }
    }
}
