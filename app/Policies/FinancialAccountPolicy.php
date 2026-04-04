<?php

namespace App\Policies;

use App\Models\FinancialAccount;
use App\Models\User;

class FinancialAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialAccount $financialAccount): bool
    {
        return $financialAccount->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialAccount $financialAccount): bool
    {
        return $financialAccount->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialAccount $financialAccount): bool
    {
        return $financialAccount->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialAccount $financialAccount): bool
    {
        return $financialAccount->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialAccount $financialAccount): bool
    {
        return $financialAccount->user_uid === $user->uid;
    }
}
