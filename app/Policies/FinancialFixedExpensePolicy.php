<?php

namespace App\Policies;

use App\Models\FinancialFixedExpense;
use App\Models\User;

class FinancialFixedExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialFixedExpense $financialFixedExpense): bool
    {
        return $financialFixedExpense->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialFixedExpense $financialFixedExpense): bool
    {
        return $financialFixedExpense->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialFixedExpense $financialFixedExpense): bool
    {
        return $financialFixedExpense->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialFixedExpense $financialFixedExpense): bool
    {
        return $financialFixedExpense->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialFixedExpense $financialFixedExpense): bool
    {
        return $financialFixedExpense->user_uid === $user->uid;
    }
}
