<?php

namespace Database\Seeders;

use App\Domain\Category\Models\Category;
use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\User\Models\User;
use Database\Factories\FinancialCreditCardFactory;
use Database\Factories\FinancialFixedExpenseFactory;
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

        $this->resetFixedExpenses($user);
        $this->seedNamedFixedExpenses($user);
        $this->seedFactoryFixedExpenses($user);
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

    private function resetFixedExpenses(User $user): void
    {
        FixedExpense::where('user_uid', $user->uid)->delete();
    }

    private function seedNamedFixedExpenses(User $user): void
    {
        $category = Category::where('user_uid', $user->uid)
            ->where('direction', 'OUTFLOW')
            ->first();

        $expenses = [
            ['name' => 'Aluguel', 'amount' => 1500.00, 'due_day' => 10, 'active' => true, 'category_uid' => $category->uid],
            ['name' => 'Internet', 'amount' => 120.00, 'due_day' => 15, 'active' => true, 'category_uid' => $category->uid],
            ['name' => 'Academia', 'amount' => 89.90, 'due_day' => 5, 'active' => false, 'category_uid' => $category->uid],
        ];

        foreach ($expenses as $expense) {
            FixedExpense::create(array_merge($expense, ['user_uid' => $user->uid]));
        }
    }

    private function seedFactoryFixedExpenses(User $user): void
    {
        $category = Category::where('user_uid', $user->uid)
            ->where('direction', 'OUTFLOW')
            ->first();

        FinancialFixedExpenseFactory::new()
            ->count(20)
            ->create([
                'user_uid' => $user->uid,
                'category_uid' => $category->uid,
            ]);
    }
}
