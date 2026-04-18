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

        $this->seedNamedCreditCards($user);
        $this->seedFactoryCreditCards($user);
    }

    private function seedNamedCreditCards(User $user): void
    {
        $cards = [
            [
                'name' => 'Nubank',
                'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
                'due_day' => 15,
            ],
            [
                'name' => 'Inter',
                'card_type' => CreditCard::CARD_TYPE_VIRTUAL,
                'due_day' => 20,
            ],
            [
                'name' => 'C6 Bank',
                'card_type' => CreditCard::CARD_TYPE_PHYSICAL,
                'due_day' => 10,
            ],
        ];

        foreach ($cards as $card) {
            CreditCard::updateOrCreate(
                [
                    'user_uid' => $user->uid,
                    'name' => $card['name'],
                ],
                $card,
            );
        }
    }

    private function seedFactoryCreditCards(User $user): void
    {
        $existingCount = CreditCard::where('user_uid', $user->uid)
            ->whereNotIn('name', ['Nubank', 'Inter', 'C6 Bank'])
            ->count();

        $remaining = 20 - $existingCount;

        if ($remaining > 0) {
            FinancialCreditCardFactory::new()
                ->count($remaining)
                ->create(['user_uid' => $user->uid]);
        }
    }
}
