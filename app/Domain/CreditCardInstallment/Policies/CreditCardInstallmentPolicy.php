<?php

namespace App\Domain\CreditCardInstallment\Policies;

use App\Domain\CreditCardInstallment\Models\CreditCardInstallment;
use App\Domain\User\Models\User;

class CreditCardInstallmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CreditCardInstallment $creditCardInstallment): bool
    {
        return $creditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CreditCardInstallment $creditCardInstallment): bool
    {
        return $creditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }

    public function delete(User $user, CreditCardInstallment $creditCardInstallment): bool
    {
        return $creditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }

    public function restore(User $user, CreditCardInstallment $creditCardInstallment): bool
    {
        return $creditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }

    public function forceDelete(User $user, CreditCardInstallment $creditCardInstallment): bool
    {
        return $creditCardInstallment->charge->creditCard->user_uid === $user->uid;
    }
}
