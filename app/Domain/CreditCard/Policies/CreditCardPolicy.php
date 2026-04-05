<?php

namespace App\Domain\CreditCard\Policies;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\User\Models\User;

class CreditCardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CreditCard $creditCard): bool
    {
        return $creditCard->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CreditCard $creditCard): bool
    {
        return $creditCard->user_uid === $user->uid;
    }

    public function delete(User $user, CreditCard $creditCard): bool
    {
        return $creditCard->user_uid === $user->uid;
    }

    public function restore(User $user, CreditCard $creditCard): bool
    {
        return $creditCard->user_uid === $user->uid;
    }

    public function forceDelete(User $user, CreditCard $creditCard): bool
    {
        return $creditCard->user_uid === $user->uid;
    }
}
