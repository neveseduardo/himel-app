<?php

namespace Database\Seeders;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\User\Models\User;
use Database\Factories\FinancialCreditCardFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class E2eTestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'e2e@test.com'],
            [
                'name' => 'E2E Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $this->resetCreditCards($user);
        $this->seedNamedCreditCards($user);
        $this->seedFactoryCreditCards($user);
    }

    private function resetCreditCards(User $user): void
    {
        CreditCard::where('user_uid', $user->uid)->delete();
    }

    private function seedNamedCreditCards(User $user): void
    {
        $cards = [
            [
                'name' => 'Nubank',
                'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
                'due_day' => 15,
                'closing_day' => 5,
                'last_four_digits' => '1234',
            ],
            [
                'name' => 'Inter',
                'card_type' => CreditCard::CARD_TYPE_VIRTUAL,
                'due_day' => 20,
                'closing_day' => 10,
                'last_four_digits' => '5678',
            ],
            [
                'name' => 'C6 Bank',
                'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
                'due_day' => 10,
                'closing_day' => 1,
                'last_four_digits' => '9012',
            ],
        ];

        foreach ($cards as $card) {
            CreditCard::create(array_merge($card, ['user_uid' => $user->uid]));
        }
    }

    private function seedFactoryCreditCards(User $user): void
    {
        FinancialCreditCardFactory::new()
            ->count(20)
            ->create(['user_uid' => $user->uid]);
    }
}
