<?php

namespace Database\Seeders;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\User\Models\User;
use Database\Factories\FinancialCreditCardChargeFactory;
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

        $this->resetCreditCardCharges($user);
        $this->seedNamedCreditCardCharges($user);
        $this->seedFactoryCreditCardCharges($user);
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

    private function resetCreditCardCharges(User $user): void
    {
        $chargeUids = CreditCardCharge::whereHas('creditCard', function ($query) use ($user): void {
            $query->where('user_uid', $user->uid);
        })->pluck('uid');

        CreditCardInstallment::whereIn('credit_card_charge_uid', $chargeUids)->delete();
        CreditCardCharge::whereIn('uid', $chargeUids)->delete();
    }

    private function seedNamedCreditCardCharges(User $user): void
    {
        $nubank = CreditCard::where('user_uid', $user->uid)->where('name', 'Nubank')->first();
        $inter = CreditCard::where('user_uid', $user->uid)->where('name', 'Inter')->first();
        $c6Bank = CreditCard::where('user_uid', $user->uid)->where('name', 'C6 Bank')->first();

        $charges = [
            [
                'credit_card_uid' => $nubank->uid,
                'description' => 'Notebook Dell',
                'amount' => 4500.00,
                'total_installments' => 12,
            ],
            [
                'credit_card_uid' => $inter->uid,
                'description' => 'Fone Bluetooth',
                'amount' => 250.00,
                'total_installments' => 3,
            ],
            [
                'credit_card_uid' => $c6Bank->uid,
                'description' => 'Curso Online',
                'amount' => 1200.00,
                'total_installments' => 6,
            ],
        ];

        foreach ($charges as $charge) {
            CreditCardCharge::create($charge);
        }
    }

    private function seedFactoryCreditCardCharges(User $user): void
    {
        $cardUids = CreditCard::where('user_uid', $user->uid)
            ->whereIn('name', ['Nubank', 'Inter', 'C6 Bank'])
            ->pluck('uid')
            ->toArray();

        foreach (range(1, 13) as $i) {
            FinancialCreditCardChargeFactory::new()->create([
                'credit_card_uid' => $cardUids[$i % count($cardUids)],
            ]);
        }
    }
}
