<?php

namespace App\Domain\FixedExpense\Policies;

use App\Domain\FixedExpense\Models\FixedExpense;
use App\Domain\User\Models\User;

class FixedExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FixedExpense $fixedExpense): bool
    {
        return $fixedExpense->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FixedExpense $fixedExpense): bool
    {
        return $fixedExpense->user_uid === $user->uid;
    }

    public function delete(User $user, FixedExpense $fixedExpense): bool
    {
        return $fixedExpense->user_uid === $user->uid;
    }

    public function restore(User $user, FixedExpense $fixedExpense): bool
    {
        return $fixedExpense->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FixedExpense $fixedExpense): bool
    {
        return $fixedExpense->user_uid === $user->uid;
    }
}
