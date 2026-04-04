<?php

namespace App\Policies;

use App\Models\FinancialTransaction;
use App\Models\User;

class FinancialTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialTransaction $financialTransaction): bool
    {
        return $financialTransaction->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialTransaction $financialTransaction): bool
    {
        return $financialTransaction->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialTransaction $financialTransaction): bool
    {
        return $financialTransaction->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialTransaction $financialTransaction): bool
    {
        return $financialTransaction->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialTransaction $financialTransaction): bool
    {
        return $financialTransaction->user_uid === $user->uid;
    }
}
