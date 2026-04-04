<?php

namespace App\Policies;

use App\Models\FinancialCreditCard;
use App\Models\User;

class FinancialCreditCardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialCreditCard $financialCreditCard): bool
    {
        return $financialCreditCard->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialCreditCard $financialCreditCard): bool
    {
        return $financialCreditCard->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialCreditCard $financialCreditCard): bool
    {
        return $financialCreditCard->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialCreditCard $financialCreditCard): bool
    {
        return $financialCreditCard->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialCreditCard $financialCreditCard): bool
    {
        return $financialCreditCard->user_uid === $user->uid;
    }
}
