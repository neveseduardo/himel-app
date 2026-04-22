<?php

namespace Database\Seeders;

use App\Domain\Account\Models\Account;
use App\Domain\Category\Models\Category;
use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\Period\Models\Period;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\Transfer\Models\Transfer;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Database\Factories\AccountFactory;
use Database\Factories\CreditCardChargeFactory;
use Database\Factories\CreditCardFactory;
use Database\Factories\FixedExpenseFactory;
use Database\Factories\TransactionFactory;
use Database\Factories\TransferFactory;
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

        // Reset in reverse FK order: Transactions → Transfers → Accounts
        $this->resetTransactions($user);
        $this->resetTransfers($user);
        $this->resetAccounts($user);

        // Seed in FK order: Accounts → Transfers → Transactions
        $this->seedNamedAccounts($user);
        $this->seedFactoryAccounts($user);
        $this->seedNamedTransfers($user);
        $this->seedFactoryTransfers($user);
        $this->seedNamedTransactions($user);
        $this->seedFactoryTransactions($user);

        $this->resetCreditCards($user);
        $this->seedNamedCreditCards($user);
        $this->seedFactoryCreditCards($user);

        $this->resetCreditCardCharges($user);
        $this->seedNamedCreditCardCharges($user);
        $this->seedFactoryCreditCardCharges($user);

        $this->resetFixedExpenses($user);
        $this->seedNamedFixedExpenses($user);
        $this->seedFactoryFixedExpenses($user);

        $this->resetPeriods($user);
        $this->seedNamedPeriods($user);
        $this->seedPeriodTransactions($user);
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

    private function resetAccounts(User $user): void
    {
        Account::where('user_uid', $user->uid)->delete();
    }

    private function seedNamedAccounts(User $user): void
    {
        $accounts = [
            ['name' => 'Conta Corrente BB', 'type' => Account::TYPE_CHECKING, 'balance' => 5000.00],
            ['name' => 'Poupança Nubank', 'type' => Account::TYPE_SAVINGS, 'balance' => 12000.00],
            ['name' => 'Carteira', 'type' => Account::TYPE_CASH, 'balance' => 350.50],
        ];

        foreach ($accounts as $account) {
            Account::create(array_merge($account, ['user_uid' => $user->uid]));
        }
    }

    private function seedFactoryAccounts(User $user): void
    {
        AccountFactory::new()->count(20)->create(['user_uid' => $user->uid]);
    }

    private function resetTransfers(User $user): void
    {
        Transfer::where('user_uid', $user->uid)->delete();
    }

    private function seedNamedTransfers(User $user): void
    {
        $bb = Account::where('user_uid', $user->uid)->where('name', 'Conta Corrente BB')->first();
        $nubank = Account::where('user_uid', $user->uid)->where('name', 'Poupança Nubank')->first();
        $carteira = Account::where('user_uid', $user->uid)->where('name', 'Carteira')->first();

        $transfers = [
            ['from_account_uid' => $bb->uid, 'to_account_uid' => $nubank->uid, 'amount' => 1000.00],
            ['from_account_uid' => $nubank->uid, 'to_account_uid' => $carteira->uid, 'amount' => 200.00],
            ['from_account_uid' => $carteira->uid, 'to_account_uid' => $bb->uid, 'amount' => 50.00],
        ];

        foreach ($transfers as $transfer) {
            Transfer::create(array_merge($transfer, ['user_uid' => $user->uid]));
        }
    }

    private function seedFactoryTransfers(User $user): void
    {
        $accountUids = Account::where('user_uid', $user->uid)->pluck('uid')->toArray();

        foreach (range(1, 13) as $i) {
            TransferFactory::new()->create([
                'user_uid' => $user->uid,
                'from_account_uid' => $accountUids[$i % count($accountUids)],
                'to_account_uid' => $accountUids[($i + 1) % count($accountUids)],
            ]);
        }
    }

    private function resetTransactions(User $user): void
    {
        Transaction::where('user_uid', $user->uid)->delete();
    }

    private function seedNamedTransactions(User $user): void
    {
        $bb = Account::where('user_uid', $user->uid)->where('name', 'Conta Corrente BB')->first();
        $nubank = Account::where('user_uid', $user->uid)->where('name', 'Poupança Nubank')->first();

        $salario = Category::where('user_uid', $user->uid)->where('name', 'Salário')->first();
        $alimentacao = Category::where('user_uid', $user->uid)->where('name', 'Alimentação')->first();
        $moradia = Category::where('user_uid', $user->uid)->where('name', 'Moradia')->first();

        $transactions = [
            [
                'account_uid' => $bb->uid,
                'category_uid' => $salario->uid,
                'amount' => 8500.00,
                'direction' => Transaction::DIRECTION_INFLOW,
                'status' => Transaction::STATUS_PAID,
                'source' => Transaction::SOURCE_MANUAL,
                'description' => 'Salário Mensal',
                'occurred_at' => now(),
            ],
            [
                'account_uid' => $bb->uid,
                'category_uid' => $alimentacao->uid,
                'amount' => 450.00,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PAID,
                'source' => Transaction::SOURCE_MANUAL,
                'description' => 'Supermercado',
                'occurred_at' => now(),
            ],
            [
                'account_uid' => $nubank->uid,
                'category_uid' => $moradia->uid,
                'amount' => 180.00,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_MANUAL,
                'description' => 'Conta de Luz',
                'occurred_at' => now(),
            ],
        ];

        foreach ($transactions as $transaction) {
            Transaction::create(array_merge($transaction, ['user_uid' => $user->uid]));
        }
    }

    private function seedFactoryTransactions(User $user): void
    {
        $accountUids = Account::where('user_uid', $user->uid)->pluck('uid')->toArray();
        $categoryUids = Category::where('user_uid', $user->uid)->pluck('uid')->toArray();

        foreach (range(1, 20) as $i) {
            TransactionFactory::new()->create([
                'user_uid' => $user->uid,
                'account_uid' => $accountUids[$i % count($accountUids)],
                'category_uid' => $categoryUids[$i % count($categoryUids)],
            ]);
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
        CreditCardFactory::new()->count(20)->create(['user_uid' => $user->uid]);
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
            CreditCardChargeFactory::new()->create([
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

        FixedExpenseFactory::new()->count(20)->create([
            'user_uid' => $user->uid,
            'category_uid' => $category->uid,
        ]);
    }

    private function resetPeriods(User $user): void
    {
        $periodUids = Period::where('user_uid', $user->uid)->pluck('uid');

        Transaction::whereIn('period_uid', $periodUids)->update(['period_uid' => null]);
        Period::where('user_uid', $user->uid)->delete();
    }

    private function seedNamedPeriods(User $user): void
    {
        $periods = [
            ['month' => 1, 'year' => 2025],
            ['month' => 2, 'year' => 2025],
            ['month' => 3, 'year' => 2025],
        ];

        foreach ($periods as $period) {
            Period::create(array_merge($period, ['user_uid' => $user->uid]));
        }
    }

    private function seedPeriodTransactions(User $user): void
    {
        $janeiro = Period::where('user_uid', $user->uid)->where('month', 1)->where('year', 2025)->first();
        $bb = Account::where('user_uid', $user->uid)->where('name', 'Conta Corrente BB')->first();
        $salarioCategory = Category::where('user_uid', $user->uid)->where('name', 'Salário')->first();
        $alimentacaoCategory = Category::where('user_uid', $user->uid)->where('name', 'Alimentação')->first();
        $moradiaCategory = Category::where('user_uid', $user->uid)->where('name', 'Moradia')->first();

        // 1. MANUAL INFLOW — Salário
        Transaction::create([
            'user_uid' => $user->uid,
            'account_uid' => $bb->uid,
            'category_uid' => $salarioCategory->uid,
            'period_uid' => $janeiro->uid,
            'amount' => 5000.00,
            'direction' => Transaction::DIRECTION_INFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'description' => 'Salário',
            'due_date' => Carbon::create(2025, 1, 5),
            'occurred_at' => Carbon::create(2025, 1, 1),
        ]);

        // 2. MANUAL OUTFLOW — Supermercado
        Transaction::create([
            'user_uid' => $user->uid,
            'account_uid' => $bb->uid,
            'category_uid' => $alimentacaoCategory->uid,
            'period_uid' => $janeiro->uid,
            'amount' => 300.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_MANUAL,
            'description' => 'Supermercado',
            'due_date' => Carbon::create(2025, 1, 10),
            'occurred_at' => Carbon::create(2025, 1, 1),
        ]);

        // 3. FIXED OUTFLOW — linked to Aluguel fixed expense
        $aluguel = FixedExpense::where('user_uid', $user->uid)->where('name', 'Aluguel')->first();

        Transaction::create([
            'user_uid' => $user->uid,
            'account_uid' => $bb->uid,
            'category_uid' => $moradiaCategory->uid,
            'period_uid' => $janeiro->uid,
            'amount' => 1500.00,
            'direction' => Transaction::DIRECTION_OUTFLOW,
            'status' => Transaction::STATUS_PENDING,
            'source' => Transaction::SOURCE_FIXED,
            'reference_id' => $aluguel->uid,
            'description' => 'Aluguel',
            'due_date' => Carbon::create(2025, 1, 10),
            'occurred_at' => Carbon::create(2025, 1, 1),
        ]);

        // 4. CREDIT_CARD OUTFLOW — linked to Notebook Dell installment
        $notebookCharge = CreditCardCharge::whereHas('creditCard', function ($query) use ($user): void {
            $query->where('user_uid', $user->uid);
        })->where('description', 'Notebook Dell')->first();

        if ($notebookCharge) {
            // Create an installment for the charge if none exists
            $installment = CreditCardInstallment::where('credit_card_charge_uid', $notebookCharge->uid)
                ->orderBy('installment_number')
                ->first();

            if (! $installment) {
                $installmentAmount = round($notebookCharge->amount / $notebookCharge->total_installments, 2);
                $installment = CreditCardInstallment::create([
                    'credit_card_charge_uid' => $notebookCharge->uid,
                    'installment_number' => 1,
                    'amount' => $installmentAmount,
                    'due_date' => Carbon::create(2025, 1, 15),
                ]);
            }

            Transaction::create([
                'user_uid' => $user->uid,
                'account_uid' => $bb->uid,
                'category_uid' => $moradiaCategory->uid,
                'period_uid' => $janeiro->uid,
                'amount' => $installment->amount,
                'direction' => Transaction::DIRECTION_OUTFLOW,
                'status' => Transaction::STATUS_PENDING,
                'source' => Transaction::SOURCE_CREDIT_CARD,
                'reference_id' => $installment->uid,
                'description' => 'Notebook Dell',
                'due_date' => Carbon::create(2025, 1, 15),
                'occurred_at' => Carbon::create(2025, 1, 1),
            ]);
        }
    }
}
