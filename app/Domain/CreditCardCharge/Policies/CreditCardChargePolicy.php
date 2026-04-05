<?php

namespace App\Domain\CreditCardCharge\Policies;

use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\User\Models\User;

class CreditCardChargePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CreditCardCharge $creditCardCharge): bool
    {
        return $creditCardCharge->creditCard->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CreditCardCharge $creditCardCharge): bool
    {
        return $creditCardCharge->creditCard->user_uid === $user->uid;
    }

    public function delete(User $user, CreditCardCharge $creditCardCharge): bool
    {
        return $creditCardCharge->creditCard->user_uid === $user->uid;
    }

    public function restore(User $user, CreditCardCharge $creditCardCharge): bool
    {
        return $creditCardCharge->creditCard->user_uid === $user->uid;
    }

    public function forceDelete(User $user, CreditCardCharge $creditCardCharge): bool
    {
        return $creditCardCharge->creditCard->user_uid === $user->uid;
    }
}
