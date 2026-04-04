<?php

namespace App\Policies;

use App\Models\FinancialCategory;
use App\Models\User;

class FinancialCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialCategory $financialCategory): bool
    {
        return $financialCategory->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialCategory $financialCategory): bool
    {
        return $financialCategory->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialCategory $financialCategory): bool
    {
        return $financialCategory->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialCategory $financialCategory): bool
    {
        return $financialCategory->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialCategory $financialCategory): bool
    {
        return $financialCategory->user_uid === $user->uid;
    }
}
