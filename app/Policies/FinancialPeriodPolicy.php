<?php

namespace App\Policies;

use App\Models\FinancialPeriod;
use App\Models\User;

class FinancialPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialPeriod $financialPeriod): bool
    {
        return $financialPeriod->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialPeriod $financialPeriod): bool
    {
        return $financialPeriod->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialPeriod $financialPeriod): bool
    {
        return $financialPeriod->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialPeriod $financialPeriod): bool
    {
        return $financialPeriod->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialPeriod $financialPeriod): bool
    {
        return $financialPeriod->user_uid === $user->uid;
    }
}
