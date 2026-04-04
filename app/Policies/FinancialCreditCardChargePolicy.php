<?php

namespace App\Policies;

use App\Models\FinancialCreditCardCharge;
use App\Models\User;

class FinancialCreditCardChargePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialCreditCardCharge $financialCreditCardCharge): bool
    {
        return $financialCreditCardCharge->creditCard->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialCreditCardCharge $financialCreditCardCharge): bool
    {
        return $financialCreditCardCharge->creditCard->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialCreditCardCharge $financialCreditCardCharge): bool
    {
        return $financialCreditCardCharge->creditCard->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialCreditCardCharge $financialCreditCardCharge): bool
    {
        return $financialCreditCardCharge->creditCard->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialCreditCardCharge $financialCreditCardCharge): bool
    {
        return $financialCreditCardCharge->creditCard->user_uid === $user->uid;
    }
}
