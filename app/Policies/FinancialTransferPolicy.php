<?php

namespace App\Policies;

use App\Models\FinancialTransfer;
use App\Models\User;

class FinancialTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FinancialTransfer $financialTransfer): bool
    {
        return $financialTransfer->user_uid === $user->uid;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FinancialTransfer $financialTransfer): bool
    {
        return $financialTransfer->user_uid === $user->uid;
    }

    public function delete(User $user, FinancialTransfer $financialTransfer): bool
    {
        return $financialTransfer->user_uid === $user->uid;
    }

    public function restore(User $user, FinancialTransfer $financialTransfer): bool
    {
        return $financialTransfer->user_uid === $user->uid;
    }

    public function forceDelete(User $user, FinancialTransfer $financialTransfer): bool
    {
        return $financialTransfer->user_uid === $user->uid;
    }
}
