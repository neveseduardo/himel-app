<?php

namespace Database\Seeders;

use App\Domain\Category\Models\Category;
use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\User\Models\User;
use Database\Factories\FinancialCreditCardChargeFactory;
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

        $this->ensureDefaultCategories($user);

        $this->resetCreditCards($user);
        $this->seedNamedCreditCards($user);
        $this->seedFactoryCreditCards($user);

        $this->resetCreditCardCharges($user);
        $this->seedNamedCreditCardCharges($user);
        $this->seedFactoryCreditCardCharges($user);

        $this->resetFixedExpenses($user);
        $this->seedNamedFixedExpenses($user);
        $this->seedFactoryFixedExpenses($user);
    }

    private function ensureDefaultCategories(User $user): void
    {
        if (Category::where('user_uid', $user->uid)->exists()) {
            return;
        }

        $categories = [
            ['name' => 'Alimentação', 'direction' => 'OUTFLOW'],
            ['name' => 'Moradia', 'direction' => 'OUTFLOW'],
            ['name' => 'Transporte', 'direction' => 'OUTFLOW'],
            ['name' => 'Saúde', 'direction' => 'OUTFLOW'],
            ['name' => 'Educação', 'direction' => 'OUTFLOW'],
            ['name' => 'Lazer', 'direction' => 'OUTFLOW'],
            ['name' => 'Vestuário', 'direction' => 'OUTFLOW'],
            ['name' => 'Outros Gastos', 'direction' => 'OUTFLOW'],
            ['name' => 'Salário', 'direction' => 'INFLOW'],
            ['name' => 'Freelance', 'direction' => 'INFLOW'],
            ['name' => 'Investimentos', 'direction' => 'INFLOW'],
            ['name' => 'Outros Recebimentos', 'direction' => 'INFLOW'],
        ];

        foreach ($categories as $category) {
            Category::create(array_merge($category, ['user_uid' => $user->uid]));
        }
    }

    private function resetCreditCards(User $user): void
    {
        CreditCard::where('user_uid', $user->uid)->delete();
    }

    private function seedNamedCreditCards(User $user): void
    {
        $cards = [
            ['name' => 'Nubank', 'card_type' => CreditCard::CARD_TYPE_PHYSICAL, 'due_day' => 15, 'closing_day' => 5, 'last_four_digits' => '1234'],
            ['name' => 'Inter', 'card_type' => CreditCard::CARD_TYPE_VIRTUAL, 'due_day' => 20, 'closing_day' => 10, 'last_four_digits' => '5678'],
            ['name' => 'C6 Bank', 'card_type' => CreditCard::CARD_TYPE_PHYSICAL, 'due_day' => 10, 'closing_day' => 1, 'last_four_digits' => '9012'],
        ];

        foreach ($cards as $card) {
            CreditCard::create(array_merge($card, ['user_uid' => $user->uid]));
        }
    }

    private function seedFactoryCreditCards(User $user): void
    {
        FinancialCreditCardFactory::new()->count(20)->create(['user_uid' => $user->uid]);
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
            ['credit_card_uid' => $nubank->uid, 'description' => 'Notebook Dell', 'amount' => 4500.00, 'total_installments' => 12, 'purchase_date' => '2024-03-15'],
            ['credit_card_uid' => $inter->uid, 'description' => 'Fone Bluetooth', 'amount' => 250.00, 'total_installments' => 3, 'purchase_date' => '2024-02-20'],
            ['credit_card_uid' => $c6Bank->uid, 'description' => 'Curso Online', 'amount' => 1200.00, 'total_installments' => 6, 'purchase_date' => '2024-01-10'],
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

    private function resetFixedExpenses(User $user): void
    {
        FixedExpense::where('user_uid', $user->uid)->delete();
    }

    private function seedNamedFixedExpenses(User $user): void
    {
        $category = Category::where('user_uid', $user->uid)->where('direction', 'OUTFLOW')->first();

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
        $category = Category::where('user_uid', $user->uid)->where('direction', 'OUTFLOW')->first();

        FinancialFixedExpenseFactory::new()->count(20)->create([
            'user_uid' => $user->uid,
            'category_uid' => $category->uid,
        ]);
    }
}
