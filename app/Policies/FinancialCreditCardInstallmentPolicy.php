<?php

namespace App\Policies;

use App\Models\FinancialCreditCardInstallment;
use App\Models\User;

class FinancialCreditCardInstallmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialCreditCardInstallment $financialCreditCardInstallment): bool
    {
        return $financialCreditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialCreditCardInstallment $financialCreditCardInstallment): bool
    {
        return $financialCreditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialCreditCardInstallment $financialCreditCardInstallment): bool
    {
        return $financialCreditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialCreditCardInstallment $financialCreditCardInstallment): bool
    {
        return $financialCreditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialCreditCardInstallment $financialCreditCardInstallment): bool
    {
        return $financialCreditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }
}
